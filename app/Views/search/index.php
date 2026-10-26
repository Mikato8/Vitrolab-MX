<?php
/** @var string $q */
/** @var string $category */
/** @var array<int, array{type: string, item: array<string, mixed>}> $results */
/** @var int $total */
/** @var array<int, array<string, mixed>> $categories */
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Buscar</span>
        </nav>
        <div>
            <span class="eyebrow">Buscador</span>
            <h1><?= $q !== '' ? 'Resultados para «' . e($q) . '»' : 'Busca en el archivo' ?></h1>
            <p>Casos, reportes, especies, medios y variables. Prueba con el nombre científico, un regulador o una técnica.</p>
        </div>
        <form class="hero-search hero-search-light" action="<?= e(url('/buscar')) ?>" method="get" role="search">
            <div class="hero-search-field">
                <?= icon('search', 'icon') ?>
                <input type="search" name="q" value="<?= e($q) ?>" placeholder="Ej. BAP, Agave, embriogénesis, aclimatación…" aria-label="Buscar">
            </div>
            <select name="category" aria-label="Categoría">
                <option value="">Todas las líneas</option>
                <?php foreach ($categories as $item): ?>
                    <option value="<?= e($item['code']) ?>" <?= $category === $item['code'] ? 'selected' : '' ?>><?= e($item['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Buscar</button>
        </form>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <?php if (mb_strlen($q) < 2): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('search', 'icon') ?></span>
                <h2>Escribe al menos dos caracteres</h2>
                <p>El buscador consulta títulos, resúmenes, especies, medios y variables de casos y reportes.</p>
                <div class="empty-actions">
                    <a class="btn btn-primary" href="<?= e(url('/casos')) ?>">Ver casos</a>
                    <a class="btn btn-outline" href="<?= e(url('/reportes')) ?>">Ver reportes</a>
                </div>
            </div>
        <?php elseif ($results === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('search', 'icon') ?></span>
                <h2>Sin resultados</h2>
                <p>No encontramos coincidencias para «<?= e($q) ?>». Prueba con un término más general o revisa la biblioteca.</p>
                <div class="empty-actions">
                    <a class="btn btn-primary" href="<?= e(url('/biblioteca')) ?>">Ir a la biblioteca</a>
                    <a class="btn btn-outline" href="<?= e(url('/especies')) ?>">Ver especies</a>
                </div>
            </div>
        <?php else: ?>
            <div class="results-head">
                <p><strong><?= number_format($total) ?></strong> <?= plural($total, 'resultado', 'resultados') ?> en casos y reportes</p>
            </div>
            <div class="search-results">
                <?php foreach ($results as $result): ?>
                    <?php $item = $result['item']; ?>
                    <?php if ($result['type'] === 'caso'): ?>
                        <a class="search-result" href="<?= e(url('/casos/' . $item['slug'])) ?>">
                            <span class="search-result-type"><?= icon('flask', 'icon icon-sm') ?> Caso</span>
                            <span class="search-result-body">
                                <strong><?= e($item['title']) ?></strong>
                                <small><?= e($item['category_name'] ?? '') ?> · <em><?= e($item['species_text'] ?? '') ?></em> · <?= e($item['state_name'] ?? 'México') ?></small>
                                <p><?= e(excerpt($item['summary'] ?? '', 150)) ?></p>
                            </span>
                            <?= icon('arrow-right', 'icon icon-xs') ?>
                        </a>
                    <?php else: ?>
                        <a class="search-result" href="<?= e(url('/reportes/' . $item['slug'])) ?>">
                            <span class="search-result-type search-result-type-report"><?= icon('file-text', 'icon icon-sm') ?> Reporte</span>
                            <span class="search-result-body">
                                <strong><?= e($item['title']) ?></strong>
                                <small><?= e(report_type_label($item['report_type'] ?? null)) ?> · <em><?= e($item['species_text'] ?? '') ?></em> · <?= e($item['state_name'] ?? 'México') ?></small>
                                <p><?= e(excerpt($item['summary'] ?? '', 150)) ?></p>
                            </span>
                            <?= icon('arrow-right', 'icon icon-xs') ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
