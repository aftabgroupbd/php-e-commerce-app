<?php

declare(strict_types=1);

/**
 * Front controller - the ONE entry point of the application.
 *
 * Every request (/, /products/3, /admin/orders) is handled by this file:
 *
 *     browser -> public/index.php -> Router -> Middleware -> Controller
 *
 * Only this "public" folder should be reachable from the web. Everything else
 * (app/, config/, storage/, vendor/, .env) stays one level above it and can
 * never be downloaded.
 */

use App\Support\Request;
use App\Support\Router;

// When running "php -S localhost:8000 -t public public/index.php" the built-in
// web server sends every request here, including CSS and images. Returning
// false tells it to serve those files directly.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . urldecode((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    if (is_file($file)) {
        return false;
    }
}

/** @var \App\Support\Container $container */
$container = require dirname(__DIR__) . '/bootstrap.php';

/** @var Router $router */
$router = require dirname(__DIR__) . '/routes/web.php';

$router->dispatch(Request::capture(), $container);
