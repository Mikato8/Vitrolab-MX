<?php
/** @var array<int, array<string, mixed>> $species */
/** @var array<int, array<string, mixed>> $grouped */
/** @var int $total */
/** @var string $q */
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Especies</span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Directorio botánico</span>
                <h1>Especies documentadas</h1>
                <p>Fichas de las especies que aparecen en los casos y reportes. La familia, el nombre común y los documentos disponibles para cada una.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= number_format($total) ?></span>
                <span><?= plural($total, 'especie registrada', 'especies registradas') ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="filter-bar" method="get" action="<?= e(url('/especies')) ?>">
            <div class="filter-search">
                <?= icon('search', 'icon icon-sm') ?>
                <input type="search" name="q" value="<?= e($q) ?>" placeholder="Nombre científico, común o familia…" aria-label="Buscar especies">
            </div>
            <button class="btn btn-dark btn-sm" type="submit">Buscar</button>
            <?php if ($q !== ''): ?><a class="filter-clear" href="<?= e(url('/especies')) ?>">Limpiar</a><?php endif; ?>
        </form>

        <?php if ($species === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('leaf', 'icon') ?></span>
                <h2>No encontramos esa especie</h2>
                <p>Prueba con el nombre científico o revisa el directorio completo.</p>
                <a class="btn btn-primary" href="<?= e(url('/especies')) ?>">Ver todas</a>
            </div>
        <?php else: ?>
            <?php $groupIndex = 0; foreach ($grouped as $group => $items): $groupIndex++; ?>
                <div class="species-group">
                    <div class="species-group-head"><span></span><h2><span class="section-index"><?= str_pad((string) $groupIndex, 2, '0', STR_PAD_LEFT) ?></span> <?= e($group) ?></h2><span></span></div>
                    <div class="species-grid">
                        <?php foreach ($items as $item): ?>
                            <a class="species-card" href="<?= e(url('/especies/' . $item['slug'])) ?>">
                                <span class="species-card-family"><?= e($item['family']) ?></span>
                                <strong><em><?= e($item['scientific_name']) ?></em></strong>
                                <span class="species-card-common"><?= e($item['common_name']) ?></span>
                                <p><?= e(excerpt($item['description'], 104)) ?></p>
                                <span class="species-card-foot">
                                    <?= (int) $item['cases_count'] ?> <?= plural((int) $item['cases_count'], 'caso', 'casos') ?> ·
                                    <?= (int) $item['reports_count'] ?> <?= plural((int) $item['reports_count'], 'reporte', 'reportes') ?>
                                    <?= icon('arrow-up-right', 'icon icon-xs') ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
