<?php
/** @var array<string, mixed> $case */
/** @var array<int, array<string, mixed>> $comments */
/** @var array<int, array<string, mixed>> $reviews */
/** @var array<int, array<string, mixed>> $similar */
/** @var array<int, array<string, mixed>> $linkedReports */
/** @var bool $reacted */
/** @var bool $bookmarked */
/** @var int $reactionsCount */
/** @var bool $preview */
/** @var bool $canEdit */
/** @var bool $canModerate */
$caseUrl = url('/casos/' . $case['slug']);
$publishedYear = $case['published_at'] ? date('Y', strtotime((string) $case['published_at'])) : date('Y');
$citation = ($case['author_name'] ?? 'Autor') . ' (' . $publishedYear . '). ' . $case['title'] . '. VitroLab MX. ' . $caseUrl;
$status = (string) $case['status'];
$metrics = [
    ['label' => 'Contaminación', 'value' => $case['contamination_pct'] !== null ? pct($case['contamination_pct']) : null, 'hint' => 'menor es mejor'],
    ['label' => 'Supervivencia', 'value' => $case['survival_pct'] !== null ? pct($case['survival_pct']) : null, 'hint' => 'explantes viables'],
    ['label' => 'Multiplicación', 'value' => $case['multiplication_rate'] !== null ? ratio($case['multiplication_rate']) : null, 'hint' => 'brotes/explante'],
    ['label' => 'Enraizamiento', 'value' => $case['rooting_pct'] !== null ? pct($case['rooting_pct']) : null, 'hint' => 'brotes enraizados'],
    ['label' => 'Aclimatación', 'value' => $case['acclimatization_pct'] !== null ? pct($case['acclimatization_pct']) : null, 'hint' => 'supervivencia ex vitro'],
];
?>
<section class="case-header case-header-lab">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/casos')) ?>">Casos</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= e(excerpt((string) $case['title'], 60)) ?></span>
        </nav>

        <?php if ($preview): ?>
            <div class="notice notice-warning">
                <?= icon('eye', 'icon') ?>
                <div>
                    <strong>Vista previa.</strong>
                    <p>Este caso no es público. Estado actual: <?= e(status_label($status)) ?>. Solo el autor y el comité editorial pueden verlo.</p>
                </div>
                <?php if ($canModerate): ?>
                    <a class="btn btn-sm btn-dark" href="<?= e(url('/admin/revision/caso/' . $case['id'])) ?>">Revisar en el panel</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($case['is_demo']): ?>
            <div class="notice notice-info">
                <?= icon('info', 'icon') ?>
                <div>
                    <strong>Contenido demostrativo.</strong>
                    <p>Este caso se elaboró para mostrar el flujo editorial. Las cifras son ilustrativas y no constituyen resultados experimentales publicados.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="case-title-row">
            <div class="case-title-main">
                <div class="case-kicker">
                    <span class="crops-pill"><?= e($case['category_emoji'] ?? '🌿') ?> <?= e($case['category_name'] ?? 'Cultivo in vitro') ?></span>
                    <?php if (!empty($case['species_slug'])): ?>
                        <a class="badge badge-soft" href="<?= e(url('/especies/' . $case['species_slug'])) ?>"><em><?= e($case['scientific_name']) ?></em></a>
                    <?php endif; ?>
                    <span class="badge badge-neutral"><?= e((string) $case['year']) ?></span>
                    <span class="badge badge-neutral"><?= icon('map-pin', 'icon icon-xs') ?> <?= e($case['state_name'] ?? 'México') ?></span>
                    <?php if (!empty($case['featured'])): ?><span class="badge badge-accent"><?= icon('sparkles', 'icon icon-xs') ?> Destacado</span><?php endif; ?>
                </div>
                <h1><?= e($case['title']) ?></h1>
                <p class="case-deck"><?= e($case['summary']) ?></p>
                <div class="case-byline">
                    <?= avatar_tag((string) $case['author_name'], $case['author_avatar_url'] ?? null, 'avatar-sm') ?>
                    <div>
                        <strong><a href="<?= e(url('/miembro/' . $case['author_id'])) ?>"><?= e($case['author_name']) ?></a></strong>
                        <span><?= e($case['institution_name'] ?? ($case['author_career'] ?? 'Miembro de la comunidad')) ?><?php if ($case['author_status'] === 'verificado'): ?> · <span class="verified"><?= icon('check-circle', 'icon icon-xs') ?> Verificado</span><?php endif; ?></span>
                    </div>
                    <span class="byline-divider"></span>
                    <span class="byline-meta"><?= icon('calendar', 'icon icon-xs') ?> <?= e(format_date($case['published_at'] ?? null, 'd M Y')) ?></span>
                    <span class="byline-meta"><?= icon('eye', 'icon icon-xs') ?> <?= number_format((int) $case['views']) ?> vistas</span>
                    <span class="byline-meta"><?= icon('message-circle', 'icon icon-xs') ?> <?= (int) $case['comments_count'] ?> comentarios</span>
                </div>
            </div>
            <div class="case-title-actions">
                <?php if ($canEdit): ?>
                    <a class="btn btn-outline btn-sm" href="<?= e(url('/casos/' . $case['slug'] . '/editar')) ?>"><?= icon('edit', 'icon icon-xs') ?> Editar</a>
                <?php endif; ?>
                <a class="btn btn-dark btn-sm" href="<?= e(url('/casos/' . $case['slug'] . '/pdf')) ?>"><?= icon('download', 'icon icon-xs') ?> PDF</a>
                <button class="btn btn-ghost btn-sm" type="button" data-copy="<?= e($caseUrl) ?>"><?= icon('share', 'icon icon-xs') ?> Compartir</button>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight case-body">
    <div class="container case-layout">
        <article class="case-content">
            <?php if ($case['images'] !== []): ?>
                <div class="case-gallery">
                    <?php foreach ($case['images'] as $image): ?>
                        <figure class="case-figure">
                            <img src="<?= e(url('/uploads/casos/' . $image['path'])) ?>" alt="<?= e($image['alt_text'] ?? '') ?>" loading="lazy">
                            <?php if (!empty($image['caption'])): ?><figcaption><?= e($image['caption']) ?></figcaption><?php endif; ?>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($case['contamination_pct'] !== null || $case['survival_pct'] !== null || $case['multiplication_rate'] !== null || $case['rooting_pct'] !== null || $case['acclimatization_pct'] !== null): ?>
            <div class="metric-band">
                <?php foreach ($metrics as $metric): ?>
                    <?php if ($metric['value'] !== null): ?>
                    <div class="metric">
                        <span class="metric-label"><?= e($metric['label']) ?></span>
                        <strong class="metric-value"><?= e($metric['value']) ?></strong>
                        <span class="metric-hint"><?= e($metric['hint']) ?></span>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if ($case['duration_weeks']): ?><div class="metric"><span class="metric-label">Duración</span><strong class="metric-value"><?= (int) $case['duration_weeks'] ?></strong><span class="metric-hint">semanas</span></div><?php endif; ?>
            </div>
            <?php endif; ?>

            <section class="prose-section">
                <h2><span class="section-index">01</span> Objetivo</h2>
                <p><?= nl2br(e($case['objective'])) ?></p>
            </section>

            <section class="prose-section">
                <h2><span class="section-index">02</span> Contexto y material de partida</h2>
                <p><?= nl2br(e($case['context'])) ?></p>
            </section>

            <section class="prose-section">
                <h2><span class="section-index">03</span> Ficha técnica del protocolo</h2>
                <div class="ficha-grid">
                    <div class="ficha-item"><span>Explante</span><p><?= nl2br(e($case['explant'])) ?></p></div>
                    <div class="ficha-item"><span>Desinfección</span><p><?= nl2br(e($case['disinfection'])) ?></p></div>
                    <div class="ficha-item"><span>Medio de cultivo</span><p><?= nl2br(e($case['medium'])) ?></p></div>
                    <div class="ficha-item"><span>Reguladores (PGR)</span><p><?= nl2br(e($case['pgr'] ?: 'No se utilizaron reguladores o no se especificaron.')) ?></p></div>
                    <div class="ficha-item"><span>Condiciones de incubación</span><p><?= nl2br(e($case['conditions'])) ?></p></div>
                    <div class="ficha-item"><span>Especie / familia</span><p><em><?= e($case['scientific_name'] ?? $case['species_text']) ?></em><?php if (!empty($case['family'])): ?> · <?= e($case['family']) ?><?php endif; ?><br><?= e($case['category_name'] ?? '') ?></p></div>
                </div>
            </section>

            <section class="prose-section">
                <h2><span class="section-index">04</span> Protocolo paso a paso</h2>
                <div class="protocol-timeline">
                    <?php
                    $steps = preg_split('/\R/u', trim((string) $case['protocol'])) ?: [];
                    $n = 0;
                    foreach ($steps as $step):
                        $step = trim($step);
                        if ($step === '') continue;
                        $n++;
                    ?>
                        <div class="protocol-step">
                            <span class="protocol-number"><?= str_pad((string) $n, 2, '0', STR_PAD_LEFT) ?></span>
                            <p><?= e($step) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="prose-section">
                <h2><span class="section-index">05</span> Resultados</h2>
                <p><?= nl2br(e($case['results'])) ?></p>
            </section>

            <?php if (trim((string) $case['challenges']) !== ''): ?>
            <section class="prose-section">
                <h2><span class="section-index">06</span> Dificultades y ajustes</h2>
                <p><?= nl2br(e($case['challenges'])) ?></p>
            </section>
            <?php endif; ?>

            <?php if (trim((string) $case['lessons']) !== ''): ?>
            <section class="prose-section prose-highlight">
                <h2><span class="section-index">07</span> Aprendizajes</h2>
                <p><?= nl2br(e($case['lessons'])) ?></p>
            </section>
            <?php endif; ?>

            <?php if ($linkedReports !== []): ?>
            <section class="prose-section">
                <h2><span class="section-index">08</span> Reportes vinculados</h2>
                <div class="linked-list">
                    <?php foreach ($linkedReports as $report): ?>
                        <a class="linked-report" href="<?= e(url('/reportes/' . $report['slug'])) ?>">
                            <span class="report-icon report-icon-sm"><?= icon('file-text', 'icon icon-sm') ?></span>
                            <span><strong><?= e($report['title']) ?></strong><small><?= e(report_type_label($report['report_type'])) ?> · <?= e($report['author_name']) ?></small></span>
                            <?= icon('arrow-right', 'icon icon-xs') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <section class="prose-section">
                <h2><span class="section-index">09</span> Referencias y cómo citar</h2>
                <div class="references-block"><?= nl2br(e($case['references_text'])) ?></div>
                <div class="citation-box">
                    <span class="citation-label"><?= icon('copy', 'icon icon-xs') ?> Cita sugerida</span>
                    <p><?= e($citation) ?></p>
                    <span class="citation-license">Licencia: <?= e($case['license']) ?> · Folio <?= e($case['folio']) ?></span>
                </div>
            </section>

            <section class="comments-section" id="comentarios">
                <div class="section-head section-head-small">
                    <div>
                        <span class="eyebrow">Discusión</span>
                        <h2><?= count($comments) ?> <?= plural(count($comments), 'comentario', 'comentarios') ?></h2>
                    </div>
                    <div class="reaction-row">
                        <form method="post" action="<?= e(url('/casos/' . $case['slug'] . '/util')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline btn-sm <?= $reacted ? 'is-active' : '' ?>" type="submit"><?= icon('sprout', 'icon icon-xs') ?> Útil · <?= (int) $reactionsCount ?></button>
                        </form>
                        <?php if (auth()): ?>
                        <form method="post" action="<?= e(url('/casos/' . $case['slug'] . '/guardar')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-ghost btn-sm <?= $bookmarked ? 'is-active' : '' ?>" type="submit"><?= icon('bookmark', 'icon icon-xs') ?> <?= $bookmarked ? 'Guardado' : 'Guardar' ?></button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($comments === []): ?>
                    <p class="muted">Todavía no hay comentarios. Si replicaste el protocolo o tienes una duda concreta, abre la conversación.</p>
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
                    <form class="comment-form" method="post" action="<?= e(url('/casos/' . $case['slug'] . '/comentarios')) ?>">
                        <?= csrf_field() ?>
                        <label for="comment-body">Aporta un dato, una duda o una observación reproducible</label>
                        <textarea id="comment-body" name="body" rows="4" maxlength="2000" required placeholder="Ej. En nuestro ensayo con la misma especie, bajar el BAP a 1.5 mg/L redujo la vitrificación sin afectar la tasa…"></textarea>
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
                    <?= avatar_tag((string) $case['author_name'], $case['author_avatar_url'] ?? null, 'avatar-lg') ?>
                    <strong><?= e($case['author_name']) ?></strong>
                    <span><?= e(member_role_label($case['author_role'] ?? null)) ?></span>
                    <span><?= e($case['institution_name'] ?? 'Institución no indicada') ?></span>
                    <?php if (!empty($case['author_orcid'])): ?><a class="orcid-link" href="https://orcid.org/<?= e($case['author_orcid']) ?>" target="_blank" rel="noopener">ORCID <?= e($case['author_orcid']) ?></a><?php endif; ?>
                    <?php if (!empty($case['author_bio'])): ?><p><?= e(excerpt($case['author_bio'], 150)) ?></p><?php endif; ?>
                    <a class="text-link" href="<?= e(url('/miembro/' . $case['author_id'])) ?>">Ver perfil público <?= icon('arrow-right', 'icon icon-xs') ?></a>
                </div>
            </div>

            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('layers', 'icon icon-sm') ?> En una mirada</h2>
                <dl class="sidebar-facts">
                    <div><dt>Línea</dt><dd><?= e($case['category_name'] ?? '—') ?></dd></div>
                    <div><dt>Especie</dt><dd><em><?= e($case['scientific_name'] ?? $case['species_text']) ?></em></dd></div>
                    <div><dt>Explante</dt><dd><?= e(excerpt($case['explant'], 90)) ?></dd></div>
                    <div><dt>Duración</dt><dd><?= $case['duration_weeks'] ? (int) $case['duration_weeks'] . ' semanas' : 'No indicada' ?></dd></div>
                    <div><dt>Estado</dt><dd><?= e($case['state_name'] ?? 'México') ?><?= !empty($case['municipality']) ? ' · ' . e($case['municipality']) : '' ?></dd></div>
                    <div><dt>Publicado</dt><dd><?= e(format_date($case['published_at'] ?? null, 'd M Y')) ?></dd></div>
                </dl>
            </div>

            <div class="sidebar-card sidebar-cta">
                <h2 class="sidebar-title"><?= icon('download', 'icon icon-sm') ?> Llévalo contigo</h2>
                <p>Descarga el caso en PDF con folio, autoría, licencia y cita sugerida.</p>
                <a class="btn btn-dark btn-block" href="<?= e(url('/casos/' . $case['slug'] . '/pdf')) ?>">Descargar PDF</a>
                <button class="btn btn-ghost btn-block" type="button" data-copy="<?= e($caseUrl) ?>">Copiar enlace</button>
            </div>

            <?php if ($similar !== []): ?>
            <div class="sidebar-card">
                <h2 class="sidebar-title"><?= icon('flask', 'icon icon-sm') ?> Casos relacionados</h2>
                <div class="sidebar-related">
                    <?php foreach ($similar as $item): ?>
                        <a href="<?= e(url('/casos/' . $item['slug'])) ?>">
                            <strong><?= e(excerpt($item['title'], 72)) ?></strong>
                            <small><?= e($item['scientific_name'] ?? '') ?> · <?= e($item['state_name'] ?? 'México') ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
