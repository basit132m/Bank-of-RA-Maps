<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;

require dirname(__DIR__) . '/app/bootstrap.php';

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => ! Config::isLocal(),
]);
session_start();

Response::securityHeaders();

$request = new Request();

/** @var Router $router */
$router = require BASE_PATH . '/app/routes.php';

try {
    echo $router->dispatch($request);
} catch (NotFoundException) {
    if (http_response_code() !== 405) {
        http_response_code(404);
    }
    echo View::render('errors/404', ['title' => 'Page not found']);
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);

    if (Config::isDebug()) {
        echo '<pre style="padding:2rem;font:14px/1.5 monospace;white-space:pre-wrap">'
            . e((string) $e) . '</pre>';
    } else {
        echo View::render('errors/500', ['title' => 'Something went wrong']);
    }
}
