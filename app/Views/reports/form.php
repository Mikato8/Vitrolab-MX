<?php
/** @var array<string, mixed>|null $report */
/** @var array<int, array<string, mixed>> $treatments */
/** @var array<int, array<string, mixed>> $caseOptions */
/** @var array<string, mixed> $user */
/** @var array<int, array<string, mixed>> $categoriesList */
/** @var array<int, array<string, mixed>> $speciesList */
/** @var array<int, array<string, mixed>> $states */
$isEdit = $report !== null;
$actionUrl = $isEdit ? url('/reportes/' . $report['slug']) : url('/reportes');
$errors = \App\Core\Flash::errors();
$selectedCategory = (string) old('category_id', $report['category_id'] ?? '');
$selectedSpecies = (string) old('species_id', $report['species_id'] ?? '');
$selectedState = (string) old('state_code', $report['state_code'] ?? ($user['state_code'] ?? ''));
$selectedType = (string) old('report_type', $report['report_type'] ?? 'ensayo');
$selectedCase = (string) old('case_id', $report['case_id'] ?? '');
$value = static fn (string $field): string => (string) old($field, $report[$field] ?? '');
$old = \App\Core\Flash::oldInput();
$rows = is_array($old['treatments'] ?? null) ? $old['treatments'] : $treatments;
if ($rows === []) {
    $rows = array_fill(0, 4, ['label' => '', 'n' => '', 'response' => '', 'unit' => '', 'notes' => '']);
}
?>
<section class="page-hero page-hero-form">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/mi-panel')) ?>">Mi panel</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= $isEdit ? 'Editar reporte' : 'Nuevo reporte' ?></span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow"><?= $isEdit ? 'Edición' : 'Nueva aportación' ?></span>
                <h1><?= $isEdit ? 'Editar reporte de laboratorio' : 'Documenta un reporte de laboratorio' ?></h1>
                <p>Diseño, tratamientos, variables y resultados. La tabla de tratamientos es opcional, pero un reporte sin datos comparables pierde buena parte de su valor.</p>
            </div>
            <div class="form-progress">
                <span class="form-progress-label">Estado</span>
                <strong><?= e($isEdit ? status_label($report['status']) : 'Nuevo borrador') ?></strong>
                <span class="badge badge-<?= e($isEdit ? status_tone($report['status']) : 'neutral') ?>"><?= $isEdit ? e(status_label($report['status'])) : 'Sin guardar' ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container form-layout">
        <form class="case-form" method="post" action="<?= e($actionUrl) ?>" enctype="multipart/form-data" data-report-form>
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
                        <p>Puedes guardar borradores. Para enviar a revisión, verifica tu correo institucional o sube una constancia en <a href="<?= e(url('/perfil')) ?>">tu perfil</a>.</p>
                    </div>
                </div>
            <?php endif; ?>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">01</span>
                    <div>
                        <h2>Ficha del reporte</h2>
                        <p>Qué se estudió, en qué especie y durante qué periodo.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="title">Título <span class="req">*</span></label>
                    <input id="title" name="title" type="text" maxlength="180" required value="<?= e($value('title')) ?>" placeholder="Ej. Efecto de BAP e IBA en la proliferación de brotes de Agave tequilana">
                    <div class="field-foot"><span>Entre 10 y 180 caracteres. Nombra el factor, la especie y la respuesta.</span></div>
                    <?php if (field_error('title')): ?><span class="field-error"><?= e(field_error('title')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="summary">Resumen <span class="req">*</span></label>
                    <textarea id="summary" name="summary" rows="4" maxlength="600" required data-counter placeholder="En 3 o 4 líneas: qué se comparó, con cuántas unidades, qué se encontró y qué se concluye."><?= e($value('summary')) ?></textarea>
                    <div class="field-foot"><span>Mínimo 30 caracteres.</span><span data-counter-for="summary"></span></div>
                    <?php if (field_error('summary')): ?><span class="field-error"><?= e(field_error('summary')) ?></span><?php endif; ?>
                </div>
                <div class="field-grid field-grid-3">
                    <div class="field">
                        <label for="report_type">Tipo de reporte <span class="req">*</span></label>
                        <select id="report_type" name="report_type" required>
                            <?php foreach (['ensayo' => 'Ensayo experimental', 'protocolo' => 'Protocolo', 'diagnostico' => 'Diagnóstico', 'seguimiento' => 'Seguimiento', 'validacion' => 'Validación', 'datos' => 'Conjunto de datos'] as $valueOption => $label): ?>
                                <option value="<?= e($valueOption) ?>" <?= $selectedType === $valueOption ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="category_id">Línea de trabajo <span class="req">*</span></label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Selecciona…</option>
                            <?php foreach ($categoriesList as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= $selectedCategory === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (field_error('category_id')): ?><span class="field-error"><?= e(field_error('category_id')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="case_id">Caso vinculado</label>
                        <select id="case_id" name="case_id">
                            <option value="">Sin caso vinculado</option>
                            <?php foreach ($caseOptions as $option): ?>
                                <option value="<?= (int) $option['id'] ?>" <?= $selectedCase === (string) $option['id'] ? 'selected' : '' ?>><?= e(excerpt($option['title'], 70)) ?><?= $option['status'] !== 'publicado' ? ' (borrador)' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="field-grid field-grid-3">
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
                    <div class="field">
                        <label for="state_code">Estado <span class="req">*</span></label>
                        <select id="state_code" name="state_code" required>
                            <option value="">Selecciona…</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?= e($state['code']) ?>" <?= $selectedState === $state['code'] ? 'selected' : '' ?>><?= e($state['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="period_start">Inicio del periodo</label>
                        <input id="period_start" name="period_start" type="date" value="<?= e($value('period_start')) ?>">
                    </div>
                    <div class="field">
                        <label for="period_end">Fin del periodo</label>
                        <input id="period_end" name="period_end" type="date" value="<?= e($value('period_end')) ?>">
                        <?php if (field_error('period_end')): ?><span class="field-error"><?= e(field_error('period_end')) ?></span><?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">02</span>
                    <div>
                        <h2>Diseño y variables</h2>
                        <p>Qué se comparó, cómo se asignaron las unidades y qué se midió.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="objective">Objetivo <span class="req">*</span></label>
                    <textarea id="objective" name="objective" rows="3" maxlength="4000" required placeholder="Verbo de acción + variable + condición + criterio de éxito."><?= e($value('objective')) ?></textarea>
                    <?php if (field_error('objective')): ?><span class="field-error"><?= e(field_error('objective')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="hypothesis">Hipótesis</label>
                    <textarea id="hypothesis" name="hypothesis" rows="2" maxlength="3000" placeholder="Qué se esperaba y por qué. Si el reporte es exploratorio, dilo."><?= e($value('hypothesis')) ?></textarea>
                </div>
                <div class="field">
                    <label for="design">Diseño experimental <span class="req">*</span></label>
                    <textarea id="design" name="design" rows="4" maxlength="4000" required placeholder="Diseño, factores, niveles, número de repeticiones, unidad experimental y análisis previsto."><?= e($value('design')) ?></textarea>
                    <?php if (field_error('design')): ?><span class="field-error"><?= e(field_error('design')) ?></span><?php endif; ?>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="treatments_text">Descripción de tratamientos <span class="req">*</span></label>
                        <textarea id="treatments_text" name="treatments_text" rows="4" maxlength="4000" required placeholder="T0, T1, T2… con concentraciones y controles."><?= e($value('treatments_text')) ?></textarea>
                        <?php if (field_error('treatments_text')): ?><span class="field-error"><?= e(field_error('treatments_text')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="variables">Variables medidas <span class="req">*</span></label>
                        <textarea id="variables" name="variables" rows="4" maxlength="3000" required placeholder="Variables de respuesta, unidad y momento de medición."><?= e($value('variables')) ?></textarea>
                        <?php if (field_error('variables')): ?><span class="field-error"><?= e(field_error('variables')) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="field">
                    <label for="replicates">Unidades experimentales por tratamiento</label>
                    <input id="replicates" name="replicates" type="number" min="1" max="100000" value="<?= e($value('replicates')) ?>" placeholder="Ej. 30">
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">03</span>
                    <div>
                        <h2>Tabla de tratamientos</h2>
                        <p>Una fila por tratamiento. Puedes dejar filas vacías; se ignoran al guardar.</p>
                    </div>
                </div>
                <div class="treatment-editor" data-treatment-editor>
                    <div class="treatment-head">
                        <span>Tratamiento</span><span>n</span><span>Respuesta</span><span>Unidad</span><span>Notas</span>
                    </div>
                    <?php foreach (array_slice($rows, 0, 12) as $row): ?>
                        <div class="treatment-row">
                            <input type="text" name="treatment_label[]" maxlength="160" value="<?= e($row['label'] ?? '') ?>" placeholder="T0 · testigo">
                            <input type="number" name="treatment_n[]" min="0" max="100000" value="<?= e($row['n'] ?? '') ?>" placeholder="30">
                            <input type="number" step="0.01" name="treatment_response[]" value="<?= e($row['response'] ?? '') ?>" placeholder="4.2">
                            <input type="text" name="treatment_unit[]" maxlength="40" value="<?= e($row['unit'] ?? '') ?>" placeholder="brotes/explante">
                            <input type="text" name="treatment_notes[]" maxlength="240" value="<?= e($row['notes'] ?? '') ?>" placeholder="Observación">
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">04</span>
                    <div>
                        <h2>Resultados y discusión</h2>
                        <p>Datos, interpretación y límites. La conclusión debe poder rastrearse hasta los resultados.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="results">Resultados <span class="req">*</span></label>
                    <textarea id="results" name="results" rows="6" maxlength="6000" required placeholder="Qué se observó, con qué números y en qué condiciones. Incluye los resultados inesperados."><?= e($value('results')) ?></textarea>
                    <?php if (field_error('results')): ?><span class="field-error"><?= e(field_error('results')) ?></span><?php endif; ?>
                </div>
                <div class="field">
                    <label for="analysis">Análisis <span class="req">*</span></label>
                    <textarea id="analysis" name="analysis" rows="5" maxlength="6000" required placeholder="Pruebas aplicadas, supuestos, comparaciones y qué parte es interpretación."><?= e($value('analysis')) ?></textarea>
                    <?php if (field_error('analysis')): ?><span class="field-error"><?= e(field_error('analysis')) ?></span><?php endif; ?>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="conclusions">Conclusiones <span class="req">*</span></label>
                        <textarea id="conclusions" name="conclusions" rows="4" maxlength="4000" required placeholder="Respuesta al objetivo, sin extrapolar más allá de los datos."><?= e($value('conclusions')) ?></textarea>
                        <?php if (field_error('conclusions')): ?><span class="field-error"><?= e(field_error('conclusions')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="recommendations">Recomendaciones <span class="req">*</span></label>
                        <textarea id="recommendations" name="recommendations" rows="4" maxlength="4000" required placeholder="Siguiente paso concreto para quien quiera repetir o continuar."><?= e($value('recommendations')) ?></textarea>
                        <?php if (field_error('recommendations')): ?><span class="field-error"><?= e(field_error('recommendations')) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="field-grid field-grid-2">
                    <div class="field">
                        <label for="limitations">Limitaciones <span class="req">*</span></label>
                        <textarea id="limitations" name="limitations" rows="3" maxlength="3000" required placeholder="Qué no permite concluir el diseño, qué faltó y qué sesgos existen."><?= e($value('limitations')) ?></textarea>
                        <?php if (field_error('limitations')): ?><span class="field-error"><?= e(field_error('limitations')) ?></span><?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="references_text">Referencias <span class="req">*</span></label>
                        <textarea id="references_text" name="references_text" rows="3" maxlength="5000" required placeholder="Una referencia por línea."><?= e($value('references_text')) ?></textarea>
                        <?php if (field_error('references_text')): ?><span class="field-error"><?= e(field_error('references_text')) ?></span><?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section-head">
                    <span class="form-step">05</span>
                    <div>
                        <h2>Archivos y licencia</h2>
                        <p>Hasta 3 archivos por envío: figuras (JPG, PNG, WebP), datos (CSV, XLSX) o documentos (PDF, DOCX). Máximo 12 MB cada uno.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="documents">Anexos</label>
                    <input id="documents" name="documents[]" type="file" accept=".pdf,.xlsx,.xls,.csv,.docx,.jpg,.jpeg,.png,.webp" multiple>
                </div>
                <?php if ($isEdit && $report['files_count'] > 0): ?>
                    <div class="existing-files">
                        <?php foreach ((new \App\Repositories\ReportRepository())->files((int) $report['id']) as $file): ?>
                            <div class="existing-file">
                                <span><?= icon(str_starts_with($file['mime'], 'image/') ? 'eye' : 'file-text', 'icon icon-sm') ?></span>
                                <span><strong><?= e($file['original_name']) ?></strong><small><?= e(file_kind_label($file['kind'])) ?> · <?= e(format_bytes($file['size_bytes'])) ?></small></span>
                                <form method="post" action="<?= e(url('/reportes/' . $report['slug'] . '/archivos/' . $file['id'] . '/eliminar')) ?>" onsubmit="return confirm('¿Eliminar este archivo?')">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-ghost btn-xs" type="submit"><?= icon('trash', 'icon icon-xs') ?> Eliminar</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="field">
                    <label for="license">Licencia del contenido</label>
                    <select id="license" name="license">
                        <?php foreach (['CC BY-NC-SA 4.0', 'CC BY 4.0', 'CC BY-SA 4.0', 'CC0 1.0', 'Todos los derechos reservados'] as $license): ?>
                            <option value="<?= e($license) ?>" <?= $value('license') === $license ? 'selected' : '' ?>><?= e($license) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </section>

            <div class="form-actions">
                <div class="form-actions-left">
                    <a class="btn btn-ghost" href="<?= e($isEdit ? url('/reportes/' . $report['slug']) : url('/mi-panel')) ?>">Cancelar</a>
                </div>
                <div class="form-actions-right">
                    <button class="btn btn-outline" type="submit" name="action" value="draft">Guardar borrador</button>
                    <button class="btn btn-primary" type="submit" name="action" value="submit">Enviar a revisión</button>
                </div>
            </div>
        </form>

        <aside class="form-sidebar">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('info', 'icon icon-sm') ?> Un reporte útil</h2>
                <ul class="check-list">
                    <li><?= icon('check', 'icon icon-sm') ?> Una hipótesis explícita, aunque sea exploratoria.</li>
                    <li><?= icon('check', 'icon icon-sm') ?> El tamaño de muestra y la unidad experimental.</li>
                    <li><?= icon('check', 'icon icon-sm') ?> Las variables con su unidad y momento de medición.</li>
                    <li><?= icon('check', 'icon icon-sm') ?> Los resultados negativos o inesperados.</li>
                    <li><?= icon('check', 'icon icon-sm') ?> Las limitaciones del diseño.</li>
                </ul>
            </div>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('database', 'icon icon-sm') ?> Datos abiertos</h2>
                <p>Sube la figura y el CSV de la tabla. Un reporte con datos descargables se puede auditar y reutilizar.</p>
                <a class="text-link" href="<?= e(url('/guias')) ?>">Guía para reportar <?= icon('arrow-right', 'icon icon-xs') ?></a>
            </div>
            <?php if ($isEdit): ?>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('activity', 'icon icon-sm') ?> Estado</h2>
                <p><span class="badge badge-<?= e(status_tone($report['status'])) ?>"><?= e(status_label($report['status'])) ?></span></p>
                <p class="muted">Folio <?= e($report['folio']) ?> · <?= e(format_date($report['updated_at'], 'd/m/Y H:i')) ?></p>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
