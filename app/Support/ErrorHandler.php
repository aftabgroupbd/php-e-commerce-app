<?php

declare(strict_types=1);

namespace App\Support;

use ErrorException;
use Throwable;

/**
 * Central error handling.
 *
 * In development you see the real message and stack trace.
 * In production the visitor only sees a friendly page, while the details are
 * written to storage/logs/.
 */
final class ErrorHandler
{
    public static function register(): void
    {
        $debug = (bool) config('app.debug');

        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');

        // Turn PHP warnings/notices into exceptions so nothing is silently ignored.
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler([self::class, 'handle']);
    }

    public static function handle(Throwable $e): void
    {
        $status = $e instanceof HttpException ? $e->status() : 500;

        if ($status >= 500) {
            logger(sprintf(
                '%s: %s in %s:%d%s%s',
                $e::class,
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                PHP_EOL,
                $e->getTraceAsString()
            ));
        }

        if (!headers_sent()) {
            http_response_code($status);
        }

        $debug = (bool) config('app.debug');

        // A view can only be rendered if the session/helpers are available.
        $message = $e instanceof HttpException || $debug
            ? $e->getMessage()
            : 'Something went wrong on our side. Please try again later.';

        try {
            view('errors/error', [
                'title'   => 'Error ' . $status,
                'status'  => $status,
                'message' => $message,
                'trace'   => $debug && !$e instanceof HttpException ? $e->getTraceAsString() : null,
            ]);
        } catch (Throwable) {
            // Last resort: the view system itself failed.
            echo '<h1>Error ' . $status . '</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        }
    }
}
