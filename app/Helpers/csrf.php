<?php

declare(strict_types=1);

use App\Support\HttpException;
use App\Support\Session;

/**
 * CSRF (Cross Site Request Forgery) protection.
 *
 * The idea: every form that changes something gets a hidden random token that
 * only our own pages know. A malicious website can make your browser send a
 * POST request, but it cannot read the token, so the request is rejected.
 */

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        $token = Session::get('_csrf_token');

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf_token', $token);
        }

        return $token;
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Print this inside every POST form: <?= csrf_field() ?>
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('verify_csrf_token')) {
    /**
     * @param string|null $token the "_token" field submitted with the form
     */
    function verify_csrf_token(?string $token): bool
    {
        $expected = Session::get('_csrf_token');

        if (!is_string($expected) || $expected === '' || !is_string($token)) {
            return false;
        }

        // hash_equals() compares in constant time (timing attack safe).
        return hash_equals($expected, $token);
    }
}

if (!function_exists('require_csrf')) {
    /**
     * Stop the request with 419 when the token is missing or wrong.
     */
    function require_csrf(?string $token): void
    {
        if (!verify_csrf_token($token)) {
            throw new HttpException(419);
        }
    }
}
