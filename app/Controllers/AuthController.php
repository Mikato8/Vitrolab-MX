<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Logger;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\Str;
use App\Core\Upload;
use App\Repositories\CatalogRepository;
use App\Repositories\UserRepository;
use App\Services\MemberVerifier;

final class AuthController extends BaseController
{
    public function showLogin(Request $request): Response
    {
        if (Auth::check()) {
            return $this->redirect('/mi-panel');
        }
        return $this->view('auth/login', [
            'title' => 'Acceder · VitroLab MX',
            'metaDescription' => 'Inicia sesión para publicar casos y reportes de cultivo in vitro.',
            'robots' => 'noindex,follow',
        ]);
    }

    public function login(Request $request): Response
    {
        $email = trim($request->string('email'));
        $password = $request->string('password');
        $remember = (bool) $request->post('remember');

        $errors = [];
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Escribe un correo electrónico válido.';
        }
        if ($password === '') {
            $errors['password'] = 'Escribe tu contraseña.';
        }

        if ($errors !== []) {
            $this->withErrors($errors);
            $this->flashOld(['email' => $email]);
            return $this->redirect('/acceder');
        }

        $result = Auth::attempt($email, $password, $remember);
        if (!$result['ok']) {
            $this->withErrors(['login' => $result['message']]);
            $this->flashOld(['email' => $email]);
            return $this->redirect('/acceder');
        }

        $this->audit('sesion.inicio', 'usuario', Auth::id());
        Flash::success('Bienvenido de nuevo. Tu panel está listo.');
        $intended = Auth::intendedUrl();
        return $this->redirect($intended === '/' ? '/mi-panel' : $intended);
    }

    public function showRegister(Request $request): Response
    {
        if (Auth::check()) {
            return $this->redirect('/mi-panel');
        }
        $catalog = new CatalogRepository();
        return $this->view('auth/register', [
            'title' => 'Crear cuenta · VitroLab MX',
            'metaDescription' => 'Regístrate con tu correo institucional o una constancia. Comunidad de cultivo in vitro de plantas, sin cédula profesional.',
            'states' => $catalog->states(),
            'institutions' => $catalog->institutions(),
            'robots' => 'noindex,follow',
        ]);
    }

    public function register(Request $request): Response
    {
        $data = [
            'name' => trim($request->string('name')),
            'email' => Str::normalizeEmail($request->string('email')),
            'role' => $request->string('role', 'estudiante'),
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
            'name' => 'nombre completo',
            'email' => 'correo electrónico',
            'role' => 'perfil',
            'career' => 'formación o puesto',
            'semester' => 'semestre',
            'student_id' => 'matrícula o identificación',
            'orcid' => 'ORCID',
            'state_code' => 'estado',
            'bio' => 'semblanza',
        ]);
        $validator->validate([
            'name' => 'required|min:3|max:120',
            'email' => 'required|email|max:180',
            'role' => 'required|in:estudiante,docente,investigador,tecnico',
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
        if ((new UserRepository())->findByEmail($data['email']) !== null) {
            $errors['email'] = 'Ya existe una cuenta con este correo. ¿Quieres iniciar sesión?';
        }

        $password = $request->string('password');
        if (mb_strlen($password) < 10) {
            $errors['password'] = 'La contraseña debe tener al menos 10 caracteres.';
        }
        if ($password !== $request->string('password_confirmation')) {
            $errors['password_confirmation'] = 'Las contraseñas no coinciden.';
        }
        if (!$request->post('terms')) {
            $errors['terms'] = 'Debes aceptar los términos y el aviso de privacidad.';
        }

        if ($errors !== []) {
            $this->withErrors($errors);
            $this->flashOld($data);
            return $this->redirect('/registro');
        }

        $verifier = (new MemberVerifier())->evaluate($data['email'], $data['institution_id'] > 0 ? $data['institution_id'] : null);
        $userId = (new UserRepository())->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $data['role'],
            'status' => $verifier['status'],
            'institution_id' => $verifier['institution_id'],
            'institution_other' => $data['institution_other'],
            'career' => $data['career'],
            'semester' => $data['semester'] !== '' ? (int) $data['semester'] : null,
            'student_id' => $data['student_id'],
            'orcid' => $data['orcid'],
            'state_code' => $data['state_code'],
            'bio' => $data['bio'],
            'verification_method' => $verifier['method'],
        ]);

        $proof = $request->file('proof');
        if ($proof !== null && (int) ($proof['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $path = Upload::proof($proof, $userId);
                (new UserRepository())->setProof($userId, $path);
            } catch (\Throwable $e) {
                Flash::error('No pudimos guardar tu documento: ' . $e->getMessage());
            }
        }

        $user = (new UserRepository())->find($userId);
        if ($user !== null) {
            Auth::login($user, false);
            (new UserRepository())->touchLogin($userId);
        }

        $this->audit('cuenta.registro', 'usuario', $userId, ['metodo' => $verifier['method']]);

        if ($verifier['status'] === 'verificado') {
            Flash::success($verifier['message'] . ' Ya puedes publicar tu primer caso.');
        } else {
            Flash::info($verifier['message'] . ' Mientras se revisa tu cuenta puedes guardar borradores.');
        }

        return $this->redirect('/mi-panel');
    }

    public function logout(Request $request): Response
    {
        $this->audit('sesion.cierre', 'usuario', Auth::id());
        Auth::logout();
        Flash::info('Cerraste sesión correctamente.');
        return $this->redirect('/');
    }

    public function showForgot(Request $request): Response
    {
        return $this->view('auth/forgot', [
            'title' => 'Recuperar contraseña · VitroLab MX',
            'robots' => 'noindex,nofollow',
        ]);
    }

    public function sendReset(Request $request): Response
    {
        $email = Str::normalizeEmail($request->string('email'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->withErrors(['email' => 'Escribe un correo válido.']);
            $this->flashOld(['email' => $email]);
            return $this->redirect('/recuperar');
        }

        $user = (new UserRepository())->findByEmail($email);
        if ($user !== null) {
            $token = (new UserRepository())->createPasswordReset((int) $user['id']);
            $sent = Mailer::passwordReset($user, $token);
            if (!$sent) {
                Logger::info('Enlace de recuperación generado sin correo', [
                    'email' => $email,
                    'link' => url('/recuperar/' . $token),
                ]);
            }
        }

        Flash::success('Si el correo existe en el portal, enviamos un enlace para restablecer la contraseña. Revisa también tu carpeta de spam.');
        return $this->redirect('/recuperar');
    }

    public function showReset(Request $request, string $token): Response
    {
        $user = (new UserRepository())->findByResetToken($token);
        if ($user === null) {
            Flash::error('El enlace no es válido o ya venció. Solicita uno nuevo.');
            return $this->redirect('/recuperar');
        }
        return $this->view('auth/reset', [
            'title' => 'Nueva contraseña · VitroLab MX',
            'token' => $token,
            'robots' => 'noindex,nofollow',
        ]);
    }

    public function reset(Request $request, string $token): Response
    {
        $user = (new UserRepository())->findByResetToken($token);
        if ($user === null) {
            Flash::error('El enlace no es válido o ya venció. Solicita uno nuevo.');
            return $this->redirect('/recuperar');
        }

        $password = $request->string('password');
        $errors = [];
        if (mb_strlen($password) < 10) {
            $errors['password'] = 'La contraseña debe tener al menos 10 caracteres.';
        }
        if ($password !== $request->string('password_confirmation')) {
            $errors['password_confirmation'] = 'Las contraseñas no coinciden.';
        }
        if ($errors !== []) {
            $this->withErrors($errors);
            return $this->redirect('/recuperar/' . $token);
        }

        $repo = new UserRepository();
        $repo->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        $repo->consumeResetToken($token);
        $this->audit('cuenta.password_restablecida', 'usuario', (int) $user['id']);
        Flash::success('Tu contraseña se actualizó. Ya puedes iniciar sesión.');
        return $this->redirect('/acceder');
    }
}
