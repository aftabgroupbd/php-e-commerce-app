<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * A tiny .env file reader.
 *
 * Keeping credentials in .env (which is git ignored) means the source code
 * never contains a real password.
 */
final class Env
{
    /** @var array<string, string> */
    private static array $values = [];

    private static bool $loaded = false;

    public static function load(string $path): void
    {
        self::$loaded = true;

        if (!is_readable($path)) {
            // Not fatal: the app can still run on server provided environment variables.
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new RuntimeException("Unable to read the environment file [{$path}].");
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key   = trim($key);
            $value = trim($value);

            // Strip surrounding quotes: APP_NAME="Mini Shop" => Mini Shop
            if (strlen($value) > 1 && ($value[0] === '"' || $value[0] === "'") && $value[0] === substr($value, -1)) {
                $value = substr($value, 1, -1);
            }

            self::$values[$key] = $value;
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        if (!self::$loaded) {
            self::load(base_path('.env'));
        }

        if (array_key_exists($key, self::$values)) {
            return self::$values[$key];
        }

        $fromServer = getenv($key);

        return $fromServer === false ? $default : $fromServer;
    }
}
