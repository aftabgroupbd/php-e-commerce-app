<?php

declare(strict_types=1);

/**
 * Database configuration used by App\Database\Database.
 */
return [
    'host'     => env('DB_HOST', 'localhost'),
    'port'     => (int) env('DB_PORT', '3306'),
    'name'     => env('DB_NAME', 'hw'),
    'user'     => env('DB_USER', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset'  => env('DB_CHARSET', 'utf8mb4'),
];
