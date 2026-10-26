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
use App\Support\ReportPdf;

final class ReportController extends BaseController
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => trim($request->string('q')),
            'type' => $request->string('type'),
            'category' => $request->string('category'),
            'species' => $request->string('species'),
            'state' => $request->string('state'),
            'year' => $request->int('year'),
            'sort' => $request->string('sort', 'recientes'),
        ];
        $page = max(1, $request->int('page', 1));
        $perPage = (int) config('app.pagination.reports', 9);
        $repo = new ReportRepository();
        $result = $repo->search($filters, $page, $perPage);
        $catalog = new CatalogRepository();

        return $this->view('reports/index', [
            'title' => 'Reportes de laboratorio · VitroLab MX',
            'metaDescription' => 'Ensayos, validaciones, protocolos y seguimientos de cultivo in vitro: diseño, tratamientos, variables, resultados y conclusiones.',
            'reports' => $result['items'],
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
        $repo = new ReportRepository();
        $report = $repo->findPublishedBySlug($slug);
        $preview = false;
        if ($report === null) {
            $user = Auth::user();
            $report = $repo->findBySlug($slug);
            if ($report !== null && $user !== null && ((int) $report['author_id'] === (int) $user['id'] || in_array($user['role'], (array) config('app.moderators', []), true))) {
                $preview = true;
            } else {
                $report = null;
            }
        }
        if ($report === null) {
            $this->notFound('Este reporte no existe o aún no se ha publicado.');
        }

        if (!$preview) {
            $repo->incrementViews((int) $report['id']);
            $report['views'] = (int) $report['views'] + 1;
        }

        $user = Auth::user();
        $interactions = new InteractionRepository();

        return $this->view('reports/show', [
            'title' => $report['title'] . ' · VitroLab MX',
            'metaDescription' => excerpt((string) $report['summary'], 160),
            'report' => $report,
            'treatments' => $repo->treatments((int) $report['id']),
            'files' => $repo->files((int) $report['id']),
            'comments' => $interactions->comments('reporte', (int) $report['id'], $user !== null && in_array($user['role'], (array) config('app.moderators', []), true)),
            'reviews' => $interactions->reviews('reporte', (int) $report['id']),
            'reacted' => $user !== null && $interactions->hasReacted('reporte', (int) $report['id'], (int) $user['id']),
            'bookmarked' => $user !== null && $interactions->isBookmarked('reporte', (int) $report['id'], (int) $user['id']),
            'reactionsCount' => $interactions->reactionsCount('reporte', (int) $report['id']),
            'preview' => $preview,
            'canEdit' => $user !== null && $this->canEditContent($report, $user),
            'canModerate' => $user !== null && in_array($user['role'], (array) config('app.moderators', []), true),
            'canonical' => url('/reportes/' . $report['slug']),
            'robots' => $preview ? 'noindex,nofollow' : 'index,follow',
        ]);
    }

    public function create(Request $request): Response
    {
        $user = Auth::requireLogin();
        return $this->view('reports/form', $this->formData($user) + [
            'title' => 'Subir un reporte · VitroLab MX',
            'metaDescription' => 'Publica un ensayo, protocolo, validación o seguimiento de laboratorio con diseño, tratamientos y resultados.',
            'report' => null,
            'treatments' => [],
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
            return $this->redirect('/reportes/nuevo');
        }

        $data['author_id'] = (int) $user['id'];
        $data['status'] = $action === 'submit' ? 'en_revision' : 'borrador';
        $repo = new ReportRepository();
        $id = $repo->create($data);
        $this->saveReportFiles($id, $request);
        $this->audit($action === 'submit' ? 'reporte.enviado' : 'reporte.borrador', 'reporte', $id);
        Flash::success($action === 'submit' ? 'Tu reporte se envió al comité editorial.' : 'Guardamos el borrador del reporte.');
        $report = $repo->findById($id);
        return $this->redirect('/reportes/' . ($report['slug'] ?? ''));
    }

    public function edit(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $report = (new ReportRepository())->findBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no existe.');
        }
        $this->requireContentOwner($report, $user);
        return $this->view('reports/form', $this->formData($user) + [
            'title' => 'Editar reporte · VitroLab MX',
            'report' => $report,
            'treatments' => (new ReportRepository())->treatments((int) $report['id']),
            'user' => $user,
            'robots' => 'noindex,nofollow',
        ]);
    }

    public function update(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $repo = new ReportRepository();
        $report = $repo->findBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no existe.');
        }
        $this->requireContentOwner($report, $user);

        $action = $request->string('action', 'draft');
        [$data, $errors] = $this->validate($request, $action, $user);
        if ($errors !== []) {
            $this->withErrors($errors);
            $this->flashOld($data);
            return $this->redirect('/reportes/' . $report['slug'] . '/editar');
        }

        $repo->update((int) $report['id'], $data);
        if ($action === 'submit') {
            $repo->updateStatus((int) $report['id'], 'en_revision');
        }
        $this->saveReportFiles((int) $report['id'], $request);
        $this->audit('reporte.actualizado', 'reporte', (int) $report['id']);
        Flash::success($action === 'submit' ? 'Tu reporte se envió de nuevo al comité.' : 'Guardamos los cambios.');
        return $this->redirect('/reportes/' . $report['slug']);
    }

    public function submit(Request $request, string $slug): Response
    {
        $user = Auth::requireVerified();
        $repo = new ReportRepository();
        $report = $repo->findBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no existe.');
        }
        $this->requireContentOwner($report, $user);
        $repo->updateStatus((int) $report['id'], 'en_revision');
        $this->audit('reporte.enviado', 'reporte', (int) $report['id']);
        Flash::success('Tu reporte se envió al comité editorial.');
        return $this->redirect('/reportes/' . $report['slug']);
    }

    public function comment(Request $request, string $slug): Response
    {
        $user = Auth::requireVerified();
        $report = (new ReportRepository())->findPublishedBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no está disponible para comentar.');
        }
        $body = trim($request->string('body'));
        if (mb_strlen($body) < 5) {
            Flash::error('Escribe un comentario de al menos 5 caracteres.');
            return $this->redirect('/reportes/' . $slug . '#comentarios');
        }
        (new InteractionRepository())->addComment('reporte', (int) $report['id'], (int) $user['id'], mb_substr($body, 0, 2000));
        Flash::success('Tu comentario se publicó.');
        return $this->redirect('/reportes/' . $slug . '#comentarios');
    }

    public function react(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $report = (new ReportRepository())->findPublishedBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no está disponible.');
        }
        $result = (new InteractionRepository())->toggleReaction('reporte', (int) $report['id'], (int) $user['id']);
        return $request->wantsJson() ? Response::json($result) : $this->redirect('/reportes/' . $slug);
    }

    public function bookmark(Request $request, string $slug): Response
    {
        $user = Auth::requireLogin();
        $report = (new ReportRepository())->findPublishedBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no está disponible.');
        }
        $result = (new InteractionRepository())->toggleBookmark('reporte', (int) $report['id'], (int) $user['id']);
        if ($request->wantsJson()) {
            return Response::json($result);
        }
        Flash::success($result['active'] ? 'Guardaste este reporte.' : 'Quitamos el reporte de tus favoritos.');
        return $this->redirect('/reportes/' . $slug);
    }

    public function deleteFile(Request $request, string $slug, string $fileId): Response
    {
        $user = Auth::requireLogin();
        $repo = new ReportRepository();
        $report = $repo->findBySlug($slug);
        if ($report === null) {
            $this->notFound('Este reporte no existe.');
        }
        $this->requireContentOwner($report, $user);
        $path = $repo->deleteFile((int) $fileId, (int) $report['id']);
        if ($path !== null) {
            $file = rtrim((string) config('app.uploads.files_path'), '/') . '/' . ltrim($path, '/');
            if (is_file($file)) {
                @unlink($file);
            }
        }
        Flash::success('El archivo se eliminó.');
        return $this->redirect('/reportes/' . $slug . '/editar');
    }

    public function pdf(Request $request, string $slug): Response
    {
        $repo = new ReportRepository();
        $report = $repo->findPublishedBySlug($slug);
        if ($report === null) {
            $user = Auth::user();
            $report = $repo->findBySlug($slug);
            if ($report === null || $user === null || ((int) $report['author_id'] !== (int) $user['id'] && !in_array($user['role'], (array) config('app.moderators', []), true))) {
                $this->notFound('Este reporte no está disponible para descarga.');
            }
        }
        $content = ReportPdf::render($report, $repo->treatments((int) $report['id']));
        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reporte-' . $report['slug'] . '.pdf"',
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    /** @return array<string, mixed> */
    private function formData(array $user): array
    {
        return $this->catalogData() + [
            'caseOptions' => (new ReportRepository())->forCaseForm((int) $user['id']),
        ];
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
            'report_type' => $request->string('report_type', 'ensayo'),
            'category_id' => $request->int('category_id'),
            'species_id' => $speciesId,
            'species_text' => $speciesText,
            'state_code' => $request->string('state_code'),
            'case_id' => $request->int('case_id'),
            'period_start' => $request->string('period_start'),
            'period_end' => $request->string('period_end'),
            'objective' => trim($request->string('objective')),
            'hypothesis' => trim($request->string('hypothesis')),
            'design' => trim($request->string('design')),
            'treatments_text' => trim($request->string('treatments_text')),
            'replicates' => trim($request->string('replicates')),
            'variables' => trim($request->string('variables')),
            'results' => trim($request->string('results')),
            'analysis' => trim($request->string('analysis')),
            'conclusions' => trim($request->string('conclusions')),
            'recommendations' => trim($request->string('recommendations')),
            'limitations' => trim($request->string('limitations')),
            'references_text' => trim($request->string('references_text')),
            'license' => $request->string('license', 'CC BY-NC-SA 4.0'),
            'treatments' => $this->treatmentsFrom($request),
        ];

        $validator = $this->validator($data, [
            'title' => 'título',
            'summary' => 'resumen',
            'report_type' => 'tipo de reporte',
            'category_id' => 'categoría',
            'species_text' => 'especie',
            'state_code' => 'estado',
            'objective' => 'objetivo',
            'design' => 'diseño',
            'treatments_text' => 'tratamientos',
            'variables' => 'variables',
            'results' => 'resultados',
            'analysis' => 'análisis',
            'conclusions' => 'conclusiones',
            'recommendations' => 'recomendaciones',
            'limitations' => 'limitaciones',
            'references_text' => 'referencias',
        ]);
        $validator->validate([
            'title' => 'required|min:10|max:180',
            'summary' => 'required|min:30|max:600',
            'report_type' => 'required|in:ensayo,protocolo,diagnostico,seguimiento,validacion,datos',
            'category_id' => 'required|integer|min:1',
            'species_text' => 'required|min:3|max:160',
            'state_code' => 'required|min:2|max:5',
            'objective' => 'required|min:30|max:4000',
            'design' => 'required|min:20|max:4000',
            'treatments_text' => 'required|min:10|max:4000',
            'variables' => 'required|min:10|max:3000',
            'results' => 'required|min:30|max:6000',
            'analysis' => 'required|min:20|max:6000',
            'conclusions' => 'required|min:20|max:4000',
            'recommendations' => 'required|min:10|max:4000',
            'limitations' => 'required|min:10|max:3000',
            'references_text' => 'required|min:10|max:5000',
            'replicates' => 'nullable|integer|between:1,100000',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
        ]);

        $errors = $validator->errors();
        if ($data['category_id'] <= 0) {
            $errors['category_id'] = 'Selecciona una categoría.';
        }
        if ($data['period_start'] !== '' && $data['period_end'] !== '' && strtotime($data['period_start']) > strtotime($data['period_end'])) {
            $errors['period_end'] = 'La fecha final debe ser posterior a la inicial.';
        }
        if ($data['case_id'] > 0) {
            $case = (new CaseRepository())->findById($data['case_id']);
            $allowed = $case !== null && ((int) $case['author_id'] === (int) $user['id'] || $case['status'] === 'publicado');
            if (!$allowed) {
                $errors['case_id'] = 'El caso vinculado no está disponible.';
            }
        }
        if ($action === 'submit' && $user['status'] !== 'verificado') {
            $errors['action'] = 'Para enviar a revisión necesitas verificar tu cuenta. Puedes guardar el borrador.';
        }

        $data['replicates'] = $data['replicates'] === '' ? null : (int) $data['replicates'];
        $data['period_start'] = $data['period_start'] === '' ? null : $data['period_start'];
        $data['period_end'] = $data['period_end'] === '' ? null : $data['period_end'];

        return [$data, $errors];
    }

    /** @return array<int, array<string, string>> */
    private function treatmentsFrom(Request $request): array
    {
        $labels = $request->post('treatment_label');
        if (!is_array($labels)) {
            return [];
        }
        $ns = (array) $request->post('treatment_n', []);
        $responses = (array) $request->post('treatment_response', []);
        $units = (array) $request->post('treatment_unit', []);
        $notes = (array) $request->post('treatment_notes', []);
        $rows = [];
        foreach ($labels as $index => $label) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }
            $rows[] = [
                'label' => mb_substr($label, 0, 160),
                'n' => trim((string) ($ns[$index] ?? '')),
                'response' => trim((string) ($responses[$index] ?? '')),
                'unit' => trim((string) ($units[$index] ?? '')),
                'notes' => trim((string) ($notes[$index] ?? '')),
            ];
        }
        return $rows;
    }
}
