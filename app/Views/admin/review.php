<?php
/** @var string $type */
/** @var array<string, mixed> $content */
/** @var array<int, array<string, mixed>> $reviews */
/** @var array<int, array<string, mixed>> $comments */
/** @var array<int, array<string, mixed>> $files */
/** @var array<int, array<string, mixed>> $treatments */
$route = content_route($type, (string) $content['slug']);
$status = (string) $content['status'];
?>
<div class="admin-page-head">
    <div>
        <a class="text-link" href="<?= e(url('/admin/contenidos?tipo=' . $type . '&estado=en_revision')) ?>"><?= icon('arrow-left', 'icon icon-xs') ?> Volver a la cola</a>
        <h1><?= e($content['title']) ?></h1>
        <p><?= e(content_type_label($type)) ?> · <?= e($content['author_name'] ?? '') ?> · <?= e($content['institution_name'] ?? '') ?> · <span class="badge badge-<?= e(status_tone($status)) ?>"><?= e(status_label($status)) ?></span></p>
    </div>
    <div class="admin-page-actions">
        <a class="btn btn-outline btn-sm" href="<?= e($route) ?>" target="_blank" rel="noopener">Ver en el sitio <?= icon('external-link', 'icon icon-xs') ?></a>
        <a class="btn btn-ghost btn-sm" href="<?= e($route . '/pdf') ?>">PDF</a>
    </div>
</div>

<div class="admin-review-layout">
    <div class="admin-review-main">
        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Resumen</h2></div>
            <p class="review-summary"><?= e($content['summary'] ?? '') ?></p>
            <dl class="plot-data plot-data-wide">
                <div><dt>Categoría</dt><dd><?= e($content['category_name'] ?? '—') ?></dd></div>
                <div><dt>Especie</dt><dd><em><?= e($content['scientific_name'] ?? $content['species_text'] ?? '—') ?></em></dd></div>
                <div><dt>Estado</dt><dd><?= e($content['state_name'] ?? '—') ?></dd></div>
                <?php if ($type === 'caso'): ?>
                    <div><dt>Explante</dt><dd><?= e(excerpt($content['explant'] ?? '', 80)) ?></dd></div>
                    <div><dt>Duración</dt><dd><?= $content['duration_weeks'] ? (int) $content['duration_weeks'] . ' semanas' : '—' ?></dd></div>
                    <div><dt>Año</dt><dd><?= (int) $content['year'] ?></dd></div>
                <?php else: ?>
                    <div><dt>Tipo</dt><dd><?= e(report_type_label($content['report_type'] ?? null)) ?></dd></div>
                    <div><dt>Periodo</dt><dd><?= e(format_date($content['period_start'] ?? null, 'd/m/Y')) ?> – <?= e(format_date($content['period_end'] ?? null, 'd/m/Y')) ?></dd></div>
                    <div><dt>Unidad experimental</dt><dd><?= $content['replicates'] ? 'n = ' . (int) $content['replicates'] : '—' ?></dd></div>
                <?php endif; ?>
            </dl>
        </section>

        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Texto para revisión</h2></div>
            <div class="review-text">
                <?php if ($type === 'caso'): ?>
                    <?php foreach (['Objetivo' => 'objective', 'Contexto' => 'context', 'Explante' => 'explant', 'Desinfección' => 'disinfection', 'Medio' => 'medium', 'PGR' => 'pgr', 'Condiciones' => 'conditions', 'Protocolo' => 'protocol', 'Resultados' => 'results', 'Dificultades' => 'challenges', 'Aprendizajes' => 'lessons', 'Referencias' => 'references_text'] as $label => $field): ?>
                        <?php if (trim((string) ($content[$field] ?? '')) !== ''): ?>
                            <h3><?= e($label) ?></h3>
                            <div class="prose prose-sm"><?= nl2br(e($content[$field])) ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach (['Objetivo' => 'objective', 'Hipótesis' => 'hypothesis', 'Diseño' => 'design', 'Tratamientos' => 'treatments_text', 'Variables' => 'variables', 'Resultados' => 'results', 'Análisis' => 'analysis', 'Conclusiones' => 'conclusions', 'Recomendaciones' => 'recommendations', 'Limitaciones' => 'limitations', 'Referencias' => 'references_text'] as $label => $field): ?>
                        <?php if (trim((string) ($content[$field] ?? '')) !== ''): ?>
                            <h3><?= e($label) ?></h3>
                            <div class="prose prose-sm"><?= nl2br(e($content[$field])) ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($treatments !== []): ?>
            <section class="admin-card">
                <div class="admin-card-head"><h2 class="admin-card-title">Tabla de tratamientos</h2></div>
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead><tr><th>Tratamiento</th><th>n</th><th>Respuesta</th><th>Unidad</th><th>Notas</th></tr></thead>
                        <tbody>
                            <?php foreach ($treatments as $treatment): ?>
                                <tr><td><?= e($treatment['label']) ?></td><td><?= $treatment['n'] !== null ? (int) $treatment['n'] : '—' ?></td><td><?= e((string) $treatment['response']) ?></td><td><?= e($treatment['unit']) ?></td><td><?= e($treatment['notes']) ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($files !== []): ?>
            <section class="admin-card">
                <div class="admin-card-head"><h2 class="admin-card-title">Anexos</h2></div>
                <div class="attachment-list">
                    <?php foreach ($files as $file): ?>
                        <a class="attachment" href="<?= e(url('/uploads/archivos/' . $file['path'])) ?>" target="_blank" rel="noopener">
                            <span class="attachment-icon"><?= icon(str_starts_with($file['mime'], 'image/') ? 'eye' : 'file-text', 'icon') ?></span>
                            <span><strong><?= e($file['original_name']) ?></strong><small><?= e(file_kind_label($file['kind'])) ?> · <?= e(format_bytes($file['size_bytes'])) ?></small></span>
                            <?= icon('download', 'icon icon-xs') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Comentarios (<?= count($comments) ?>)</h2></div>
            <?php if ($comments === []): ?>
                <div class="admin-empty"><p>Sin comentarios.</p></div>
            <?php else: ?>
                <div class="admin-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="admin-list-row">
                            <span class="admin-list-main"><strong><?= e(excerpt($comment['body'], 120)) ?></strong><small><?= e($comment['author_name']) ?> · <?= e($comment['status']) ?> · <?= e(time_ago($comment['created_at'])) ?></small></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <aside class="admin-review-side">
        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Decisión editorial</h2></div>
            <form method="post" action="<?= e(url('/admin/revision/' . $type . '/' . $content['id'])) ?>">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="decision">Decisión</label>
                    <select id="decision" name="decision" required>
                        <option value="">Selecciona…</option>
                        <option value="publicar">Publicar</option>
                        <option value="cambios">Solicitar cambios</option>
                        <option value="rechazar">Rechazar</option>
                    </select>
                </div>
                <div class="field">
                    <label for="notes">Notas para el autor</label>
                    <textarea id="notes" name="notes" rows="6" maxlength="3000" placeholder="Explica la decisión, los cambios necesarios y los criterios aplicados…"></textarea>
                    <span class="field-help">Obligatorio para solicitar cambios o rechazar.</span>
                </div>
                <label class="check-line">
                    <input type="checkbox" name="featured" value="1" <?= !empty($content['featured']) ? 'checked' : '' ?>>
                    <span>Destacar en la portada si se publica</span>
                </label>
                <button class="btn btn-primary btn-block" type="submit">Registrar decisión</button>
            </form>
        </section>

        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Historial</h2></div>
            <?php if ($reviews === []): ?>
                <div class="admin-empty"><p>Sin revisiones previas.</p></div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-note">
                        <span class="badge badge-<?= $review['decision'] === 'publicar' ? 'success' : ($review['decision'] === 'cambios' ? 'warning' : 'danger') ?>"><?= e($review['decision'] === 'publicar' ? 'Publicado' : ($review['decision'] === 'cambios' ? 'Cambios' : 'Rechazado')) ?></span>
                        <p><?= e($review['notes'] ?: 'Sin notas.') ?></p>
                        <small><?= e($review['reviewer_name']) ?> · <?= e(time_ago($review['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="admin-card">
            <div class="admin-card-head"><h2 class="admin-card-title">Datos del autor</h2></div>
            <p><strong><?= e($content['author_name'] ?? '') ?></strong><br><span class="muted"><?= e($content['institution_name'] ?? '') ?></span><br><span class="muted"><?= e($content['author_email'] ?? '') ?></span></p>
            <?php if (!empty($content['author_id'])): ?><a class="btn btn-outline btn-block btn-sm" href="<?= e(url('/admin/usuarios/' . $content['author_id'])) ?>">Ver cuenta</a><?php endif; ?>
        </section>
    </aside>
</div>
