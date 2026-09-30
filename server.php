<?php

/**
 * Router for PHP's built-in development server, which has no .htaccess.
 *
 *   php -S 127.0.0.1:8000 -t public_html server.php
 *
 * Existing files are served as-is; everything else goes to the front controller,
 * matching what mod_rewrite does in production.
 */

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) ? $path : '/';
$file = __DIR__ . '/public_html' . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/public_html/index.php';
