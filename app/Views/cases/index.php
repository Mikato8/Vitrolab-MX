<?php
/** @var array<int, array<string, mixed>> $cases */
/** @var int $total */
/** @var array<string, mixed> $filters */
/** @var \App\Core\Paginator $paginator */
/** @var array<int, array<string, mixed>> $categoriesList */
/** @var array<int, array<string, mixed>> $speciesList */
/** @var array<int, array<string, mixed>> $states */
/** @var array<int, int> $years */
$hasFilters = $filters['q'] !== '' || $filters['category'] !== '' || $filters['species'] !== '' || $filters['state'] !== '' || $filters['year'] > 0;
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Casos</span>
        </nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Archivo de cultivo in vitro</span>
                <h1>Casos con protocolo completo</h1>
                <p>Micropropagación, organogénesis, embriogénesis, saneamiento, conservación y aclimatación contadas con explante, medio, reguladores y resultados.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= number_format($total) ?></span>
                <span><?= plural($total, 'caso encontrado', 'casos encontrados') ?></span>
                <a class="btn btn-primary btn-sm" href="<?= e(url('/casos/nuevo')) ?>"><?= icon('plus', 'icon icon-xs') ?> Compartir caso</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <form class="filter-bar filter-bar-wide" method="get" action="<?= e(url('/casos')) ?>" data-filter-form>
            <div class="filter-search">
                <?= icon('search', 'icon icon-sm') ?>
                <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Especie, medio, explante…" aria-label="Buscar casos">
            </div>
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
            <label class="filter-select">
                <span>Año</span>
                <select name="year">
                    <option value="">Todos</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?= $year ?>" <?= $filters['year'] === $year ? 'selected' : '' ?>><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="filter-select">
                <span>Orden</span>
                <select name="sort">
                    <?php foreach (['recientes' => 'Recientes', 'vistos' => 'Más vistos', 'antiguos' => 'Más antiguos', 'relevancia' => 'Relevancia'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $filters['sort'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn btn-dark btn-sm" type="submit">Filtrar</button>
            <?php if ($hasFilters): ?><a class="filter-clear" href="<?= e(url('/casos')) ?>">Limpiar</a><?php endif; ?>
        </form>

        <?php if ($cases === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('flask', 'icon') ?></span>
                <h2>No hay casos con esos filtros</h2>
                <p>Prueba otra especie o línea de trabajo. También puedes publicar el primero.</p>
                <div class="empty-actions">
                    <a class="btn btn-primary" href="<?= e(url('/casos/nuevo')) ?>">Compartir un caso</a>
                    <a class="btn btn-outline" href="<?= e(url('/casos')) ?>">Ver todos</a>
                </div>
            </div>
        <?php else: ?>
            <div class="case-grid">
                <?php foreach ($cases as $case): ?>
                    <?php \App\Core\View::partial('partials/case-card', ['case' => $case]); ?>
                <?php endforeach; ?>
            </div>
            <div class="results-foot">
                <p>Mostrando <strong><?= $paginator->from() ?>–<?= $paginator->to() ?></strong> de <strong><?= number_format($total) ?></strong> casos</p>
                <?php \App\Core\View::partial('partials/pagination', ['paginator' => $paginator]); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
