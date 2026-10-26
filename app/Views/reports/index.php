<?php
/** @var array<int, array<string, mixed>> $reports */
/** @var int $total */
/** @var array<string, mixed> $filters */
/** @var \App\Core\Paginator $paginator */
/** @var array<int, array<string, mixed>> $categoriesList */
/** @var array<int, array<string, mixed>> $speciesList */
/** @var array<int, array<string, mixed>> $states */
/** @var array<int, int> $years */
$hasFilters = $filters['q'] !== '' || $filters['type'] !== '' || $filters['category'] !== '' || $filters['species'] !== '' || $filters['state'] !== '' || $filters['year'] > 0;
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Reportes</span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Cuaderno de laboratorio</span>
                <h1>Reportes de laboratorio</h1>
                <p>Ensayos, validaciones, protocolos y seguimientos con diseño experimental, tratamientos, variables y conclusiones. También se publican los resultados que no salieron como se esperaba.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= number_format($total) ?></span>
                <span><?= plural($total, 'reporte publicado', 'reportes publicados') ?></span>
                <a class="btn btn-primary btn-sm" href="<?= e(url('/reportes/nuevo')) ?>"><?= icon('plus', 'icon icon-xs') ?> Subir reporte</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="filter-bar filter-bar-wide" method="get" action="<?= e(url('/reportes')) ?>">
            <div class="filter-search">
                <?= icon('search', 'icon icon-sm') ?>
                <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Especie, tratamiento, variable…" aria-label="Buscar reportes">
            </div>
            <label class="filter-select">
                <span>Tipo</span>
                <select name="type">
                    <option value="">Todos</option>
                    <?php foreach (['ensayo' => 'Ensayo experimental', 'protocolo' => 'Protocolo', 'diagnostico' => 'Diagnóstico', 'seguimiento' => 'Seguimiento', 'validacion' => 'Validación', 'datos' => 'Conjunto de datos'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $filters['type'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="filter-select">
                <span>Línea</span>
                <select name="category">
                    <option value="">Todas</option>
                    <?php foreach ($categoriesList as $category): ?>
                        <option value="<?= e($category['code']) ?>" <?= $filters['category'] === $category['code'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="filter-select">
                <span>Especie</span>
                <select name="species">
                    <option value="">Todas</option>
                    <?php foreach ($speciesList as $species): ?>
                        <option value="<?= e($species['slug']) ?>" <?= $filters['species'] === $species['slug'] ? 'selected' : '' ?>><?= e($species['scientific_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="filter-select">
                <span>Estado</span>
                <select name="state">
                    <option value="">Todos</option>
                    <?php foreach ($states as $state): ?>
                        <option value="<?= e($state['code']) ?>" <?= $filters['state'] === $state['code'] ? 'selected' : '' ?>><?= e($state['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn btn-dark btn-sm" type="submit">Filtrar</button>
            <?php if ($hasFilters): ?><a class="filter-clear" href="<?= e(url('/reportes')) ?>">Limpiar</a><?php endif; ?>
        </form>

        <?php if ($reports === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('file-text', 'icon') ?></span>
                <h2>No hay reportes con esos filtros</h2>
                <p>Prueba otra línea o publica el primer reporte de este tema.</p>
                <div class="empty-actions">
                    <a class="btn btn-primary" href="<?= e(url('/reportes/nuevo')) ?>">Subir un reporte</a>
                    <a class="btn btn-outline" href="<?= e(url('/reportes')) ?>">Ver todos</a>
                </div>
            </div>
        <?php else: ?>
            <div class="report-grid">
                <?php foreach ($reports as $report): ?>
                    <?php \App\Core\View::partial('partials/report-card', ['report' => $report]); ?>
                <?php endforeach; ?>
            </div>
            <div class="results-foot">
                <p>Mostrando <strong><?= $paginator->from() ?>–<?= $paginator->to() ?></strong> de <strong><?= number_format($total) ?></strong> reportes</p>
                <?php \App\Core\View::partial('partials/pagination', ['paginator' => $paginator]); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
