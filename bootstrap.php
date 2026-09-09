<?php

declare(strict_types=1);

/**
 * Application bootstrap.
 *
 * Every entry point (public/index.php, a CLI script, a test) requires this
 * file. It prepares the application and hands back the container.
 */

use App\Support\Container;
use App\Support\Env;
use App\Support\ErrorHandler;
use App\Support\Session;

// 1. Composer autoloading (classes from app/ + the helper functions).
require __DIR__ . '/vendor/autoload.php';

// 2. Configuration from .env.
Env::load(__DIR__ . '/.env');

date_default_timezone_set('UTC');

// 3. Error handling: friendly page for visitors, details in storage/logs.
ErrorHandler::register();

// 4. Session with hardened cookie settings.
Session::start();

// 5. The container knows how to build every controller/service/repository.
return new Container();
