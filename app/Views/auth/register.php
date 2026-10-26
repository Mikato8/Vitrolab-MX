<?php
/** @var array<int, array<string, mixed>> $states */
/** @var array<int, array<string, mixed>> $institutions */
$errors = \App\Core\Flash::errors();
$selectedRole = (string) old('role', 'estudiante');
$selectedInstitution = (string) old('institution_id');
?>
<section class="auth-section auth-section-register">
    <div class="container auth-grid auth-grid-wide">
        <div class="auth-panel">
            <span class="eyebrow">Nueva cuenta</span>
            <h1>Crea tu cuenta</h1>
            <p class="auth-lead">Para estudiantes, docentes, investigadores y técnicos que trabajan con material vegetal in vitro. Verificamos tu correo institucional o tu constancia. <strong>No pedimos cédula profesional.</strong></p>

            <?php if ($errors !== []): ?>
                <div class="form-error-summary" role="alert">
                    <?= icon('alert-triangle', 'icon') ?>
                    <div><strong>Revisa los siguientes campos</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
                </div>
            <?php endif; ?>

            <div class="notice notice-info">
                <?= icon('shield', 'icon') ?>
                <div>
                    <strong>Cómo verificamos</strong>
                    <p>Si tu correo pertenece a una institución del directorio o termina en <code>.edu.mx</code>, la cuenta se verifica automáticamente. Si usas un correo personal, sube una constancia o credencial y un editor la revisará.</p>
                </div>
            </div>

            <form class="auth-form" method="post" action="<?= e(url('/registro')) ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="name">Nombre completo <span class="req">*</span></label>
                        <input id="name" name="name" type="text" autocomplete="name" required maxlength="120" value="<?= e(old('name')) ?>" placeholder="Ana Sofía Ramírez">
                    </div>
                    <div class="field">
                        <label for="email">Correo electrónico <span class="req">*</span></label>
                        <input id="email" name="email" type="email" autocomplete="email" required maxlength="180" value="<?= e(old('email')) ?>" placeholder="tu@universidad.edu.mx">
                        <span class="field-help">Si es institucional, verificamos al instante.</span>
                    </div>
                </div>

                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="role">Perfil <span class="req">*</span></label>
                        <select id="role" name="role" required>
                            <?php foreach (['estudiante' => 'Estudiante', 'docente' => 'Docente', 'investigador' => 'Investigador/a', 'tecnico' => 'Técnico/a de laboratorio'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $selectedRole === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="state_code">Estado <span class="req">*</span></label>
                        <select id="state_code" name="state_code" required>
                            <option value="">Selecciona…</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?= e($state['code']) ?>" <?= old('state_code') === $state['code'] ? 'selected' : '' ?>><?= e($state['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="institution_id">Institución</label>
                        <select id="institution_id" name="institution_id">
                            <option value="">Selecciona tu institución…</option>
                            <?php foreach ($institutions as $institution): ?>
                                <option value="<?= (int) $institution['id'] ?>" <?= $selectedInstitution === (string) $institution['id'] ? 'selected' : '' ?>><?= e($institution['name']) ?><?= $institution['acronym'] !== '' ? ' (' . e($institution['acronym']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="field-help">Si no aparece, escribe el nombre abajo.</span>
                    </div>
                    <div class="field">
                        <label for="institution_other">Otra institución</label>
                        <input id="institution_other" name="institution_other" type="text" maxlength="180" value="<?= e(old('institution_other')) ?>" placeholder="Nombre de tu universidad, centro o laboratorio">
                    </div>
                </div>

                <div class="field-grid field-grid-3">
                    <div class="field">
                        <label for="career">Formación o puesto <span class="req">*</span></label>
                        <input id="career" name="career" type="text" maxlength="160" required value="<?= e(old('career')) ?>" placeholder="Ej. Ingeniería en Biotecnología">
                    </div>
                    <div class="field">
                        <label for="semester">Semestre</label>
                        <select id="semester" name="semester">
                            <option value="">No aplicar</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>" <?= (string) old('semester') === (string) $i ? 'selected' : '' ?>><?= $i ?>º</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="orcid">ORCID <span class="optional">(opcional)</span></label>
                        <input id="orcid" name="orcid" type="text" maxlength="30" value="<?= e(old('orcid')) ?>" placeholder="0000-0000-0000-0000">
                    </div>
                </div>

                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="student_id">Matrícula o identificación <span class="optional">(privada)</span></label>
                        <input id="student_id" name="student_id" type="text" maxlength="40" value="<?= e(old('student_id')) ?>" placeholder="Opcional">
                        <span class="field-help">No se muestra públicamente.</span>
                    </div>
                    <div class="field">
                        <label for="proof">Constancia o credencial</label>
                        <input id="proof" name="proof" type="file" accept=".pdf,image/jpeg,image/png">
                        <span class="field-help">PDF, JPG o PNG · máx. 8 MB.</span>
                    </div>
                </div>

                <div class="field">
                    <label for="bio">Semblanza breve <span class="optional">(opcional)</span></label>
                    <textarea id="bio" name="bio" rows="3" maxlength="1500" placeholder="Especies, líneas de trabajo o técnicas que te interesan."><?= e(old('bio')) ?></textarea>
                </div>

                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="password">Contraseña <span class="req">*</span></label>
                        <div class="password-field">
                            <input id="password" name="password" type="password" autocomplete="new-password" minlength="10" required placeholder="Mínimo 10 caracteres">
                            <?php \App\Core\View::partial('partials/password-toggle', ['id' => 'password']); ?>
                        </div>
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Confirmar contraseña <span class="req">*</span></label>
                        <div class="password-field">
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="10" required placeholder="Repite la contraseña">
                            <?php \App\Core\View::partial('partials/password-toggle', ['id' => 'password_confirmation']); ?>
                        </div>
                    </div>
                </div>

                <label class="check-line">
                    <input type="checkbox" name="terms" value="1" <?= old('terms') ? 'checked' : '' ?>>
                    <span>Acepto los <a href="<?= e(url('/terminos')) ?>" target="_blank" rel="noopener">términos de uso</a>, los <a href="<?= e(url('/etica')) ?>" target="_blank" rel="noopener">criterios editoriales</a> y el <a href="<?= e(url('/privacidad')) ?>" target="_blank" rel="noopener">aviso de privacidad</a>.</span>
                </label>

                <button class="btn btn-primary btn-block" type="submit">Crear mi cuenta <?= icon('arrow-right', 'icon icon-sm') ?></button>
            </form>

            <p class="auth-switch">¿Ya tienes cuenta? <a href="<?= e(url('/acceder')) ?>">Inicia sesión</a></p>
        </div>

        <aside class="auth-aside auth-aside-register">
            <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Qué obtienes</span>
            <h2>Un portafolio de trabajo de laboratorio.</h2>
            <ol class="onboarding-steps">
                <li><span>01</span><div><strong>Crea tu perfil</strong><p>Institución, formación y líneas de interés.</p></div></li>
                <li><span>02</span><div><strong>Verifica tu cuenta</strong><p>Correo institucional o constancia. Sin cédula profesional.</p></div></li>
                <li><span>03</span><div><strong>Publica y recibe retroalimentación</strong><p>Casos y reportes con revisión editorial y crédito para tu trabajo.</p></div></li>
            </ol>
            <div class="auth-note">
                <?= icon('lock', 'icon icon-sm') ?>
                <p>Tu correo, matrícula y constancia no se publican. El aviso de privacidad explica cómo tratamos tus datos.</p>
            </div>
            <div class="auth-note">
                <?= icon('check-circle', 'icon icon-sm') ?>
                <p>Si tu correo es <strong>.edu.mx</strong>, no necesitas subir constancia: la verificación es automática.</p>
            </div>
        </aside>
    </div>
</section>
