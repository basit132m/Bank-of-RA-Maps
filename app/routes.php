<?php

declare(strict_types=1);

use App\Controllers\DownloadController;
use App\Controllers\HomeController;
use App\Controllers\MapController;
use App\Controllers\PageController;
use App\Core\Router;

$router = new Router();

$router->get('/', [HomeController::class, 'index']);

$router->get('/maps', [MapController::class, 'index']);
$router->get('/maps/tag/{tag}', [MapController::class, 'tag']);
$router->get('/maps/{slug}', [MapController::class, 'show']);

$router->get('/download/{slug}', [DownloadController::class, 'map']);
$router->get('/download/{slug}/{fileId}', [DownloadController::class, 'file']);

$router->get('/guides', [PageController::class, 'guides']);
$router->get('/guides/install', [PageController::class, 'installGuide']);
$router->get('/community', [PageController::class, 'community']);
$router->get('/about', [PageController::class, 'about']);

return $router;
