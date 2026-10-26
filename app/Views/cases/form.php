<?php
/** @var array<string, mixed>|null $case */
/** @var array<string, mixed> $user */
/** @var array<int, array<string, mixed>> $categoriesList */
/** @var array<int, array<string, mixed>> $speciesList */
/** @var array<int, array<string, mixed>> $states */
$isEdit = $case !== null;
$actionUrl = $isEdit ? url('/casos/' . $case['slug']) : url('/casos');
$errors = \App\Core\Flash::errors();
$selectedCategory = (string) old('category_id', $case['category_id'] ?? '');
$selectedSpecies = (string) old('species_id', $case['species_id'] ?? '');
$selectedState = (string) old('state_code', $case['state_code'] ?? ($user['state_code'] ?? ''));
$value = static fn (string $field): string => (string) old($field, $case[$field] ?? '');
?>
<section class="page-hero page-hero-form">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/mi-panel')) ?>">Mi panel</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= $isEdit ? 'Editar caso' : 'Nuevo caso' ?></span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow"><?= $isEdit ? 'Edición' : 'Nueva aportación' ?></span>
                <h1><?= $isEdit ? 'Editar caso de cultivo in vitro' : 'Documenta un caso de cultivo in vitro' ?></h1>
                <p>Escribe para alguien que no estuvo en el laboratorio: material de partida, decisiones, controles, resultados y límites. Un protocolo sin condiciones no se puede repetir.</p>
            </div>
            <div class="form-progress">
                <span class="form-progress-label">Estado</span>
                <strong><?= e($isEdit ? status_label($case['status']) : 'Nuevo borrador') ?></strong>
                <span class="badge badge-<?= e($isEdit ? status_tone($case['status']) : 'neutral') ?>"><?= $isEdit ? e(status_label($case['status'])) : 'Sin guardar' ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container form-layout">
        <form class="case-form" method="post" action="<?= e($actionUrl) ?>" enctype="multipart/form-data" data-case-form>
            <?= csrf_field() ?>

            <?php if ($errors !== []): ?>
                <div class="form-error-summary" role="alert">
                    <?= icon('alert-triangle', 'icon') ?>
                    <div>
                        <strong>Revisa los siguientes campos</strong>
                        <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($user['status'] !== 'verificado'): ?>
                <div class="notice notice-warning">
                    <?= icon('shield', 'icon') ?>
                    <div>
                        <strong>Tu cuenta está pendiente de verificación</strong>
                        <p>Puedes guardar borradores, pero para enviar a revisión necesitas verificar tu correo institucional o subir una constancia en <a href="<?= e(url('/perfil')) ?>">tu perfil</a>.</p>
                    </div>
                </div>
            <?php endif; ?>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">01</span>
                    <div>
                        <h2>Ficha del caso</h2>
                        <p>Un título concreto y un resumen que permita decidir si el caso es relevante.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="title">Título <span class="req">*</span></label>
                    <input id="title" name="title" type="text" maxlength="180" required value="<?= e($value('title')) ?>" placeholder="Ej. Micropropagación de Agave tequilana en inmersión temporal: 4.2 brotes por explante">
                    <div class="field-foot"><span>Entre 10 y 180 caracteres. Incluye especie, método y resultado principal.</span><span data-counter-for="title"></span></div>
                    <?php if (field_error('title')): ?><span class="field-error"><?= e(field_error('title')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="summary">Resumen <span class="req">*</span></label>
                    <textarea id="summary" name="summary" rows="4" maxlength="600" required data-counter placeholder="En 3 o 4 líneas: qué material se usó, cuál era el reto, qué se hizo y qué resultado se obtuvo."><?= e($value('summary')) ?></textarea>
                    <div class="field-foot"><span>Mínimo 30 caracteres.</span><span data-counter-for="summary"></span></div>
                    <?php if (field_error('summary')): ?><span class="field-error"><?= e(field_error('summary')) ?></span><?php endif; ?>
                </div>
                <div class="field-grid field-grid-3">
                    <div class="field">
                        <label for="category_id">Línea de trabajo <span class="req">*</span></label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Selecciona…</option>
                            <?php foreach ($categoriesList as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= $selectedCategory === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['emoji'] . ' ' . $category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (field_error('category_id')): ?><span class="field-error"><?= e(field_error('category_id')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="species_id">Especie <span class="req">*</span></label>
                        <select id="species_id" name="species_id">
                            <option value="">Otra especie…</option>
                            <?php foreach ($speciesList as $species): ?>
                                <option value="<?= (int) $species['id'] ?>" <?= $selectedSpecies === (string) $species['id'] ? 'selected' : '' ?>><?= e($species['scientific_name'] . ' — ' . $species['common_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="species_text">Nombre científico si no está en la lista</label>
                        <input id="species_text" name="species_text" type="text" maxlength="160" value="<?= e($value('species_text')) ?>" placeholder="Ej. Agave cupreata">
                    </div>
                </div>
                <div class="field-grid field-grid-3">
                    <div class="field">
                        <label for="state_code">Estado <span class="req">*</span></label>
                        <select id="state_code" name="state_code" required>
                            <option value="">Selecciona…</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?= e($state['code']) ?>" <?= $selectedState === $state['code'] ? 'selected' : '' ?>><?= e($state['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="municipality">Municipio o localidad</label>
                        <input id="municipality" name="municipality" type="text" maxlength="120" value="<?= e($value('municipality')) ?>" placeholder="Ej. Tequila">
                    </div>
                    <div class="field">
                        <label for="year">Año del trabajo <span class="req">*</span></label>
                        <input id="year" name="year" type="number" min="2000" max="2100" value="<?= e($value('year') ?: (string) date('Y')) ?>" required>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">02</span>
                    <div>
                        <h2>Material y protocolo</h2>
                        <p>La parte que permite repetir el trabajo. Sé específico con concentraciones, tiempos y unidades.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="objective">Objetivo <span class="req">*</span></label>
                    <textarea id="objective" name="objective" rows="3" maxlength="3000" required placeholder="Qué se buscaba y con qué criterio se consideraría logrado."><?= e($value('objective')) ?></textarea>
                    <?php if (field_error('objective')): ?><span class="field-error"><?= e(field_error('objective')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="context">Contexto y material de partida <span class="req">*</span></label>
                    <textarea id="context" name="context" rows="4" maxlength="4000" required placeholder="Procedencia del material, estado sanitario, condiciones del laboratorio y por qué se eligió esta estrategia."><?= e($value('context')) ?></textarea>
                    <?php if (field_error('context')): ?><span class="field-error"><?= e(field_error('context')) ?></span><?php endif; ?>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="explant">Explante <span class="req">*</span></label>
                        <textarea id="explant" name="explant" rows="3" maxlength="2000" required placeholder="Tipo, tamaño, posición y edad del explante."><?= e($value('explant')) ?></textarea>
                        <?php if (field_error('explant')): ?><span class="field-error"><?= e(field_error('explant')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="disinfection">Desinfección <span class="req">*</span></label>
                        <textarea id="disinfection" name="disinfection" rows="3" maxlength="3000" required placeholder="Agentes, concentraciones, tiempos y enjuagues."><?= e($value('disinfection')) ?></textarea>
                        <?php if (field_error('disinfection')): ?><span class="field-error"><?= e(field_error('disinfection')) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="medium">Medio de cultivo <span class="req">*</span></label>
                        <textarea id="medium" name="medium" rows="3" maxlength="3000" required placeholder="Sales, vitaminas, sacarosa, agar, pH y esterilización."><?= e($value('medium')) ?></textarea>
                        <?php if (field_error('medium')): ?><span class="field-error"><?= e(field_error('medium')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="pgr">Reguladores del crecimiento (PGR)</label>
                        <textarea id="pgr" name="pgr" rows="3" maxlength="2000" placeholder="BAP, IBA, NAA, 2,4-D, kinetina, TDZ… con concentración y fase."><?= e($value('pgr')) ?></textarea>
                    </div>
                </div>
                <div class="field">
                    <label for="conditions">Condiciones de incubación <span class="req">*</span></label>
                    <textarea id="conditions" name="conditions" rows="3" maxlength="2000" required placeholder="Temperatura, fotoperiodo, intensidad lumínica, humedad, tipo de recipiente y subcultivos."><?= e($value('conditions')) ?></textarea>
                    <?php if (field_error('conditions')): ?><span class="field-error"><?= e(field_error('conditions')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="protocol">Protocolo paso a paso <span class="req">*</span></label>
                    <textarea id="protocol" name="protocol" rows="8" maxlength="8000" required placeholder="1) Desinfección…&#10;2) Establecimiento…&#10;3) Multiplicación…&#10;4) Enraizamiento…&#10;5) Aclimatación…"><?= e($value('protocol')) ?></textarea>
                    <div class="field-foot"><span>Un paso por línea. Incluye tiempos y número de subcultivos.</span></div>
                    <?php if (field_error('protocol')): ?><span class="field-error"><?= e(field_error('protocol')) ?></span><?php endif; ?>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">03</span>
                    <div>
                        <h2>Resultados y aprendizajes</h2>
                        <p>Reporta lo que ocurrió, incluidos los problemas. Las métricas son opcionales, pero ayudan a comparar.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="results">Resultados <span class="req">*</span></label>
                    <textarea id="results" name="results" rows="5" maxlength="5000" required placeholder="Qué se obtuvo, con qué número de explantes y en cuánto tiempo. Distingue observación de interpretación."><?= e($value('results')) ?></textarea>
                    <?php if (field_error('results')): ?><span class="field-error"><?= e(field_error('results')) ?></span><?php endif; ?>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="challenges">Dificultades y ajustes</label>
                        <textarea id="challenges" name="challenges" rows="3" maxlength="4000" placeholder="Contaminación, oxidación, vitrificación, pérdida de explantes, cambios de último momento."><?= e($value('challenges')) ?></textarea>
                    </div>
                    <div class="field">
                        <label for="lessons">Aprendizajes</label>
                        <textarea id="lessons" name="lessons" rows="3" maxlength="3000" placeholder="Qué harías distinto y qué le dirías a alguien que empieza."><?= e($value('lessons')) ?></textarea>
                    </div>
                </div>
                <div class="field">
                    <span class="field-label">Métricas del caso</span>
                    <div class="metric-inputs">
                        <?php foreach ([
                            'contamination_pct' => ['Contaminación', '%', '0', '100'],
                            'survival_pct' => ['Supervivencia', '%', '0', '100'],
                            'multiplication_rate' => ['Multiplicación', 'brotes/explante', '0', '1000'],
                            'rooting_pct' => ['Enraizamiento', '%', '0', '100'],
                            'acclimatization_pct' => ['Aclimatación', '%', '0', '100'],
                            'duration_weeks' => ['Duración', 'semanas', '1', '260'],
                        ] as $field => [$label, $unit, $min, $max]): ?>
                            <label class="metric-input">
                                <span><?= e($label) ?></span>
                                <input type="number" step="0.01" min="<?= e($min) ?>" max="<?= e($max) ?>" name="<?= e($field) ?>" value="<?= e($value($field)) ?>" placeholder="—">
                                <small><?= e($unit) ?></small>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="field">
                    <label for="references_text">Referencias <span class="req">*</span></label>
                    <textarea id="references_text" name="references_text" rows="3" maxlength="5000" required placeholder="Literatura, manuales o protocolos citados. Una referencia por línea."><?= e($value('references_text')) ?></textarea>
                    <?php if (field_error('references_text')): ?><span class="field-error"><?= e(field_error('references_text')) ?></span><?php endif; ?>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">04</span>
                    <div>
                        <h2>Imágenes y licencia</h2>
                        <p>Hasta 4 imágenes JPG, PNG o WebP de 8 MB. No subas rostros ni datos sensibles sin consentimiento.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="images">Imágenes del caso</label>
                    <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                    <div class="field-foot"><span>Puedes seleccionar varias. Se reencuadran a un máximo de 2,400 px.</span></div>
                </div>
                <?php if ($isEdit && $case['images'] !== []): ?>
                    <div class="existing-images">
                        <?php foreach ($case['images'] as $image): ?>
                            <figure>
                                <img src="<?= e(url('/uploads/casos/' . $image['path'])) ?>" alt="">
                                <figcaption><?= e($image['caption'] ?: 'Imagen del caso') ?></figcaption>
                                <form method="post" action="<?= e(url('/casos/' . $case['slug'] . '/imagenes/' . $image['id'] . '/eliminar')) ?>" onsubmit="return confirm('¿Eliminar esta imagen?')">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-ghost btn-xs" type="submit"><?= icon('trash', 'icon icon-xs') ?> Eliminar</button>
                                </form>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="license">Licencia del contenido</label>
                        <select id="license" name="license">
                            <?php foreach (['CC BY-NC-SA 4.0', 'CC BY 4.0', 'CC BY-SA 4.0', 'CC0 1.0', 'Todos los derechos reservados'] as $license): ?>
                                <option value="<?= e($license) ?>" <?= $value('license') === $license ? 'selected' : '' ?>><?= e($license) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field field-note">
                        <span class="field-label">Antes de enviar</span>
                        <p class="muted">Confirma que tienes autorización para publicar las imágenes y que no incluyes datos personales de terceros sin consentimiento.</p>
                    </div>
                </div>
            </section>

            <div class="form-actions">
                <div class="form-actions-left">
                    <a class="btn btn-ghost" href="<?= e($isEdit ? url('/casos/' . $case['slug']) : url('/mi-panel')) ?>">Cancelar</a>
                </div>
                <div class="form-actions-right">
                    <button class="btn btn-outline" type="submit" name="action" value="draft">Guardar borrador</button>
                    <button class="btn btn-primary" type="submit" name="action" value="submit">Enviar a revisión</button>
                </div>
            </div>
        </form>

        <aside class="form-sidebar">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('info', 'icon icon-sm') ?> Un caso útil</h2>
                <ul class="check-list">
                    <li><?= icon('check', 'icon icon-sm') ?> Declara el material de partida y su estado sanitario.</li>
                    <li><?= icon('check', 'icon icon-sm') ?> Escribe concentraciones con unidades (mg/L, %, min).</li>
                    <li><?= icon('check', 'icon icon-sm') ?> Separa resultados de interpretación.</li>
                    <li><?= icon('check', 'icon icon-sm') ?> Incluye los problemas, no solo los éxitos.</li>
                    <li><?= icon('check', 'icon icon-sm') ?> Cita fuentes verificables.</li>
                </ul>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('shield', 'icon icon-sm') ?> Revisión editorial</h2>
                <p>El comité revisa coherencia, trazabilidad y ética. Puede publicar, pedir cambios o rechazar con notas. No se edita el contenido sin avisarte.</p>
                <a class="text-link" href="<?= e(url('/etica')) ?>">Criterios editoriales <?= icon('arrow-right', 'icon icon-xs') ?></a>
            </div>
            <?php if ($isEdit): ?>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('activity', 'icon icon-sm') ?> Estado</h2>
                <p><span class="badge badge-<?= e(status_tone($case['status'])) ?>"><?= e(status_label($case['status'])) ?></span></p>
                <p class="muted">Folio <?= e($case['folio']) ?> · <?= e(format_date($case['updated_at'], 'd/m/Y H:i')) ?></p>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
