<?php

declare(strict_types=1);

use App\Support\Session;

/**
 * Authentication helpers.
 *
 * After a successful login the AuthService stores a small snapshot of the user
 * in the session (id, name, email, role). These helpers read it, so a view or
 * middleware never has to touch $_SESSION directly.
 */

if (!function_exists('current_user')) {
    /**
     * @return array{id:int, name:string, email:string, role:string}|null
     */
    function current_user(): ?array
    {
        $user = Session::get('user');

        return is_array($user) ? $user : null;
    }
}

if (!function_exists('is_authenticated')) {
    function is_authenticated(): bool
    {
        return current_user() !== null;
    }
}

if (!function_exists('current_user_id')) {
    function current_user_id(): ?int
    {
        $user = current_user();

        return $user === null ? null : (int) $user['id'];
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        $user = current_user();

        return $user !== null && $user['role'] === 'admin';
    }
}
