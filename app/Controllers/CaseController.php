<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CaseRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\InteractionRepository;
use App\Repositories\ReportRepository;
use App\Support\CasePdf;

final class CaseController extends BaseController
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => trim($request->string('q')),
            'category' => $request->string('category'),
            'species' => $request->string('species'),
            'state' => $request->string('state'),
            'year' => $request->int('year'),
            'sort' => $request->string('sort', 'recientes'),
        ];
        $page = max(1, $request->int('page', 1));
        $perPage = (int) config('app.pagination.cases', 9);
        $repo = new CaseRepository();
        $result = $repo->search($filters, $page, $perPage);
        $catalog = new CatalogRepository();

        return $this->view('cases/index', [
            'title' => 'Casos de cultivo in vitro · VitroLab MX',
            'metaDescription' => 'Casos documentados de micropropagación, organogénesis, embriogénesis, conservación y aclimatación de plantas, con protocolo, medios y resultados.',
            'cases' => $result['items'],
            'total' => $result['total'],
            'filters' => $filters,
            'paginator' => new Paginator($result['total'], $perPage, $page),
            'categoriesList' => $catalog->categories(),
            'speciesList' => $catalog->speciesList(),
            'states' => $catalog->states(),
            'years' => $catalog->years(),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $repo = new CaseRepository();
        $case = $repo->findPublishedBySlug($slug);
        $preview = false;

        if ($case === null) {
            $user = Auth::user();
            $case = $repo->findBySlug($slug);
            if ($case !== null && $user !== null && ((int) $case['author_id'] === (int) $user['id'] || in_array($user['role'], (array) config('app.moderators', []), true))) {
                $preview = true;
            } else {
                $case = null;
            }
        }

        if ($case === null) {
            $this->notFound('Este caso no existe o aún no se ha publicado.');
        }

        if (!$preview) {
            $repo->incrementViews((int) $case['id']);
            $case['views'] = (int) $case['views'] + 1;
        }

        $user = Auth::user();
        $interactions = new InteractionRepository();
        $reports = new ReportRepository();

        return $this->view('cases/show', [
            'title' => $case['title'] . ' · VitroLab MX',
            'metaDescription' => excerpt((string) $case['summary'], 160),
            'case' => $case,
            'comments' => $interactions->comments('caso', (int) $case['id'], $user !== null && in_array($user['role'], (array) config('app.moderators', []), true)),
            'reviews' => $interactions->reviews('caso', (int) $case['id']),
            'similar' => $repo->similar($case, 3),
            'linkedReports' => $reports->linkedReports((int) $case['id']),
            'reacted' => $user !== null && $interactions->hasReacted('caso', (int) $case['id'], (int) $user['id']),
            'bookmarked' => $user !== null && $interactions->isBookmarked('caso', (int) $case['id'], (int) $user['id']),
            'reactionsCount' => $interactions->reactionsCount('caso', (int) $case['id']),
            'preview' => $preview,
            'canEdit' => $user !== null && $this->canEditContent($case, $user),
            'canModerate' => $user !== null && in_array($user['role'], (array) config('app.moderators', []), true),
            'canonical' => url('/casos/' . $case['slug']),
            'robots' => $preview ? 'noindex,nofollow' : 'index,follow',
        ]);
    }

    public function create(Request $request): Response
    {
        $user = Auth::requireLogin();
        return $this->view('cases/form', $this->catalogData() + [
            'title' => 'Compartir un caso · VitroLab MX',
            'metaDescription' => 'Documenta un caso de cultivo in vitro con explante, desinfección, medio, reguladores, condiciones y resultados.',
            'case' => null,
            'user' => $user,
            'robots' => 'noindex,nofollow',
        ]);
    }

    public function store(Request $request): Response
    {
        $user = Auth::requireLogin();
        $action = $request->string('action', 'draft');
        [$data, $errors] = $this->validate($request, $action, $user);

        if ($errors !== []) {
            $this->withErrors($errors);
            $this->flashOld($data);
            return $this->redirect('/casos/nuevo');
        }

        $data['author_id'] = (int) $user['id'];
        $data['status'] = $action === 'submit' ? 'en_revision' : 'borrador';
        $repo = new CaseRepository();
        $id = $repo->create($data);
        $this->saveCaseImages($id, $request);
        $this->audit($action === 'submit' ? 'caso.enviado' : 'caso.borrador', 'caso', $id);
        Flash::success($action === 'submit' ? 'Tu caso se envió al comité editorial.' : 'Guardamos tu borrador. Puedes completarlo cuando quieras.');

        $case = $repo->findById($id);
        return $this->redirect('/casos/' . ($case['slug'] ?? ''));
    }

    public function edit(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $case = (new CaseRepository())->findBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no existe.');
        }
        $this->requireContentOwner($case, $user);

        return $this->view('cases/form', $this->catalogData() + [
            'title' => 'Editar caso · VitroLab MX',
            'case' => $case,
            'user' => $user,
            'robots' => 'noindex,nofollow',
        ]);
    }

    public function update(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $repo = new CaseRepository();
        $case = $repo->findBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no existe.');
        }
        $this->requireContentOwner($case, $user);

        $action = $request->string('action', 'draft');
        [$data, $errors] = $this->validate($request, $action, $user);
        if ($errors !== []) {
            $this->withErrors($errors);
            $this->flashOld($data);
            return $this->redirect('/casos/' . $case['slug'] . '/editar');
        }

        $repo->update((int) $case['id'], $data);
        if ($action === 'submit') {
            $repo->updateStatus((int) $case['id'], 'en_revision');
        }
        $this->saveCaseImages((int) $case['id'], $request);
        $this->audit('caso.actualizado', 'caso', (int) $case['id']);
        Flash::success($action === 'submit' ? 'Tu caso se envió de nuevo al comité editorial.' : 'Guardamos los cambios.');
        return $this->redirect('/casos/' . $case['slug']);
    }

    public function submit(Request $request, string $slug): Response
    {
        $user = Auth::requireVerified();
        $repo = new CaseRepository();
        $case = $repo->findBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no existe.');
        }
        $this->requireContentOwner($case, $user);
        $repo->updateStatus((int) $case['id'], 'en_revision');
        $this->audit('caso.enviado', 'caso', (int) $case['id']);
        Flash::success('Tu caso se envió al comité editorial. Te avisaremos cuando haya una decisión.');
        return $this->redirect('/casos/' . $case['slug']);
    }

    public function comment(Request $request, string $slug): Response
    {
        $user = Auth::requireVerified();
        $case = (new CaseRepository())->findPublishedBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no está disponible para comentar.');
        }
        $body = trim($request->string('body'));
        if (mb_strlen($body) < 5) {
            Flash::error('Escribe un comentario de al menos 5 caracteres.');
            return $this->redirect('/casos/' . $slug . '#comentarios');
        }
        if (mb_strlen($body) > 2000) {
            Flash::error('El comentario no debe exceder 2,000 caracteres.');
            return $this->redirect('/casos/' . $slug . '#comentarios');
        }
        (new InteractionRepository())->addComment('caso', (int) $case['id'], (int) $user['id'], $body);
        $this->audit('comentario.caso', 'caso', (int) $case['id']);
        Flash::success('Tu comentario se publicó.');
        return $this->redirect('/casos/' . $slug . '#comentarios');
    }

    public function react(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $case = (new CaseRepository())->findPublishedBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no está disponible.');
        }
        $result = (new InteractionRepository())->toggleReaction('caso', (int) $case['id'], (int) $user['id']);
        if ($request->wantsJson()) {
            return Response::json($result);
        }
        return $this->redirect('/casos/' . $slug);
    }

    public function bookmark(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $case = (new CaseRepository())->findPublishedBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no está disponible.');
        }
        $result = (new InteractionRepository())->toggleBookmark('caso', (int) $case['id'], (int) $user['id']);
        if ($request->wantsJson()) {
            return Response::json($result);
        }
        Flash::success($result['active'] ? 'Guardaste este caso en tus favoritos.' : 'Quitamos este caso de tus favoritos.');
        return $this->redirect('/casos/' . $slug);
    }

    public function deleteImage(Request $request, string $slug, string $imageId): Response
    {
        $user = Auth::requireLogin();
        $repo = new CaseRepository();
        $case = $repo->findBySlug($slug);
        if ($case === null) {
            $this->notFound('Este caso no existe.');
        }
        $this->requireContentOwner($case, $user);
        $path = $repo->deleteImage((int) $imageId, (int) $case['id']);
        if ($path !== null) {
            $file = rtrim((string) config('app.uploads.images_path'), '/') . '/' . ltrim($path, '/');
            if (is_file($file)) {
                @unlink($file);
            }
        }
        Flash::success('La imagen se eliminó.');
        return $this->redirect('/casos/' . $slug . '/editar');
    }

    public function pdf(Request $request, string $slug): Response
    {
        $repo = new CaseRepository();
        $case = $repo->findPublishedBySlug($slug);
        if ($case === null) {
            $user = Auth::user();
            $case = $repo->findBySlug($slug);
            if ($case === null || $user === null || ((int) $case['author_id'] !== (int) $user['id'] && !in_array($user['role'], (array) config('app.moderators', []), true))) {
                $this->notFound('Este caso no está disponible para descarga.');
            }
        }
        $content = CasePdf::render($case);
        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="caso-' . $case['slug'] . '.pdf"',
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function validate(Request $request, string $action, array $user): array
    {
        $speciesId = $request->int('species_id');
        $speciesText = trim($request->string('species_text'));
        if ($speciesId > 0) {
            $species = (new CatalogRepository())->species($speciesId);
            if ($species !== null) {
                $speciesText = (string) $species['scientific_name'];
            }
        }

        $data = [
            'title' => trim($request->string('title')),
            'summary' => trim($request->string('summary')),
            'category_id' => $request->int('category_id'),
            'species_id' => $speciesId,
            'species_text' => $speciesText,
            'state_code' => $request->string('state_code'),
            'municipality' => trim($request->string('municipality')),
            'year' => $request->int('year', (int) date('Y')),
            'objective' => trim($request->string('objective')),
            'context' => trim($request->string('context')),
            'explant' => trim($request->string('explant')),
            'disinfection' => trim($request->string('disinfection')),
            'medium' => trim($request->string('medium')),
            'pgr' => trim($request->string('pgr')),
            'conditions' => trim($request->string('conditions')),
            'protocol' => trim($request->string('protocol')),
            'results' => trim($request->string('results')),
            'challenges' => trim($request->string('challenges')),
            'lessons' => trim($request->string('lessons')),
            'contamination_pct' => trim($request->string('contamination_pct')),
            'survival_pct' => trim($request->string('survival_pct')),
            'multiplication_rate' => trim($request->string('multiplication_rate')),
            'rooting_pct' => trim($request->string('rooting_pct')),
            'acclimatization_pct' => trim($request->string('acclimatization_pct')),
            'duration_weeks' => trim($request->string('duration_weeks')),
            'references_text' => trim($request->string('references_text')),
            'license' => $request->string('license', 'CC BY-NC-SA 4.0'),
        ];

        $validator = $this->validator($data, [
            'title' => 'título',
            'summary' => 'resumen',
            'category_id' => 'categoría',
            'species_text' => 'especie',
            'state_code' => 'estado',
            'objective' => 'objetivo',
            'context' => 'contexto',
            'explant' => 'explante',
            'disinfection' => 'desinfección',
            'medium' => 'medio de cultivo',
            'conditions' => 'condiciones de incubación',
            'protocol' => 'protocolo',
            'results' => 'resultados',
            'references_text' => 'referencias',
        ]);
        $validator->validate([
            'title' => 'required|min:10|max:180',
            'summary' => 'required|min:30|max:600',
            'category_id' => 'required|integer|min:1',
            'species_text' => 'required|min:3|max:160',
            'state_code' => 'required|min:2|max:5',
            'objective' => 'required|min:30|max:3000',
            'context' => 'required|min:30|max:4000',
            'explant' => 'required|min:10|max:2000',
            'disinfection' => 'required|min:10|max:3000',
            'medium' => 'required|min:10|max:3000',
            'pgr' => 'nullable|max:2000',
            'conditions' => 'required|min:10|max:2000',
            'protocol' => 'required|min:40|max:8000',
            'results' => 'required|min:30|max:5000',
            'challenges' => 'nullable|max:4000',
            'lessons' => 'nullable|max:3000',
            'references_text' => 'required|min:10|max:5000',
            'year' => 'required|integer|between:2000,2100',
        ]);

        $errors = $validator->errors();
        if ($data['category_id'] <= 0) {
            $errors['category_id'] = 'Selecciona una línea de trabajo.';
        }

        foreach (['contamination_pct', 'survival_pct', 'rooting_pct', 'acclimatization_pct'] as $field) {
            if ($data[$field] !== '' && (!is_numeric(str_replace(',', '.', $data[$field])) || (float) str_replace(',', '.', $data[$field]) < 0 || (float) str_replace(',', '.', $data[$field]) > 100)) {
                $errors[$field] = 'Indica un porcentaje entre 0 y 100.';
            }
        }
        if ($data['multiplication_rate'] !== '' && (!is_numeric(str_replace(',', '.', $data['multiplication_rate'])) || (float) str_replace(',', '.', $data['multiplication_rate']) < 0 || (float) str_replace(',', '.', $data['multiplication_rate']) > 1000)) {
            $errors['multiplication_rate'] = 'Indica un número entre 0 y 1000.';
        }
        if ($data['duration_weeks'] !== '' && (!ctype_digit($data['duration_weeks']) || (int) $data['duration_weeks'] < 1 || (int) $data['duration_weeks'] > 260)) {
            $errors['duration_weeks'] = 'Indica una duración entre 1 y 260 semanas.';
        }
        if ($action === 'submit' && $user['status'] !== 'verificado') {
            $errors['action'] = 'Para enviar a revisión necesitas verificar tu cuenta. Puedes guardar el borrador.';
        }

        foreach (['contamination_pct', 'survival_pct', 'multiplication_rate', 'rooting_pct', 'acclimatization_pct'] as $field) {
            $data[$field] = $data[$field] === '' ? null : (float) str_replace(',', '.', $data[$field]);
        }
        $data['duration_weeks'] = $data['duration_weeks'] === '' ? null : (int) $data['duration_weeks'];

        return [$data, $errors];
    }
}
