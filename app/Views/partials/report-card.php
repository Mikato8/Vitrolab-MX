<?php
/** @var array<string, mixed> $report */
$href = content_route('reporte', (string) $report['slug']);
?>
<article class="report-card">
    <div class="report-card-top">
        <span class="report-icon"><?= icon('file-text', 'icon') ?></span>
        <div class="report-card-head">
            <span class="badge badge-soft"><?= e(report_type_label($report['report_type'] ?? null)) ?></span>
            <?php if (!empty($report['is_demo'])): ?><span class="badge badge-demo">Demostrativo</span><?php endif; ?>
            <span class="report-period"><?= e($report['species_text'] ?? '') ?></span>
        </div>
    </div>
    <h3><a href="<?= e($href) ?>"><?= e($report['title']) ?></a></h3>
    <p><?= e(excerpt((string) ($report['summary'] ?? ''), 126)) ?></p>
    <div class="report-metrics">
        <div>
            <span>Tratamientos</span>
            <strong><?= (int) ($report['treatments_count'] ?? 0) ?></strong>
        </div>
        <div>
            <span>Unidad experimental</span>
            <strong><?= $report['replicates'] ? 'n = ' . (int) $report['replicates'] : '—' ?></strong>
        </div>
        <div>
            <span>Anexos</span>
            <strong><?= (int) ($report['files_count'] ?? 0) ?></strong>
        </div>
    </div>
    <div class="report-card-footer">
        <span class="case-author">
            <span class="avatar avatar-xs"><?= e(initials((string) ($report['author_name'] ?? 'AN'))) ?></span>
            <span><?= e($report['author_name'] ?? 'Autor') ?><?php if (!empty($report['institution_acronym'])): ?> · <?= e($report['institution_acronym']) ?><?php endif; ?></span>
        </span>
        <span class="case-card-data">
            <?= icon('calendar', 'icon icon-xs') ?> <?= e(format_date($report['published_at'] ?? null, 'd/m/Y')) ?>
            <span class="case-stat"><?= icon('eye', 'icon icon-xs') ?> <?= (int) ($report['views'] ?? 0) ?></span>
        </span>
    </div>
</article>
