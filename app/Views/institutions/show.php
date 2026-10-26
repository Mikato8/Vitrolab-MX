<?php
/** @var array<string, mixed> $institution */
/** @var array<int, array<string, mixed>> $members */
/** @var array<int, array<string, mixed>> $cases */
/** @var array<int, array<string, mixed>> $reports */
/** @var int $casesTotal */
/** @var int $reportsTotal */
?>
<section class="page-hero page-hero-institution">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/instituciones')) ?>">Instituciones</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <span><?= e($institution['acronym'] ?: $institution['name']) ?></span>
        </nav>
        <div class="institution-hero-grid">
            <div class="institution-hero-copy">
                <span class="avatar avatar-xl"><?= e($institution['acronym'] ?: initials($institution['name'])) ?></span>
                <div>
                    <span class="eyebrow"><?= e(institution_type_label($institution['type'])) ?> · <?= e($institution['state_name'] ?? 'México') ?></span>
                    <h1><?= e($institution['name']) ?></h1>
                    <p><?= e($institution['description']) ?></p>
                    <?php if (!empty($institution['website'])): ?>
                        <a class="text-link" href="<?= e($institution['website']) ?>" target="_blank" rel="noopener">Sitio institucional <?= icon('external-link', 'icon icon-xs') ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <dl class="institution-stats">
                <div><dt><?= count($members) ?></dt><dd>miembros verificados</dd></div>
                <div><dt><?= (int) $casesTotal ?></dt><dd>casos publicados</dd></div>
                <div><dt><?= (int) $reportsTotal ?></dt><dd>reportes publicados</dd></div>
            </dl>
        </div>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Comunidad</span>
                <h2>Miembros que publican desde aquí</h2>
            </div>
        </div>
        <?php if ($members === []): ?>
            <p class="muted">Todavía no hay miembros verificados de esta institución. Si estudias o trabajas aquí, <a href="<?= e(url('/registro')) ?>">crea tu cuenta</a> con correo institucional.</p>
        <?php else: ?>
            <div class="member-grid">
                <?php foreach ($members as $member): ?>
                    <a class="member-card" href="<?= e(url('/miembro/' . $member['id'])) ?>">
                        <?= avatar_tag((string) $member['name'], $member['avatar_url'] ?? null, 'avatar-md') ?>
                        <span><strong><?= e($member['name']) ?></strong><small><?= e(member_role_label($member['role'])) ?> · <?= e($member['career'] ?: 'Sin formación indicada') ?></small></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-cases">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Protocolos</span><h2>Casos desde esta institución</h2></div>
            <a class="text-link" href="<?= e(url('/casos')) ?>">Ver todos <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <?php if ($cases === []): ?>
            <div class="empty-state empty-state-small"><p>Aún no hay casos publicados con esta institución.</p></div>
        <?php else: ?>
            <div class="case-grid">
                <?php foreach ($cases as $case): ?>
                    <?php \App\Core\View::partial('partials/case-card', ['case' => $case]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
