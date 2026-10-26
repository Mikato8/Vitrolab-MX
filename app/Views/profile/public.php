<?php
/** @var array<string, mixed> $user */
/** @var array<int, array<string, mixed>> $cases */
/** @var array<int, array<string, mixed>> $reports */
?>
<section class="page-hero page-hero-profile">
    <div class="container">
        <nav class="breadcrumb" aria-label="Migas de pan">
            <a href="<?= e(url('/')) ?>">Inicio</a> <?= icon('chevron-right', 'icon icon-xs') ?>
            <a href="<?= e(url('/instituciones')) ?>">Instituciones</a> <?= icon('chevron-right', 'icon icon-xs') ?> <span><?= e($user['name']) ?></span>
        </nav>
        <div class="profile-hero">
            <?= avatar_tag((string) $user['name'], $user['avatar_url'] ?? null, 'avatar-xl') ?>
            <div class="profile-hero-copy">
                <span class="eyebrow"><?= e(member_role_label($user['role'])) ?><?php if ($user['status'] === 'verificado'): ?> · <span class="verified"><?= icon('check-circle', 'icon icon-xs') ?> Verificado</span><?php endif; ?></span>
                <h1><?= e($user['name']) ?></h1>
                <p><?= e($user['institution_name'] ?? 'Institución no indicada') ?><?php if (!empty($user['career'])): ?> · <?= e($user['career']) ?><?php endif; ?><?php if (!empty($user['state_name'])): ?> · <?= e($user['state_name']) ?><?php endif; ?></p>
                <div class="profile-badges">
                    <span class="badge badge-soft"><?= (int) $user['published_cases'] ?> casos</span>
                    <span class="badge badge-soft"><?= (int) $user['published_reports'] ?> reportes</span>
                    <?php if (!empty($user['orcid'])): ?><a class="badge badge-neutral" href="https://orcid.org/<?= e($user['orcid']) ?>" target="_blank" rel="noopener">ORCID <?= e($user['orcid']) ?></a><?php endif; ?>
                </div>
            </div>
        </div>
        <?php if (!empty($user['bio'])): ?><p class="profile-bio"><?= nl2br(e($user['bio'])) ?></p><?php endif; ?>
    </div>
</section>

<section class="section section-cases">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">Aportaciones</span><h2>Casos publicados</h2></div></div>
        <?php if ($cases === []): ?>
            <p class="muted">Esta persona aún no tiene casos publicados.</p>
        <?php else: ?>
            <div class="case-grid"><?php foreach ($cases as $case): ?><?php \App\Core\View::partial('partials/case-card', ['case' => $case]); ?><?php endforeach; ?></div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-tight">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">Datos</span><h2>Reportes publicados</h2></div></div>
        <?php if ($reports === []): ?>
            <p class="muted">Esta persona aún no tiene reportes publicados.</p>
        <?php else: ?>
            <div class="report-grid"><?php foreach ($reports as $report): ?><?php \App\Core\View::partial('partials/report-card', ['report' => $report]); ?><?php endforeach; ?></div>
        <?php endif; ?>
    </div>
</section>
