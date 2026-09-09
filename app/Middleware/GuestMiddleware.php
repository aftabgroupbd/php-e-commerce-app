<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * "Only visitors who are NOT logged in."
 *
 * Used for the login and registration pages: an already logged in customer is
 * sent to their account page instead.
 */
final class GuestMiddleware
{
    public static function handle(): void
    {
        if (is_authenticated()) {
            redirect('/account');
        }
    }
}
