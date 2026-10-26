<?php
/** @var array<string, mixed> $species */
/** @var array<int, array<string, mixed>> $cases */
/** @var array<int, array<string, mixed>> $reports */
/** @var int $casesTotal */
/** @var int $reportsTotal */
?>
<section class="page-hero page-hero-species">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/especies')) ?>">Especies</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><em><?= e($species['scientific_name']) ?></em></span>
        </nav>
        <div class="species-hero-grid">
            <div>
                <span class="eyebrow"><?= e($species['family']) ?> · <?= e($species['group_name']) ?></span>
                <h1><em><?= e($species['scientific_name']) ?></em></h1>
                <p class="species-common"><?= e($species['common_name']) ?></p>
                <p class="species-description"><?= e($species['description']) ?></p>
                <div class="species-hero-actions">
                    <a class="btn btn-primary btn-sm" href="<?= e(url('/casos?species=' . $species['slug'])) ?>">Ver casos</a>
                    <a class="btn btn-outline btn-sm" href="<?= e(url('/reportes?species=' . $species['slug'])) ?>">Ver reportes</a>
                </div>
            </div>
            <div class="species-taxonomy">
                <span class="taxonomy-label"><?= icon('leaf', 'icon icon-sm') ?> Ficha taxonómica</span>
                <dl>
                    <div><dt>Nombre científico</dt><dd><em><?= e($species['scientific_name']) ?></em></dd></div>
                    <div><dt>Nombre común</dt><dd><?= e($species['common_name']) ?></dd></div>
                    <div><dt>Familia</dt><dd><?= e($species['family']) ?></dd></div>
                    <div><dt>Grupo</dt><dd><?= e($species['group_name']) ?></dd></div>
                    <div><dt>Documentos</dt><dd><?= (int) $casesTotal ?> casos · <?= (int) $reportsTotal ?> reportes</dd></div>
                </dl>
            </div>
        </div>
    </div>
</section>

<section class="section section-cases">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Protocolos</span>
                <h2>Casos de <em><?= e($species['scientific_name']) ?></em></h2>
            </div>
            <a class="text-link" href="<?= e(url('/casos?species=' . $species['slug'])) ?>">Ver todos <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <?php if ($cases === []): ?>
            <div class="empty-state empty-state-small">
                <p>Todavía no hay casos publicados para esta especie. <a href="<?= e(url('/casos/nuevo')) ?>">Comparte el primero</a>.</p>
            </div>
        <?php else: ?>
            <div class="case-grid">
                <?php foreach ($cases as $case): ?>
                    <?php \App\Core\View::partial('partials/case-card', ['case' => $case]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Datos</span>
                <h2>Reportes de laboratorio</h2>
            </div>
            <a class="text-link" href="<?= e(url('/reportes?species=' . $species['slug'])) ?>">Ver todos <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <?php if ($reports === []): ?>
            <div class="empty-state empty-state-small">
                <p>Aún no hay reportes para esta especie. <a href="<?= e(url('/reportes/nuevo')) ?>">Sube el primero</a>.</p>
            </div>
        <?php else: ?>
            <div class="report-grid">
                <?php foreach ($reports as $report): ?>
                    <?php \App\Core\View::partial('partials/report-card', ['report' => $report]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
