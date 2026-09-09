<?php

declare(strict_types=1);

/**
 * Application configuration.
 *
 * Every value comes from the .env file so that no credential or
 * environment specific value is hardcoded in the source code.
 */
return [
    'name'        => env('APP_NAME', 'Mini Shop'),
    'env'         => env('APP_ENV', 'production'),
    'debug'       => env('APP_ENV', 'production') === 'development',

    // URL prefix the app is served from, e.g. "" or "/assessment/public".
    'base_path'   => rtrim((string) env('APP_BASE_PATH', ''), '/'),

    // Where uploaded product images are stored (absolute path) and
    // the public URL prefix used to display them.
    'upload_path' => base_path('public/uploads/products'),
    'upload_url'  => '/uploads/products',

    'log_path'    => base_path('storage/logs'),
];
