<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * "You must be logged in AND have the admin role."
 *
 * A normal customer who guesses an admin URL gets a 403 page - the admin
 * controller is never reached, so they cannot create or delete anything.
 */
final class AdminMiddleware
{
    public static function handle(): void
    {
        // First make sure somebody is logged in at all.
        AuthMiddleware::handle();

        if (!is_admin()) {
            abort(403, 'This area is for administrators only.');
        }
    }
}
