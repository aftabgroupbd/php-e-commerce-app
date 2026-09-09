<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Session;

/**
 * "You must be logged in to see this page."
 *
 * Middleware runs BEFORE the controller (see App\Support\Router). Attaching it
 * to a route in routes/web.php is the only thing needed to protect that page,
 * so the check can never be forgotten inside a controller method.
 */
final class AuthMiddleware
{
    public static function handle(): void
    {
        if (is_authenticated()) {
            return;
        }

        // Remember where the visitor wanted to go, then send them to the login
        // page. Only the path is stored, so this can never redirect to another
        // website (an "open redirect" vulnerability).
        Session::flash('intended', Request::capture()->path());
        flash('warning', 'Please log in to continue.');

        redirect('/login');
    }
}
