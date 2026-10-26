<?php
/** @var array<string, mixed>|null $currentUser */
/** @var array<int, array{type: string, message: string}> $flashMessages */
$headerQuery = is_string($_GET['q'] ?? null) ? (string) $_GET['q'] : '';
?><!doctype html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'VitroLab MX') ?></title>
    <meta name="description" content="<?= e($metaDescription ?? '') ?>">
    <link rel="canonical" href="<?= e($canonical ?? url(current_path())) ?>">
    <meta name="robots" content="<?= e($robots ?? 'index,follow') ?>">
    <meta name="theme-color" content="#0B3C3A">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="VitroLab MX">
    <meta property="og:locale" content="es_MX">
    <meta property="og:title" content="<?= e($title ?? 'VitroLab MX') ?>">
    <meta property="og:description" content="<?= e($metaDescription ?? '') ?>">
    <meta property="og:url" content="<?= e($canonical ?? url(current_path())) ?>">
    <meta property="og:image" content="<?= e(url('/assets/img/og-cover.png')) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="<?= e(url('/assets/img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<a class="skip-link" href="#contenido">Saltar al contenido</a>

<header class="site-header" data-header>
    <div class="container header-inner">
        <a class="brand" href="<?= e(url('/')) ?>" aria-label="VitroLab MX, inicio">
            <img class="brand-mark" src="<?= e(url('/assets/img/logo.svg')) ?>" alt="" width="44" height="44">
            <span class="brand-text">
                <span class="brand-name">Vitro<span class="brand-accent">Lab</span></span>
                <span class="brand-sub">Cultivo in vitro · MX</span>
            </span>
        </a>

        <nav class="main-nav" id="main-nav" aria-label="Navegación principal">
            <a class="<?= e(is_active('/casos')) ?>" href="<?= e(url('/casos')) ?>">Casos</a>
            <a class="<?= e(is_active('/reportes')) ?>" href="<?= e(url('/reportes')) ?>">Reportes</a>
            <a class="<?= e(is_active('/especies')) ?>" href="<?= e(url('/especies')) ?>">Especies</a>
            <a class="<?= e(is_active('/instituciones')) ?>" href="<?= e(url('/instituciones')) ?>">Instituciones</a>
            <a class="<?= e(is_active('/biblioteca')) ?>" href="<?= e(url('/biblioteca')) ?>">Biblioteca</a>
            <a class="<?= e(is_active('/guias')) ?>" href="<?= e(url('/guias')) ?>">Guías</a>
            <div class="mobile-account">
                <?php if ($currentUser): ?>
                    <a class="btn btn-ghost btn-block" href="<?= e(url('/mi-panel')) ?>">Mi panel</a>
                    <a class="btn btn-ghost btn-block" href="<?= e(url('/perfil')) ?>">Mi perfil</a>
                    <?php if (can('moderar')): ?>
                        <a class="btn btn-dark btn-block" href="<?= e(url('/admin')) ?>">Panel editorial</a>
                    <?php endif; ?>
                    <form method="post" action="<?= e(url('/salir')) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-ghost btn-block" type="submit">Cerrar sesión</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-ghost btn-block" href="<?= e(url('/acceder')) ?>">Acceder</a>
                    <a class="btn btn-primary btn-block" href="<?= e(url('/registro')) ?>">Crear cuenta</a>
                <?php endif; ?>
            </div>
        </nav>

        <div class="header-actions">
            <form class="header-search" action="<?= e(url('/buscar')) ?>" method="get" role="search">
                <?= icon('search', 'icon icon-sm') ?>
                <input type="search" name="q" value="<?= e($currentPath === '/buscar' ? $headerQuery : '') ?>" placeholder="Especie, medio, explante…" aria-label="Buscar en el portal">
            </form>

            <?php if ($currentUser): ?>
                <a class="header-link" href="<?= e(url('/mi-panel')) ?>">Mi panel</a>
                <details class="user-menu">
                    <summary>
                        <?= avatar_tag((string) $currentUser['name'], $currentUser['avatar_url'] ?? null, 'avatar-sm') ?>
                        <span class="user-menu-name"><?= e(first_name((string) $currentUser['name'])) ?></span>
                        <?= icon('chevron-down', 'icon icon-xs') ?>
                    </summary>
                    <div class="user-menu-panel">
                        <div class="user-menu-head">
                            <strong><?= e($currentUser['name']) ?></strong>
                            <span><?= e($currentUser['email']) ?></span>
                            <span class="badge badge-<?= $currentUser['status'] === 'verificado' ? 'success' : 'warning' ?>"><?= $currentUser['status'] === 'verificado' ? 'Verificado' : 'Por verificar' ?></span>
                        </div>
                        <a href="<?= e(url('/mi-panel')) ?>"><?= icon('dashboard') ?> Mi panel</a>
                        <a href="<?= e(url('/perfil')) ?>"><?= icon('user') ?> Mi perfil</a>
                        <a href="<?= e(url('/casos/nuevo')) ?>"><?= icon('flask') ?> Publicar un caso</a>
                        <a href="<?= e(url('/reportes/nuevo')) ?>"><?= icon('file-text') ?> Subir un reporte</a>
                        <?php if (can('moderar')): ?>
                            <a href="<?= e(url('/admin')) ?>"><?= icon('shield') ?> Panel editorial</a>
                        <?php endif; ?>
                        <form method="post" action="<?= e(url('/salir')) ?>">
                            <?= csrf_field() ?>
                            <button type="submit"><?= icon('logout') ?> Cerrar sesión</button>
                        </form>
                    </div>
                </details>
            <?php else: ?>
                <a class="header-link" href="<?= e(url('/acceder')) ?>">Acceder</a>
                <a class="btn btn-primary btn-sm" href="<?= e(url('/registro')) ?>">Crear cuenta</a>
            <?php endif; ?>
        </div>

        <button class="menu-toggle" type="button" data-menu-toggle aria-controls="main-nav" aria-expanded="false" aria-label="Abrir menú">
            <span class="menu-toggle-open"><?= icon('menu') ?></span>
            <span class="menu-toggle-close"><?= icon('close') ?></span>
        </button>
    </div>
</header>

<?php \App\Core\View::partial('partials/flash', ['flashMessages' => $flashMessages]); ?>

<main id="contenido" class="site-main">
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="brand brand-light" href="<?= e(url('/')) ?>">
                <img class="brand-mark" src="<?= e(url('/assets/img/logo.svg')) ?>" alt="" width="44" height="44">
                <span class="brand-text">
                    <span class="brand-name">Vitro<span class="brand-accent">Lab</span></span>
                    <span class="brand-sub">MX</span>
                </span>
            </a>
            <p>Una comunidad para documentar el cultivo in vitro de material vegetal: casos con protocolo completo, reportes de laboratorio y una biblioteca abierta de fuentes.</p>
            <span class="footer-stamp">Hecho en México · Español · Sin cédula profesional</span>
        </div>

        <div class="footer-col">
            <h2>Explorar</h2>
            <a href="<?= e(url('/casos')) ?>">Casos de cultivo</a>
            <a href="<?= e(url('/reportes')) ?>">Reportes de laboratorio</a>
            <a href="<?= e(url('/especies')) ?>">Especies</a>
            <a href="<?= e(url('/instituciones')) ?>">Instituciones</a>
            <a href="<?= e(url('/buscar')) ?>">Buscador</a>
        </div>

        <div class="footer-col">
            <h2>Comunidad</h2>
            <a href="<?= e(url('/registro')) ?>">Crear cuenta</a>
            <a href="<?= e(url('/acceder')) ?>">Acceder</a>
            <a href="<?= e(url('/guias')) ?>">Guías para documentar</a>
            <a href="<?= e(url('/biblioteca')) ?>">Biblioteca y fuentes</a>
            <a href="<?= e(url('/glosario')) ?>">Glosario</a>
        </div>

        <div class="footer-col">
            <h2>Legal</h2>
            <a href="<?= e(url('/etica')) ?>">Ética editorial</a>
            <a href="<?= e(url('/privacidad')) ?>">Aviso de privacidad</a>
            <a href="<?= e(url('/terminos')) ?>">Términos de uso</a>
            <a href="<?= e(url('/nosotros')) ?>">Quiénes somos</a>
            <a href="<?= e(url('/salud')) ?>">Estado del servicio</a>
        </div>
    </div>

    <div class="container footer-bottom">
        <p>© <?= date('Y') ?> VitroLab MX. El contenido es educativo y no sustituye asesoría técnica, sanitaria ni legal.</p>
        <p>Los casos y reportes son responsabilidad de sus autores · Plataforma con revisión editorial.</p>
    </div>
</footer>

<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
