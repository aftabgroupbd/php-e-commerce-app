<?php

declare(strict_types=1);

use App\Services\CartService;
use App\Support\Env;
use App\Support\Session;

/**
 * Global helper functions that are available everywhere.
 * They are loaded by Composer (see the "files" section of composer.json).
 */

if (!function_exists('base_path')) {
    /**
     * Absolute path to the project root, optionally with a sub path appended.
     */
    function base_path(string $path = ''): string
    {
        $root = dirname(__DIR__, 2);

        return $path === '' ? $root : $root . '/' . ltrim($path, '/');
    }
}

if (!function_exists('env')) {
    /**
     * Read a value from the .env file (see App\Support\Env).
     */
    function env(string $key, ?string $default = null): ?string
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('config')) {
    /**
     * Read a configuration value using "file.key" dot notation.
     *
     * Example: config('database.host') reads config/database.php => ['host' => ...]
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $loaded = [];

        [$file, $item] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset($loaded[$file])) {
            $path = base_path('config/' . $file . '.php');

            if (!is_file($path)) {
                throw new RuntimeException("Configuration file [config/{$file}.php] not found.");
            }

            $loaded[$file] = require $path;
        }

        if ($item === null) {
            return $loaded[$file];
        }

        return $loaded[$file][$item] ?? $default;
    }
}

if (!function_exists('url')) {
    /**
     * Build an application URL, taking APP_BASE_PATH into account.
     */
    function url(string $path = '/'): string
    {
        return config('app.base_path') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Build a URL for a file inside public/assets.
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('product_image_url')) {
    /**
     * Build the public URL of a product image, with a fallback placeholder.
     */
    function product_image_url(?string $filename): string
    {
        if ($filename === null || $filename === '') {
            return asset('images/placeholder.svg');
        }

        return url(ltrim(config('app.upload_url'), '/') . '/' . $filename);
    }
}

if (!function_exists('format_price')) {
    /**
     * Format a price for display. Kept in one place so every page looks the same.
     */
    function format_price(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }
}

if (!function_exists('cart_count')) {
    /**
     * How many articles are currently in the cart (for the navbar badge).
     *
     * Reads the session only - the navbar must render even when the database
     * is unreachable (for example on the error page).
     */
    function cart_count(): int
    {
        $cart = Session::get(CartService::SESSION_KEY, []);

        if (!is_array($cart)) {
            return 0;
        }

        return array_sum(array_map('intval', $cart));
    }
}

if (!function_exists('logger')) {
    /**
     * Append a line to storage/logs/app-YYYY-MM-DD.log.
     */
    function logger(string $message, string $level = 'error'): void
    {
        $directory = config('app.log_path');

        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $line = sprintf(
            "[%s] %s: %s%s",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            PHP_EOL
        );

        @file_put_contents($directory . '/app-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
    }
}
