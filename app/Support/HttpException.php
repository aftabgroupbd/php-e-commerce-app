<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * Thrown when the request must be stopped with a specific HTTP status,
 * for example 404 (not found) or 419 (invalid CSRF token).
 */
final class HttpException extends RuntimeException
{
    public function __construct(private readonly int $status, string $message = '')
    {
        parent::__construct($message === '' ? self::defaultMessage($status) : $message, $status);
    }

    public function status(): int
    {
        return $this->status;
    }

    private static function defaultMessage(int $status): string
    {
        return match ($status) {
            403     => 'You are not allowed to access this page.',
            404     => 'The page you are looking for does not exist.',
            419     => 'Your session expired. Please try again.',
            default => 'Something went wrong.',
        };
    }
}
