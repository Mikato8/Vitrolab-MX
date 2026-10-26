<?php

declare(strict_types=1);

// Router para el servidor embebido de PHP:
//   php -S 127.0.0.1:8085 -t public bin/router.php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = dirname(__DIR__) . '/public' . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}
require dirname(__DIR__) . '/public/index.php';
