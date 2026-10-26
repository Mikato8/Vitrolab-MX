<?php
use App\Core\Str;

/** @var array<int, array{term: string, definition: string}> $terms */
?>
<section class="page-hero page-hero-compact">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan"><a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span>Glosario</span></nav>
        <div class="page-hero-grid">
            <div>
                <span class="eyebrow">Vocabulario de laboratorio</span>
                <h1>Glosario de cultivo in vitro</h1>
                <p>Términos que aparecen en los casos y reportes. Si un protocolo usa una palabra distinto, este glosario ayuda a comparar.</p>
            </div>
            <div class="page-hero-aside">
                <span class="big-number"><?= count($terms) ?></span>
                <span>términos</span>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <dl class="glossary-grid">
            <?php foreach ($terms as $term): ?>
                <div class="glossary-item" id="<?= e(Str::slug($term['term'], 60)) ?>">
                    <dt><?= e($term['term']) ?></dt>
                    <dd><?= e($term['definition']) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
        <div class="sidebar-card glossary-note">
            <h2 class="sidebar-title"><?= icon('info', 'icon icon-sm') ?> Sobre las definiciones</h2>
            <p>Las definiciones son operativas y buscan consistencia editorial, no sustituir un manual de fisiología vegetal. Si detectas una imprecisión, escribe a <a href="mailto:hola@vitrolab.mx">hola@vitrolab.mx</a>.</p>
        </div>
    </div>
</section>
