<?php
/** @var array<string, int> $stats */
/** @var array<int, array<string, mixed>> $featuredCases */
/** @var array<int, array<string, mixed>> $featuredReports */
/** @var array<int, array<string, mixed>> $latestCases */
/** @var array<int, array<string, mixed>> $categories */
/** @var array<int, array<string, mixed>> $species */
/** @var array<int, array<string, mixed>> $institutions */
/** @var array<int, array<string, mixed>> $states */
$heroCase = $featuredCases[0] ?? null;
$heroReport = $featuredReports[0] ?? null;
?>
<section class="hero hero-vitro">
    <div class="hero-pattern" aria-hidden="true"></div>
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Archivo abierto de cultivo de tejidos</span>
            <h1>La planta se explica<br><em>cuando documentas el proceso.</em></h1>
            <p class="hero-lead">Casos con protocolo completo, reportes de laboratorio y una biblioteca de fuentes para quien cultiva material vegetal in vitro en México. Explante, medio, reguladores, condiciones y resultados: todo en el mismo lugar.</p>

            <form class="hero-search" action="<?= e(url('/buscar')) ?>" method="get" role="search">
                <label class="sr-only" for="hero-q">Buscar en el portal</label>
                <div class="hero-search-field">
                    <?= icon('search', 'icon') ?>
                    <input id="hero-q" type="search" name="q" placeholder="Agave, BAP, embriogénesis, aclimatación…">
                </div>
                <label class="sr-only" for="hero-cat">Categoría</label>
                <select id="hero-cat" name="category">
                    <option value="">Todas las líneas</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category['code']) ?>"><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">Explorar <?= icon('arrow-right', 'icon icon-sm') ?></button>
            </form>

            <dl class="hero-stats">
                <div><dt><?= number_format($stats['cases'] ?? 0) ?></dt><dd>casos publicados</dd></div>
                <div><dt><?= number_format($stats['reports'] ?? 0) ?></dt><dd>reportes de laboratorio</dd></div>
                <div><dt><?= number_format($stats['species'] ?? 0) ?></dt><dd>especies documentadas</dd></div>
                <div><dt><?= number_format($stats['institutions'] ?? 0) ?></dt><dd>instituciones</dd></div>
            </dl>
        </div>

        <div class="hero-visual">
            <?php if ($heroCase): ?>
            <a class="specimen-card" href="<?= e(content_route('caso', $heroCase['slug'])) ?>">
                <div class="specimen-media">
                    <?php if (!empty($heroCase['cover_image'])): ?>
                        <img src="<?= e(url('/uploads/casos/' . $heroCase['cover_image'])) ?>" alt="">
                    <?php else: ?>
                        <img src="<?= e(url('/assets/img/hero-lab.jpg')) ?>" alt="">
                    <?php endif; ?>
                    <span class="specimen-tag"><?= icon('flask', 'icon icon-xs') ?> Caso destacado</span>
                </div>
                <div class="specimen-body">
                    <span class="specimen-kicker"><?= e($heroCase['category_name'] ?? 'Cultivo in vitro') ?> · <?= e($heroCase['species_text'] ?? '') ?></span>
                    <strong><?= e(excerpt($heroCase['title'], 82)) ?></strong>
                    <div class="specimen-metrics">
                        <span><b><?= e(ratio($heroCase['multiplication_rate'])) ?></b> brotes/explante</span>
                        <span><b><?= e(pct($heroCase['contamination_pct'])) ?></b> contaminación</span>
                        <span><b><?= e(pct($heroCase['survival_pct'])) ?></b> supervivencia</span>
                    </div>
                    <span class="specimen-link">Leer el caso completo <?= icon('arrow-up-right', 'icon icon-xs') ?></span>
                </div>
            </a>
            <?php endif; ?>

            <div class="hero-note-card">
                <span class="hero-note-icon"><?= icon('shield', 'icon icon-sm') ?></span>
                <div>
                    <strong>Verificación institucional, no cédula</strong>
                    <p>Correo institucional o constancia revisada por el comité. Cada caso declara su licencia.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-tight section-pillars">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Tres formas de aportar</span>
                <h2>Del cuaderno de laboratorio al archivo común</h2>
            </div>
            <a class="text-link" href="<?= e(url('/guias')) ?>">Ver guías de documentación <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <div class="pillar-grid">
            <article class="pillar-card">
                <span class="pillar-icon"><?= icon('flask', 'icon') ?></span>
                <span class="badge badge-soft"><?= number_format($stats['cases'] ?? 0) ?> publicados</span>
                <h3>Casos de cultivo</h3>
                <p>Especie, explante, desinfección, medio, reguladores, condiciones y resultados. Un protocolo que otra persona puede discutir y repetir.</p>
                <a class="text-link" href="<?= e(url('/casos')) ?>">Explorar casos <?= icon('arrow-right', 'icon icon-sm') ?></a>
            </article>
            <article class="pillar-card">
                <span class="pillar-icon"><?= icon('file-text', 'icon') ?></span>
                <span class="badge badge-soft"><?= number_format($stats['reports'] ?? 0) ?> publicados</span>
                <h3>Reportes de laboratorio</h3>
                <p>Ensayos, validaciones y seguimientos con diseño, tratamientos, tamaño de muestra, variables y conclusiones honestas.</p>
                <a class="text-link" href="<?= e(url('/reportes')) ?>">Ver reportes <?= icon('arrow-right', 'icon icon-sm') ?></a>
            </article>
            <article class="pillar-card">
                <span class="pillar-icon"><?= icon('book-open', 'icon') ?></span>
                <span class="badge badge-soft"><?= number_format($stats['species'] ?? 0) ?> especies</span>
                <h3>Especies y biblioteca</h3>
                <p>Fichas por especie y una biblioteca con repositorios de código, datasets, manuales y literatura científica verificable.</p>
                <a class="text-link" href="<?= e(url('/especies')) ?>">Ver especies <?= icon('arrow-right', 'icon icon-sm') ?></a>
            </article>
        </div>
    </div>
</section>

<section class="section section-cases">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Selección editorial</span>
                <h2>Casos que vale la pena leer</h2>
                <p class="section-sub">Protocolos con decisiones concretas: qué explante funcionó, qué concentración se descartó y por qué.</p>
            </div>
            <a class="text-link" href="<?= e(url('/casos')) ?>">Ver todos los casos <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <?php if ($featuredCases === []): ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('flask', 'icon') ?></span>
                <h2>El archivo está comenzando</h2>
                <p>Publica el primer caso y ayuda a construir esta biblioteca.</p>
                <a class="btn btn-primary" href="<?= e(url('/casos/nuevo')) ?>">Compartir un caso</a>
            </div>
        <?php else: ?>
            <div class="case-grid">
                <?php foreach ($featuredCases as $case): ?>
                    <?php \App\Core\View::partial('partials/case-card', ['case' => $case]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-lines">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Líneas de trabajo</span>
                <h2>Ocho rutas por donde entra el cultivo in vitro</h2>
            </div>
            <a class="text-link" href="<?= e(url('/biblioteca')) ?>">Biblioteca de métodos <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
                <a class="category-card" href="<?= e(url('/casos?category=' . $category['code'])) ?>">
                    <span class="category-emoji" aria-hidden="true"><?= e($category['emoji']) ?></span>
                    <div>
                        <strong><?= e($category['name']) ?></strong>
                        <p><?= e(excerpt($category['description'], 96)) ?></p>
                    </div>
                    <span class="category-count"><?= (int) $category['cases_count'] + (int) $category['reports_count'] ?> <?= icon('arrow-up-right', 'icon icon-xs') ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-reports-band">
    <div class="container report-band">
        <div class="report-band-copy">
            <span class="eyebrow eyebrow-light"><span class="eyebrow-dot"></span> Reportes con método</span>
            <h2>Los datos también se leen.</h2>
            <p>Un reporte no es un resumen bonito: declara hipótesis, diseño, tratamientos, tamaño de muestra, variables y limitaciones. Aquí puedes subir la figura y el CSV original.</p>
            <ul class="check-list check-list-light">
                <li><?= icon('check', 'icon icon-sm') ?> Tabla de tratamientos con respuesta y unidad</li>
                <li><?= icon('check', 'icon icon-sm') ?> Figuras y archivos CSV, XLSX, PDF o DOCX</li>
                <li><?= icon('check', 'icon icon-sm') ?> Vinculación con el caso que originó el ensayo</li>
                <li><?= icon('check', 'icon icon-sm') ?> Descarga del reporte completo en PDF</li>
            </ul>
            <a class="btn btn-sand" href="<?= e(url('/reportes/nuevo')) ?>">Subir un reporte <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <div class="report-band-preview">
            <div class="report-preview-head">
                <span><?= icon('file-text', 'icon icon-sm') ?> Reporte destacado</span>
                <span class="badge badge-live">Datos abiertos</span>
            </div>
            <?php if ($heroReport): ?>
                <h3><a href="<?= e(content_route('reporte', $heroReport['slug'])) ?>"><?= e($heroReport['title']) ?></a></h3>
                <p><?= e(excerpt($heroReport['summary'], 104)) ?></p>
                <div class="preview-meta">
                    <span><b><?= (int) $heroReport['treatments_count'] ?></b> tratamientos</span>
                    <span><b><?= (int) $heroReport['replicates'] ?></b> unidades</span>
                    <span><b><?= (int) $heroReport['files_count'] ?></b> anexos</span>
                </div>
                <div class="preview-bars" aria-hidden="true">
                    <span style="width: 42%"></span><span style="width: 68%"></span><span style="width: 92%"></span><span style="width: 74%"></span>
                </div>
                <a class="text-link text-link-light" href="<?= e(content_route('reporte', $heroReport['slug'])) ?>">Leer el reporte <?= icon('arrow-right', 'icon icon-sm') ?></a>
            <?php else: ?>
                <p>Aún no hay reportes publicados. Sé la primera persona en compartir uno.</p>
                <a class="btn btn-sand" href="<?= e(url('/reportes/nuevo')) ?>">Crear reporte</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section section-species">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Fichas de especie</span>
                <h2>Un mismo método, muchos materiales</h2>
                <p class="section-sub">Cada especie responde distinto al explante, al medio y a la concentración de reguladores.</p>
            </div>
            <a class="text-link" href="<?= e(url('/especies')) ?>">Ver todas las especies <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <div class="species-strip">
            <?php foreach ($species as $item): ?>
                <a class="species-chip" href="<?= e(url('/especies/' . $item['slug'])) ?>">
                    <span class="species-chip-family"><?= e($item['family']) ?></span>
                    <strong><em><?= e($item['scientific_name']) ?></em></strong>
                    <span><?= e($item['common_name']) ?></span>
                    <span class="species-chip-count"><?= (int) $item['cases_count'] + (int) $item['reports_count'] ?> documentos</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-how">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Cómo funciona</span>
                <h2>Publicar es un ejercicio de método, no un trámite</h2>
            </div>
        </div>
        <ol class="steps-grid">
            <li>
                <span class="step-number">01</span>
                <h3>Crea tu cuenta</h3>
                <p>Regístrate con correo institucional. Si usas uno personal, sube una constancia o credencial y un editor revisará tu cuenta.</p>
            </li>
            <li>
                <span class="step-number">02</span>
                <h3>Documenta con trazabilidad</h3>
                <p>Describe el material de partida, el protocolo, los controles y las condiciones. Si algo falló, también se documenta.</p>
            </li>
            <li>
                <span class="step-number">03</span>
                <h3>Recibe revisión</h3>
                <p>El comité revisa coherencia, evidencia y ética. Puede publicar, pedir cambios o rechazar con retroalimentación.</p>
            </li>
            <li>
                <span class="step-number">04</span>
                <h3>Conversa y comparte</h3>
                <p>Recibe comentarios, guarda favoritos y descarga el caso o reporte en PDF con tu crédito y licencia.</p>
            </li>
        </ol>
    </div>
</section>

<section class="section section-institutions-home">
    <div class="container institutions-band">
        <div>
            <span class="eyebrow">Comunidad</span>
            <h2>Laboratorios, aulas y centros de investigación</h2>
            <p class="section-sub">El archivo se construye entre instituciones. Consulta quién publica, desde dónde y sobre qué especies.</p>
            <a class="btn btn-outline" href="<?= e(url('/instituciones')) ?>">Ver directorio <?= icon('arrow-right', 'icon icon-sm') ?></a>
        </div>
        <div class="institution-list">
            <?php foreach ($institutions as $institution): ?>
                <a class="institution-row" href="<?= e(url('/instituciones/' . $institution['slug'])) ?>">
                    <span class="avatar avatar-sm"><?= e($institution['acronym'] ?: initials($institution['name'])) ?></span>
                    <span><strong><?= e($institution['name']) ?></strong><small><?= e($institution['state_name'] ?? 'México') ?> · <?= (int) $institution['total'] ?> miembros</small></span>
                    <?= icon('arrow-up-right', 'icon icon-xs') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-cta">
    <div class="container cta-band">
        <div>
            <span class="eyebrow eyebrow-light">El archivo crece contigo</span>
            <h2>¿Tienes un protocolo que funciona?</h2>
            <p>Publícalo con sus datos, sus límites y sus preguntas abiertas. Alguien más está intentando lo mismo.</p>
        </div>
        <div class="cta-actions">
            <a class="btn btn-sand" href="<?= e(url('/casos/nuevo')) ?>">Compartir un caso</a>
            <a class="btn btn-ghost btn-on-dark" href="<?= e(url('/reportes/nuevo')) ?>">Subir un reporte</a>
        </div>
    </div>
</section>
