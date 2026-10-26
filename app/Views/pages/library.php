<?php
/** @var array<int, array<string, mixed>> $resources */
?>
<section class="library-hero">
    <div class="container">
        <nav class="breadcrumb breadcrumb-light" aria-label="Migas de pan"><a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Biblioteca</span></nav>
        <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Biblioteca y fuentes</span>
        <h1>Lo que leemos antes de escribir.</h1>
        <p>Repositorios de código, datasets, manuales y literatura científica sobre cultivo in vitro de plantas. Los enlaces se revisan al publicar; si uno deja de funcionar, escríbenos.</p>
        <span class="library-note"><?= icon('info', 'icon icon-sm') ?> Esta biblioteca es una curaduría editorial, no un respaldo automático de cada fuente.</span>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php $groupIndex = 0; foreach ($resources as $group): $groupIndex++; ?>
            <div class="resource-group">
                <h2><span class="section-index"><?= str_pad((string) $groupIndex, 2, '0', STR_PAD_LEFT) ?></span> <?= e($group['group']) ?></h2>
                <div class="resource-grid">
                    <?php foreach ($group['items'] as $item): ?>
                        <a class="resource-card" href="<?= e($item['url']) ?>" target="_blank" rel="noopener">
                            <span class="resource-meta"><span><?= e($item['tag']) ?></span><span><?= e($item['lang']) ?></span></span>
                            <strong><?= e($item['title']) ?></strong>
                            <span class="muted"><?= e($item['author']) ?></span>
                            <p><?= e($item['description']) ?></p>
                            <span class="resource-link"><?= icon('external-link', 'icon icon-xs') ?> <?= e($item['url']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="sidebar-card">
            <h2 class="sidebar-title"><?= icon('book-open', 'icon icon-sm') ?> ¿Falta una fuente?</h2>
            <p>Si conoces un repositorio, dataset o protocolo que debería estar aquí, envía el enlace y una frase sobre por qué es útil. Priorizamos fuentes abiertas, verificables y con contexto.</p>
            <a class="btn btn-outline btn-sm" href="mailto:hola@vitrolab.mx?subject=Sugerencia%20para%20la%20biblioteca">Sugerir una fuente</a>
        </div>
    </div>
</section>
