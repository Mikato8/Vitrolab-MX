<?php
/** @var array<string, mixed> $user */
/** @var array<string, int> $activity */
/** @var array<int, array<string, mixed>> $states */
/** @var array<int, array<string, mixed>> $institutions */
/** @var array<int, array<string, mixed>> $myCases */
/** @var array<int, array<string, mixed>> $myReports */
/** @var int $completeness */
$errors = \App\Core\Flash::errors();
$value = static fn (string $field): string => (string) old($field, $user[$field] ?? '');
$institutionId = (string) old('institution_id', $user['institution_id'] ?? '');
$stateCode = (string) old('state_code', $user['state_code'] ?? '');
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/mi-panel')) ?>">Mi panel</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Mi perfil</span>
        </nav>
        <div class="profile-hero">
            <?= avatar_tag((string) $user['name'], $user['avatar_url'] ?? null, 'avatar-xl') ?>
            <div class="profile-hero-copy">
                <span class="eyebrow"><?= icon('user', 'icon icon-xs') ?> Mi perfil</span>
                <h1><?= e($user['name']) ?></h1>
                <p><?= e(member_role_label($user['role'])) ?><?php if (!empty($user['institution_name'])): ?> · <?= e($user['institution_name']) ?><?php endif; ?><?php if (!empty($user['career'])): ?> · <?= e($user['career']) ?><?php endif; ?></p>
                <div class="profile-badges">
                    <span class="badge badge-<?= $user['status'] === 'verificado' ? 'success' : 'warning' ?>"><?= e(user_status_label($user['status'])) ?></span>
                    <span class="badge badge-soft"><?= e(verification_method_label($user['verification_method'])) ?></span>
                    <?php if (!empty($user['orcid'])): ?><a class="badge badge-neutral" href="https://orcid.org/<?= e($user['orcid']) ?>" target="_blank" rel="noopener">ORCID <?= e($user['orcid']) ?></a><?php endif; ?>
                </div>
            </div>
            <div class="profile-progress">
                <span>Perfil completo</span> <strong><?= (int) $completeness ?>%</strong>
                <div class="profile-progress-bar"><span style="width: <?= (int) $completeness ?>%"></span></div>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container profile-layout">
        <div class="profile-main">
            <?php if ($errors !== []): ?>
                <div class="form-error-summary" role="alert">
                    <?= icon('alert-triangle', 'icon') ?>
                    <div><strong>Revisa los siguientes campos</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
                </div>
            <?php endif; ?>

            <section class="admin-card">
                <div class="admin-card-head"><h2 class="admin-card-title"><?= icon('user', 'icon icon-sm') ?> Datos públicos</h2><span class="muted">Se muestran en tu perfil</span></div>
                <form class="auth-form" method="post" action="<?= e(url('/perfil')) ?>">
                    <?= csrf_field() ?>
                    <div class="field-grid field-grid-2">
                        <div class="field">
                            <label for="name">Nombre completo</label>
                            <input id="name" name="name" type="text" maxlength="120" required value="<?= e($value('name')) ?>">
                        </div>
                        <div class="field">
                            <label for="career">Formación o puesto</label>
                            <input id="career" name="career" type="text" maxlength="160" required value="<?= e($value('career')) ?>" placeholder="Ej. Estudiante de Biotecnología">
                        </div>
                    </div>
                    <div class="field-grid field-grid-2">
                        <div class="field">
                            <label for="institution_id">Institución</label>
                            <select id="institution_id" name="institution_id">
                                <option value="">Selecciona…</option>
                                <?php foreach ($institutions as $institution): ?>
                                    <option value="<?= (int) $institution['id'] ?>" <?= $institutionId === (string) $institution['id'] ? 'selected' : '' ?>><?= e($institution['name']) ?><?= $institution['acronym'] !== '' ? ' (' . e($institution['acronym']) . ')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="institution_other">Otra institución</label>
                            <input id="institution_other" name="institution_other" type="text" maxlength="180" value="<?= e($value('institution_other')) ?>">
                        </div>
                    </div>
                    <div class="field-grid field-grid-3">
                        <div class="field">
                            <label for="semester">Semestre</label>
                            <select id="semester" name="semester">
                                <option value="">No aplicar</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= (string) old('semester', $user['semester'] ?? '') === (string) $i ? 'selected' : '' ?>><?= $i ?>º</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="student_id">Matrícula o ID <span class="optional">(privado)</span></label>
                            <input id="student_id" name="student_id" type="text" maxlength="40" value="<?= e($value('student_id')) ?>">
                        </div>
                        <div class="field">
                            <label for="state_code">Estado</label>
                            <select id="state_code" name="state_code" required>
                                <option value="">Selecciona…</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?= e($state['code']) ?>" <?= $stateCode === $state['code'] ? 'selected' : '' ?>><?= e($state['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label for="orcid">ORCID</label>
                        <input id="orcid" name="orcid" type="text" maxlength="30" value="<?= e($value('orcid')) ?>" placeholder="0000-0000-0000-0000">
                        <?php if (field_error('orcid')): ?><span class="field-error"><?= e(field_error('orcid')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="bio">Semblanza</label>
                        <textarea id="bio" name="bio" rows="4" maxlength="1500"><?= e($value('bio')) ?></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm" type="submit">Guardar cambios</button>
                </form>
            </section>

            <section class="admin-card">
                <div class="admin-card-head"><h2 class="admin-card-title"><?= icon('shield', 'icon icon-sm') ?> Verificación</h2><span class="badge badge-<?= $user['status'] === 'verificado' ? 'success' : 'warning' ?>"><?= e(user_status_label($user['status'])) ?></span></div>
                <p class="muted">Método: <?= e(verification_method_label($user['verification_method'])) ?><?php if (!empty($user['verification_note'])): ?> · <?= e($user['verification_note']) ?><?php endif; ?></p>
                <?php if ($user['status'] !== 'verificado'): ?>
                    <form class="auth-form" method="post" action="<?= e(url('/perfil/constancia')) ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="field">
                            <label for="proof">Constancia o credencial institucional</label>
                            <input id="proof" name="proof" type="file" accept=".pdf,image/jpeg,image/png" required>
                            <span class="field-help">PDF, JPG o PNG · máx. 8 MB. Se guarda fuera del árbol público.</span>
                        </div>
                        <button class="btn btn-outline btn-sm" type="submit">Subir documento</button>
                    </form>
                <?php else: ?>
                    <p class="muted">Tu cuenta está verificada. Si cambias de institución, actualiza tus datos y, si hace falta, sube una nueva constancia.</p>
                <?php endif; ?>
            </section>

            <section class="admin-card">
                <div class="admin-card-head"><h2 class="admin-card-title"><?= icon('lock', 'icon icon-sm') ?> Seguridad</h2></div>
                <form class="auth-form" method="post" action="<?= e(url('/perfil/password')) ?>">
                    <?= csrf_field() ?>
                    <div class="field-grid field-grid-3">
                        <div class="field"><label for="current_password">Contraseña actual</label><input id="current_password" name="current_password" type="password" required autocomplete="current-password"></div>
                        <div class="field"><label for="password">Nueva contraseña</label><input id="password" name="password" type="password" required minlength="10" autocomplete="new-password"></div>
                        <div class="field"><label for="password_confirmation">Confirmar</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="10" autocomplete="new-password"></div>
                    </div>
                    <?php if (field_error('current_password')): ?><span class="field-error"><?= e(field_error('current_password')) ?></span><?php endif; ?>
                    <button class="btn btn-outline btn-sm" type="submit">Cambiar contraseña</button>
                </form>
            </section>

            <section class="admin-card admin-card-danger">
                <div class="admin-card-head"><h2 class="admin-card-title"><?= icon('trash', 'icon icon-sm') ?> Eliminar cuenta</h2></div>
                <p class="muted">Se anonimizan tu nombre, correo, semblanza y documentos. El contenido publicado queda con autoría anonimizada para no romper las discusiones.</p>
                <form class="auth-form" method="post" action="<?= e(url('/perfil/eliminar')) ?>" onsubmit="return confirm('Esta acción no se puede deshacer. ¿Eliminar tu cuenta?')">
                    <?= csrf_field() ?>
                    <div class="field-grid field-grid-2">
                        <div class="field"><label for="delete_password">Contraseña</label><input id="delete_password" name="password" type="password" required></div>
                        <div class="field"><label for="confirmation">Escribe ELIMINAR</label><input id="confirmation" name="confirmation" type="text" required placeholder="ELIMINAR"></div>
                    </div>
                    <button class="btn btn-danger btn-sm" type="submit">Eliminar mi cuenta</button>
                </form>
            </section>
        </div>

        <aside class="profile-aside">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('user', 'icon icon-sm') ?> Foto de perfil</h2>
                <?= avatar_tag((string) $user['name'], $user['avatar_url'] ?? null, 'avatar-xl') ?>
                <form class="auth-form" method="post" action="<?= e(url('/perfil/avatar')) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="field"><input name="avatar" type="file" accept="image/jpeg,image/png,image/webp" required></div>
                    <button class="btn btn-outline btn-sm btn-block" type="submit">Subir foto</button>
                </form>
                <?php if (!empty($user['avatar_url'])): ?>
                <form method="post" action="<?= e(url('/perfil/avatar/eliminar')) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-ghost btn-sm btn-block" type="submit">Quitar foto</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('activity', 'icon icon-sm') ?> Actividad</h2>
                <dl class="sidebar-facts">
                    <div><dt>Casos</dt><dd><?= (int) $activity['cases'] ?> (<?= (int) $activity['cases_published'] ?> publicados)</dd></div>
                    <div><dt>Reportes</dt><dd><?= (int) $activity['reports'] ?> (<?= (int) $activity['reports_published'] ?> publicados)</dd></div>
                    <div><dt>Vistas</dt><dd><?= number_format((int) $activity['views']) ?></dd></div>
                    <div><dt>Comentarios</dt><dd><?= (int) $activity['comments'] ?></dd></div>
                </dl>
                <a class="text-link" href="<?= e(url('/miembro/' . $user['id'])) ?>">Ver mi perfil público <?= icon('arrow-right', 'icon icon-xs') ?></a>
            </div>
            <?php if ($myCases !== [] || $myReports !== []): ?>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('flask', 'icon icon-sm') ?> Mi contenido</h2>
                <div class="sidebar-related">
                    <?php foreach (array_slice($myCases, 0, 3) as $item): ?>
                        <a href="<?= e(url('/casos/' . $item['slug'])) ?>"><strong><?= e(excerpt($item['title'], 58)) ?></strong><small>Caso · <?= e(status_label($item['status'])) ?></small></a>
                    <?php endforeach; ?>
                    <?php foreach (array_slice($myReports, 0, 3) as $item): ?>
                        <a href="<?= e(url('/reportes/' . $item['slug'])) ?>"><strong><?= e(excerpt($item['title'], 58)) ?></strong><small>Reporte · <?= e(status_label($item['status'])) ?></small></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
