<?php
/** @var array<int, array<string, mixed>> $institutions */
/** @var array<int, array<string, mixed>> $grouped */
/** @var int $total */
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Instituciones</span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Directorio</span>
                <h1>Instituciones y laboratorios</h1>
                <p>Universidades, centros de investigación, jardines botánicos y laboratorios desde donde se documenta el cultivo in vitro. La verificación por dominio institucional se apoya en este directorio.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= number_format($total) ?></span>
                <span><?= plural($total, 'institución registrada', 'instituciones registradas') ?></span>
                <a class="btn btn-primary btn-sm" href="<?= e(url('/registro')) ?>">Sumar mi institución</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <?php foreach ($grouped as $state => $items): ?>
            <div class="institution-group">
                <div class="species-group-head"><span></span><h2><?= e($state) ?></h2><span></span></div>
                <div class="institution-grid">
                    <?php foreach ($items as $institution): ?>
                        <a class="institution-card" href="<?= e(url('/instituciones/' . $institution['slug'])) ?>">
                            <span class="avatar avatar-md"><?= e($institution['acronym'] ?: initials($institution['name'])) ?></span>
                            <div>
                                <strong><?= e($institution['name']) ?></strong>
                                <span class="institution-type"><?= e(institution_type_label($institution['type'])) ?></span>
                                <p><?= e(excerpt($institution['description'], 110)) ?></p>
                                <span class="institution-meta"><?= (int) $institution['members_count'] ?> <?= plural((int) $institution['members_count'], 'miembro', 'miembros') ?> · <?= (int) $institution['cases_count'] ?> casos · <?= (int) $institution['reports_count'] ?> reportes</span>
                            </div>
                            <?= icon('arrow-up-right', 'icon icon-xs') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
