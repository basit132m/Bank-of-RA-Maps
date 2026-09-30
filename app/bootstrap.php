<?php

declare(strict_types=1);

use App\Core\Config;

define('BASE_PATH', dirname(__DIR__));

// Autoloader: App\Core\Router -> app/Core/Router.php
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file     = BASE_PATH . '/app/' . $relative . '.php';

    if (is_file($file)) {
        require $file;
    }
});

require BASE_PATH . '/app/Core/helpers.php';

// Real configuration wins; the committed local config is the development fallback.
$configFile = is_file(BASE_PATH . '/config/config.php')
    ? BASE_PATH . '/config/config.php'
    : BASE_PATH . '/config/config.local.php';

Config::load($configFile);

date_default_timezone_set((string) Config::get('app.timezone', 'UTC'));

if (Config::isDebug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');
}
