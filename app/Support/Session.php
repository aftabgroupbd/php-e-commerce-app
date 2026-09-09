<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Thin wrapper around $_SESSION.
 *
 * Everything session related lives here so that hardening (cookie flags,
 * id regeneration) is done in exactly one place.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,   // only send the cookie over HTTPS
            'httponly' => true,      // JavaScript cannot read the cookie (XSS protection)
            'samesite' => 'Lax',     // basic CSRF hardening
        ]);

        session_name('MINISHOP_SESSION');
        session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Regenerate the session id (call it right after a successful login to
     * prevent session fixation attacks).
     */
    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Throw everything away and continue with a brand new, empty session.
     *
     * Used on logout. session_regenerate_id(true) deletes the old session on
     * the server and hands the browser a new cookie, while the session stays
     * usable - which is what lets us still show the "You have been logged out"
     * message on the next page.
     */
    public static function invalidate(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }

        $_SESSION = [];

        session_regenerate_id(true);
    }

    /**
     * Store a value that is available on the *next* request only.
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }
}
