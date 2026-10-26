<?php
/** @var array<string, mixed> $report */
/** @var array<int, array<string, mixed>> $treatments */
/** @var array<int, array<string, mixed>> $files */
/** @var array<int, array<string, mixed>> $comments */
/** @var array<int, array<string, mixed>> $reviews */
/** @var bool $reacted */
/** @var bool $bookmarked */
/** @var int $reactionsCount */
/** @var bool $preview */
/** @var bool $canEdit */
$url = url('/reportes/' . $report['slug']);
$publishedYear = $report['published_at'] ? date('Y', strtotime((string) $report['published_at'])) : date('Y');
$citation = ($report['author_name'] ?? 'Autor') . ' (' . $publishedYear . '). ' . $report['title'] . '. VitroLab MX. ' . $url;
$status = (string) $report['status'];
$images = array_values(array_filter($files, static fn (array $file): bool => str_starts_with((string) $file['mime'], 'image/')));
$documents = array_values(array_filter($files, static fn (array $file): bool => !str_starts_with((string) $file['mime'], 'image/')));
$maxResponse = 0.0;
foreach ($treatments as $treatment) {
    $maxResponse = max($maxResponse, (float) $treatment['response']);
}
?>
<section class="case-header case-header-lab">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/reportes')) ?>">Reportes</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= e(excerpt((string) $report['title'], 60)) ?></span>
        </nav>

        <?php if ($preview): ?>
            <div class="notice notice-warning">
                <?= icon('eye', 'icon') ?>
                <div><strong>Vista previa.</strong><p>Este reporte no es público. Estado: <?= e(status_label($status)) ?>.</p></div>
                <?php if (can('moderar')): ?><a class="btn btn-sm btn-dark" href="<?= e(url('/admin/revision/reporte/' . $report['id'])) ?>">Revisar</a><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($report['is_demo']): ?>
            <div class="notice notice-info">
                <?= icon('info', 'icon') ?>
                <div><strong>Contenido demostrativo.</strong><p>Diseño y cifras ilustrativos, elaborados para mostrar el flujo del portal. No sustituyen un reporte experimental real.</p></div>
            </div>
        <?php endif; ?>

        <div class="case-title-row">
            <div class="case-title-main">
                <div class="case-kicker">
                    <span class="crops-pill"><?= icon('file-text', 'icon icon-xs') ?> <?= e(report_type_label($report['report_type'])) ?></span>
                    <span class="badge badge-soft"><?= e($report['category_name'] ?? 'Cultivo in vitro') ?></span>
                    <?php if (!empty($report['species_slug'])): ?><a class="badge badge-neutral" href="<?= e(url('/especies/' . $report['species_slug'])) ?>"><em><?= e($report['scientific_name']) ?></em></a><?php endif; ?>
                    <?php if (!empty($report['featured'])): ?><span class="badge badge-accent"><?= icon('sparkles', 'icon icon-xs') ?> Destacado</span><?php endif; ?>
                </div>
                <h1><?= e($report['title']) ?></h1>
                <p class="case-deck"><?= e($report['summary']) ?></p>
                <div class="case-byline">
                    <?= avatar_tag((string) $report['author_name'], $report['author_avatar_url'] ?? null, 'avatar-sm') ?>
                    <div>
                        <strong><a href="<?= e(url('/miembro/' . $report['author_id'])) ?>"><?= e($report['author_name']) ?></a></strong>
                        <span><?= e($report['institution_name'] ?? ($report['author_career'] ?? 'Miembro de la comunidad')) ?><?php if ($report['author_status'] === 'verificado'): ?> · <span class="verified"><?= icon('check-circle', 'icon icon-xs') ?> Verificado</span><?php endif; ?></span>
                    </div>
                    <span class="byline-divider"></span>
                    <span class="byline-meta"><?= icon('calendar', 'icon icon-xs') ?> <?= e(format_date($report['published_at'] ?? null, 'd M Y')) ?></span>
                    <span class="byline-meta"><?= icon('eye', 'icon icon-xs') ?> <?= number_format((int) $report['views']) ?> vistas</span>
                    <span class="byline-meta"><?= icon('download', 'icon icon-xs') ?> <?= count($files) ?> <?= plural(count($files), 'anexo', 'anexos') ?></span>
                </div>
            </div>
            <div class="case-title-actions">
                <?php if ($canEdit): ?>
                    <a class="btn btn-outline btn-sm" href="<?= e(url('/reportes/' . $report['slug'] . '/editar')) ?>"><?= icon('edit', 'icon icon-xs') ?> Editar</a>
                <?php endif; ?>
                <a class="btn btn-dark btn-sm" href="<?= e(url('/reportes/' . $report['slug'] . '/pdf')) ?>"><?= icon('download', 'icon icon-xs') ?> PDF</a>
                <button class="btn btn-ghost btn-sm" type="button" data-copy="<?= e($url) ?>"><?= icon('share', 'icon icon-xs') ?> Compartir</button>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight case-body">
    <div class="container case-layout">
        <article class="case-content">
            <?php if ($images !== []): ?>
                <div class="case-gallery">
                    <?php foreach ($images as $image): ?>
                        <figure class="case-figure">
                            <img src="<?= e(url('/uploads/archivos/' . $image['path'])) ?>" alt="<?= e($image['original_name']) ?>" loading="lazy">
                            <figcaption><?= e($image['original_name']) ?> · <?= e(file_kind_label($image['kind'])) ?></figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="metric-band metric-band-report">
                <div class="metric"><span class="metric-label">Tratamientos</span><strong class="metric-value"><?= count($treatments) ?></strong><span class="metric-hint">comparados</span></div>
                <div class="metric"><span class="metric-label">Unidad experimental</span><strong class="metric-value"><?= $report['replicates'] ? 'n = ' . (int) $report['replicates'] : '—' ?></strong><span class="metric-hint">por tratamiento</span></div>
                <div class="metric"><span class="metric-label">Periodo</span><strong class="metric-value metric-value-date"><?= e(format_date($report['period_start'] ?? null, 'M Y')) ?> – <?= e(format_date($report['period_end'] ?? null, 'M Y')) ?></strong><span class="metric-hint">ejecución</span></div>
                <div class="metric"><span class="metric-label">Anexos</span><strong class="metric-value"><?= count($files) ?></strong><span class="metric-hint">figuras y datos</span></div>
            </div>

            <section class="prose-section">
                <h2><span class="section-index">01</span> Objetivo e hipótesis</h2>
                <p><?= nl2br(e($report['objective'])) ?></p>
                <?php if (trim((string) $report['hypothesis']) !== ''): ?>
                    <div class="hypothesis-box"><span>Hipótesis</span><p><?= nl2br(e($report['hypothesis'])) ?></p></div>
                <?php endif; ?>
            </section>

            <section class="prose-section">
                <h2><span class="section-index">02</span> Diseño experimental</h2>
                <p><?= nl2br(e($report['design'])) ?></p>
                <div class="ficha-grid ficha-grid-2">
                    <div class="ficha-item"><span>Tratamientos</span><p><?= nl2br(e($report['treatments_text'])) ?></p></div>
                    <div class="ficha-item"><span>Variables medidas</span><p><?= nl2br(e($report['variables'])) ?></p></div>
                </div>
            </section>

            <?php if ($treatments !== []): ?>
            <section class="prose-section">
                <h2><span class="section-index">03</span> Tratamientos y respuesta</h2>
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead><tr><th>Tratamiento</th><th>n</th><th>Respuesta</th><th>Unidad</th><th>Notas</th></tr></thead>
                        <tbody>
                            <?php foreach ($treatments as $treatment): ?>
                                <tr>
                                    <td><?= e($treatment['label']) ?></td>
                                    <td><?= $treatment['n'] !== null ? (int) $treatment['n'] : '—' ?></td>
                                    <td><strong><?= $treatment['response'] !== null ? e(rtrim(rtrim(number_format((float) $treatment['response'], 2, '.', ','), '0'), '.')) : '—' ?></strong></td>
                                    <td><?= e($treatment['unit']) ?></td>
                                    <td><?= e($treatment['notes']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($maxResponse > 0): ?>
                <div class="chart-card">
                    <span class="chart-title"><?= icon('activity', 'icon icon-xs') ?> Comparación de respuestas</span>
                    <div class="chart-bars">
                        <?php foreach ($treatments as $treatment): ?>
                            <div class="chart-row">
                                <span class="chart-label"><?= e(excerpt($treatment['label'], 46)) ?></span>
                                <span class="chart-track"><span style="width: <?= e((string) max(2, ((float) $treatment['response'] / $maxResponse) * 100)) ?>%"></span></span>
                                <strong><?= e(rtrim(rtrim(number_format((float) $treatment['response'], 2, '.', ','), '0'), '.')) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <span class="chart-foot">Unidad: <?= e($treatments[0]['unit'] ?? '') ?>. La barra es proporcional a la respuesta reportada.</span>
                </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <section class="prose-section">
                <h2><span class="section-index">04</span> Resultados</h2>
                <p><?= nl2br(e($report['results'])) ?></p>
            </section>

            <section class="prose-section">
                <h2><span class="section-index">05</span> Análisis</h2>
                <p><?= nl2br(e($report['analysis'])) ?></p>
            </section>

            <section class="prose-section prose-highlight">
                <h2><span class="section-index">06</span> Conclusiones</h2>
                <p><?= nl2br(e($report['conclusions'])) ?></p>
            </section>

            <section class="prose-section">
                <h2><span class="section-index">07</span> Recomendaciones y límites</h2>
                <div class="ficha-grid ficha-grid-2">
                    <div class="ficha-item"><span>Recomendaciones</span><p><?= nl2br(e($report['recommendations'])) ?></p></div>
                    <div class="ficha-item"><span>Limitaciones</span><p><?= nl2br(e($report['limitations'])) ?></p></div>
                </div>
            </section>

            <?php if ($documents !== []): ?>
            <section class="prose-section">
                <h2><span class="section-index">08</span> Archivos de datos</h2>
                <div class="attachment-list">
                    <?php foreach ($documents as $file): ?>
                        <a class="attachment" href="<?= e(url('/uploads/archivos/' . $file['path'])) ?>" target="_blank" rel="noopener">
                            <span class="attachment-icon"><?= icon(str_contains($file['mime'], 'csv') || str_contains($file['mime'], 'sheet') ? 'database' : 'file-text', 'icon') ?></span>
                            <span><strong><?= e($file['original_name']) ?></strong><small><?= e(file_kind_label($file['kind'])) ?> · <?= e(format_bytes($file['size_bytes'])) ?></small></span>
                            <?= icon('download', 'icon icon-xs') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if (!empty($report['linked_case_slug'])): ?>
            <section class="prose-section">
                <h2><span class="section-index">09</span> Caso vinculado</h2>
                <a class="linked-report" href="<?= e(url('/casos/' . $report['linked_case_slug'])) ?>">
                    <span class="report-icon report-icon-sm"><?= icon('flask', 'icon icon-sm') ?></span>
                    <span><strong><?= e($report['linked_case_title']) ?></strong><small>Protocolo completo del material y las condiciones</small></span>
                    <?= icon('arrow-right', 'icon icon-xs') ?>
                </a>
            </section>
            <?php endif; ?>

            <section class="prose-section">
                <h2><span class="section-index">10</span> Referencias y cómo citar</h2>
                <div class="references-block"><?= nl2br(e($report['references_text'])) ?></div>
                <div class="citation-box">
                    <span class="citation-label"><?= icon('copy', 'icon icon-xs') ?> Cita sugerida</span>
                    <p><?= e($citation) ?></p>
                    <span class="citation-license">Licencia: <?= e($report['license']) ?> · Folio <?= e($report['folio']) ?></span>
                </div>
            </section>

            <section class="comments-section" id="comentarios">
                <div class="section-head section-head-small">
                    <div>
                        <span class="eyebrow">Discusión</span>
                        <h2><?= count($comments) ?> <?= plural(count($comments), 'comentario', 'comentarios') ?></h2>
                    </div>
                    <div class="reaction-row">
                        <form method="post" action="<?= e(url('/reportes/' . $report['slug'] . '/util')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline btn-sm <?= $reacted ? 'is-active' : '' ?>" type="submit"><?= icon('sprout', 'icon icon-xs') ?> Útil · <?= (int) $reactionsCount ?></button>
                        </form>
                        <?php if (auth()): ?>
                        <form method="post" action="<?= e(url('/reportes/' . $report['slug'] . '/guardar')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-ghost btn-sm <?= $bookmarked ? 'is-active' : '' ?>" type="submit"><?= icon('bookmark', 'icon icon-xs') ?> <?= $bookmarked ? 'Guardado' : 'Guardar' ?></button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($comments === []): ?>
                    <p class="muted">Todavía no hay comentarios. Pregunta por un detalle del diseño o comparte una réplica.</p>
                <?php else: ?>
                    <div class="comment-list">
                        <?php foreach ($comments as $comment): ?>
                            <article class="comment">
                                <?= avatar_tag((string) $comment['author_name'], $comment['author_avatar'] ?? null, 'avatar-sm') ?>
                                <div class="comment-body">
                                    <div class="comment-head">
                                        <strong><?= e($comment['author_name']) ?></strong>
                                        <span><?= e(member_role_label($comment['author_role'] ?? null)) ?><?php if (!empty($comment['institution_acronym'])): ?> · <?= e($comment['institution_acronym']) ?><?php endif; ?></span>
                                        <time datetime="<?= e($comment['created_at']) ?>"><?= e(time_ago($comment['created_at'])) ?></time>
                                    </div>
                                    <p><?= nl2br(e($comment['body'])) ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (auth()): ?>
                    <?php if (auth()['status'] === 'verificado'): ?>
                    <form class="comment-form" method="post" action="<?= e(url('/reportes/' . $report['slug'] . '/comentarios')) ?>">
                        <?= csrf_field() ?>
                        <label for="comment-body">Pregunta por el diseño, los controles o la interpretación</label>
                        <textarea id="comment-body" name="body" rows="4" maxlength="2000" required placeholder="Ej. ¿El análisis incluyó la finca como factor de bloqueo? En nuestro caso explicaba buena parte de la varianza…"></textarea>
                        <div class="comment-form-foot">
                            <span class="muted">Máximo 2,000 caracteres. Se publica con tu nombre e institución.</span>
                            <button class="btn btn-primary btn-sm" type="submit">Publicar comentario</button>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="notice notice-warning"><?= icon('shield', 'icon') ?><div><strong>Tu cuenta está en revisión.</strong><p>Podrás comentar cuando un editor verifique tu correo o tu constancia.</p></div></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="notice notice-info"><?= icon('user', 'icon') ?><div><strong>¿Quieres comentar?</strong><p><a href="<?= e(url('/acceder')) ?>">Inicia sesión</a> o <a href="<?= e(url('/registro')) ?>">crea una cuenta</a> para participar.</p></div></div>
                <?php endif; ?>
            </section>
        </article>

        <aside class="case-sidebar">
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('user', 'icon icon-sm') ?> Autoría</h2>
                <div class="author-card">
                    <?= avatar_tag((string) $report['author_name'], $report['author_avatar_url'] ?? null, 'avatar-lg') ?>
                    <strong><?= e($report['author_name']) ?></strong>
                    <span><?= e(member_role_label($report['author_role'] ?? null)) ?></span>
                    <span><?= e($report['institution_name'] ?? 'Institución no indicada') ?></span>
                    <?php if (!empty($report['author_orcid'])): ?><a class="orcid-link" href="https://orcid.org/<?= e($report['author_orcid']) ?>" target="_blank" rel="noopener">ORCID <?= e($report['author_orcid']) ?></a><?php endif; ?>
                    <?php if (!empty($report['author_bio'])): ?><p><?= e(excerpt($report['author_bio'], 150)) ?></p><?php endif; ?>
                    <a class="text-link" href="<?= e(url('/miembro/' . $report['author_id'])) ?>">Ver perfil público <?= icon('arrow-right', 'icon icon-xs') ?></a>
                </div>
            </div>

            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('layers', 'icon icon-sm') ?> En una mirada</h2>
                <dl class="sidebar-facts">
                    <div><dt>Tipo</dt><dd><?= e(report_type_label($report['report_type'])) ?></dd></div>
                    <div><dt>Línea</dt><dd><?= e($report['category_name'] ?? '—') ?></dd></div>
                    <div><dt>Especie</dt><dd><em><?= e($report['scientific_name'] ?? $report['species_text']) ?></em></dd></div>
                    <div><dt>Unidad experimental</dt><dd><?= $report['replicates'] ? 'n = ' . (int) $report['replicates'] : 'No indicada' ?></dd></div>
                    <div><dt>Periodo</dt><dd><?= e(format_date($report['period_start'] ?? null, 'M Y')) ?> – <?= e(format_date($report['period_end'] ?? null, 'M Y')) ?></dd></div>
                    <div><dt>Estado</dt><dd><?= e($report['state_name'] ?? 'México') ?></dd></div>
                </dl>
            </div>

            <div class="sidebar-card sidebar-cta">
                <h2 class="sidebar-title"><?= icon('download', 'icon icon-sm') ?> Descargar</h2>
                <p>Reporte completo en PDF con folio, tabla de tratamientos, autoría y cita sugerida.</p>
                <a class="btn btn-dark btn-block" href="<?= e(url('/reportes/' . $report['slug'] . '/pdf')) ?>">Descargar PDF</a>
                <button class="btn btn-ghost btn-block" type="button" data-copy="<?= e($url) ?>">Copiar enlace</button>
            </div>

            <?php if ($files !== []): ?>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('folder', 'icon icon-sm') ?> Archivos</h2>
                <div class="sidebar-files">
                    <?php foreach ($files as $file): ?>
                        <a href="<?= e(url('/uploads/archivos/' . $file['path'])) ?>" target="_blank" rel="noopener">
                            <?= icon(str_starts_with($file['mime'], 'image/') ? 'eye' : 'download', 'icon icon-xs') ?>
                            <span><?= e(excerpt($file['original_name'], 34)) ?><small><?= e(format_bytes($file['size_bytes'])) ?></small></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
