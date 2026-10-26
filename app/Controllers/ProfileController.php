<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Str;
use App\Core\Upload;
use App\Repositories\CaseRepository;
use App\Repositories\CatalogRepository;
use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;

final class ProfileController extends BaseController
{
    public function show(Request $request): Response
    {
        $user = Auth::requireLogin();
        $repo = new UserRepository();
        $catalog = new CatalogRepository();

        return $this->view('profile/show', [
            'title' => 'Mi perfil · VitroLab MX',
            'metaDescription' => 'Actualiza tus datos, tu institución, tu semblanza y tu verificación.',
            'user' => $user,
            'activity' => $repo->activity((int) $user['id']),
            'states' => $catalog->states(),
            'institutions' => $catalog->institutions(),
            'myCases' => (new CaseRepository())->mine((int) $user['id']),
            'myReports' => (new ReportRepository())->mine((int) $user['id']),
            'completeness' => $this->completeness($user),
            'robots' => 'noindex,nofollow',
        ]);
    }

    public function update(Request $request): Response
    {
        $user = Auth::requireLogin();
        $data = [
            'name' => trim($request->string('name')),
            'institution_id' => $request->int('institution_id'),
            'institution_other' => trim($request->string('institution_other')),
            'career' => trim($request->string('career')),
            'semester' => $request->string('semester'),
            'student_id' => trim($request->string('student_id')),
            'orcid' => trim($request->string('orcid')),
            'state_code' => $request->string('state_code'),
            'bio' => trim($request->string('bio')),
        ];

        $validator = $this->validator($data, [
            'name' => 'nombre', 'career' => 'formación o puesto', 'semester' => 'semestre',
            'student_id' => 'matrícula o identificación', 'orcid' => 'ORCID', 'state_code' => 'estado', 'bio' => 'semblanza',
        ]);
        $validator->validate([
            'name' => 'required|min:3|max:120',
            'career' => 'required|min:3|max:160',
            'semester' => 'nullable|integer|between:1,12',
            'student_id' => 'nullable|max:40',
            'orcid' => 'nullable|max:30',
            'state_code' => 'required|min:2|max:5',
            'bio' => 'nullable|max:1500',
        ]);
        $errors = $validator->errors();
        if ($data['institution_id'] <= 0 && $data['institution_other'] === '') {
            $errors['institution_id'] = 'Selecciona tu institución o escribe su nombre.';
        }
        if ($data['orcid'] !== '' && !preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/', $data['orcid'])) {
            $errors['orcid'] = 'El ORCID debe tener el formato 0000-0000-0000-0000.';
        }
        if ($errors !== []) {
            $this->withErrors($errors);
            $this->flashOld($data);
            return $this->redirect('/perfil');
        }

        (new UserRepository())->updateProfile((int) $user['id'], [
            'name' => $data['name'],
            'institution_id' => $data['institution_id'] > 0 ? $data['institution_id'] : null,
            'institution_other' => $data['institution_other'],
            'career' => $data['career'],
            'semester' => $data['semester'] !== '' ? (int) $data['semester'] : null,
            'student_id' => $data['student_id'],
            'orcid' => $data['orcid'],
            'state_code' => $data['state_code'],
            'bio' => $data['bio'],
        ]);
        $this->audit('perfil.actualizado', 'usuario', (int) $user['id']);
        Flash::success('Tu perfil se actualizó.');
        return $this->redirect('/perfil');
    }

    public function avatar(Request $request): Response
    {
        $user = Auth::requireLogin();
        $file = $request->file('avatar');
        if ($file === null) {
            Flash::error('Selecciona una imagen.');
            return $this->redirect('/perfil');
        }
        try {
            $image = Upload::image(
                $file,
                'perfil-' . (int) $user['id'],
                (string) config('app.uploads.avatars_path'),
                (string) config('app.uploads.avatars_url'),
                800
            );
            $previous = (string) ($user['avatar_url'] ?? '');
            (new UserRepository())->updateAvatar((int) $user['id'], $image['url']);
            if ($previous !== '') {
                $relative = ltrim(str_replace((string) config('app.uploads.avatars_url'), '', $previous), '/');
                $path = rtrim((string) config('app.uploads.avatars_path'), '/') . '/' . $relative;
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            $this->audit('perfil.avatar', 'usuario', (int) $user['id']);
            Flash::success('Tu foto de perfil se actualizó.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }
        return $this->redirect('/perfil');
    }

    public function removeAvatar(Request $request): Response
    {
        $user = Auth::requireLogin();
        $previous = (string) ($user['avatar_url'] ?? '');
        if ($previous !== '') {
            $relative = ltrim(str_replace((string) config('app.uploads.avatars_url'), '', $previous), '/');
            $path = rtrim((string) config('app.uploads.avatars_path'), '/') . '/' . $relative;
            if (is_file($path)) {
                @unlink($path);
            }
        }
        (new UserRepository())->updateAvatar((int) $user['id'], null);
        Flash::success('Quitamos tu foto de perfil.');
        return $this->redirect('/perfil');
    }

    public function password(Request $request): Response
    {
        $user = Auth::requireLogin();
        $current = $request->string('current_password');
        $password = $request->string('password');
        $confirmation = $request->string('password_confirmation');

        $errors = [];
        if (!password_verify($current, (string) $user['password_hash'])) {
            $errors['current_password'] = 'La contraseña actual no es correcta.';
        }
        if (mb_strlen($password) < 10) {
            $errors['password'] = 'La nueva contraseña debe tener al menos 10 caracteres.';
        }
        if ($password !== $confirmation) {
            $errors['password_confirmation'] = 'Las contraseñas no coinciden.';
        }
        if ($errors !== []) {
            $this->withErrors($errors);
            return $this->redirect('/perfil');
        }

        (new UserRepository())->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        $this->audit('perfil.password', 'usuario', (int) $user['id']);
        Flash::success('Tu contraseña se actualizó.');
        return $this->redirect('/perfil');
    }

    public function proof(Request $request): Response
    {
        $user = Auth::requireLogin();
        $file = $request->file('proof');
        if ($file === null) {
            Flash::error('Selecciona tu constancia o credencial en PDF, JPG o PNG.');
            return $this->redirect('/perfil');
        }
        try {
            $path = Upload::proof($file, (int) $user['id']);
            (new UserRepository())->setProof((int) $user['id'], $path);
            $this->audit('perfil.constancia', 'usuario', (int) $user['id']);
            Flash::success('Recibimos tu documento. Un editor revisará tu cuenta.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }
        return $this->redirect('/perfil');
    }

    public function deleteAccount(Request $request): Response
    {
        $user = Auth::requireLogin();
        $password = $request->string('password');
        $confirmation = Str::normalizeEmail($request->string('confirmation'));

        if (!password_verify($password, (string) $user['password_hash'])) {
            Flash::error('La contraseña no es correcta. No eliminamos tu cuenta.');
            return $this->redirect('/perfil');
        }
        if (mb_strtoupper($confirmation) !== 'ELIMINAR') {
            Flash::error('Escribe ELIMINAR para confirmar.');
            return $this->redirect('/perfil');
        }

        (new UserRepository())->deleteAccount((int) $user['id']);
        $this->audit('cuenta.eliminada', 'usuario', (int) $user['id']);
        Auth::logout();
        Flash::info('Tu cuenta se cerró y tus datos personales se anonimizaron.');
        return $this->redirect('/');
    }

    public function publicProfile(Request $request, string $id): Response
    {
        $user = (new UserRepository())->find((int) $id);
        if ($user === null || $user['status'] !== 'verificado') {
            $this->notFound('Este perfil no existe o no es público.');
        }

        $cases = (new CaseRepository())->search(['author_id' => (int) $id], 1, 6);
        $reports = (new ReportRepository())->search(['author_id' => (int) $id], 1, 6);

        return $this->view('profile/public', [
            'title' => $user['name'] . ' · VitroLab MX',
            'metaDescription' => excerpt((string) ($user['bio'] ?? ''), 160) ?: 'Perfil de miembro de VitroLab MX.',
            'user' => $user,
            'cases' => $cases['items'],
            'reports' => $reports['items'],
        ]);
    }

    /** @param array<string, mixed> $user */
    private function completeness(array $user): int
    {
        $checks = [
            trim((string) ($user['name'] ?? '')) !== '',
            trim((string) ($user['career'] ?? '')) !== '',
            (int) ($user['institution_id'] ?? 0) > 0 || trim((string) ($user['institution_other'] ?? '')) !== '',
            trim((string) ($user['bio'] ?? '')) !== '',
            $user['status'] === 'verificado',
        ];
        return (int) round(count(array_filter($checks)) / count($checks) * 100);
    }
}
