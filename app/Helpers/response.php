<?php

declare(strict_types=1);

use App\Support\HttpException;
use App\Support\Session;

/**
 * Response helpers: redirecting, flashing messages and aborting.
 */

if (!function_exists('redirect')) {
    /**
     * Send the browser to another page and stop the script.
     *
     * The "exit" is important: without it PHP keeps executing the rest of the
     * page, which was a real vulnerability in the old project (a redirect away
     * from a protected page still rendered that page).
     */
    function redirect(string $path): never
    {
        header('Location: ' . url($path), true, 302);

        exit;
    }
}

if (!function_exists('flash')) {
    /**
     * Show a one-time message on the next page.
     *
     * @param 'success'|'danger'|'info'|'warning' $type
     */
    function flash(string $type, string $text): void
    {
        Session::flash('status', ['type' => $type, 'text' => $text]);
    }
}

if (!function_exists('redirect_with_errors')) {
    /**
     * Go back to a form, keeping the error messages and what the user typed.
     *
     * @param array<string, string> $errors
     * @param array<string, mixed>  $old
     */
    function redirect_with_errors(string $path, array $errors, array $old = []): never
    {
        // Never send a password back to the browser.
        unset($old['password'], $old['password_confirmation'], $old['_token']);

        Session::flash('errors', $errors);
        Session::flash('old', $old);

        redirect($path);
    }
}

if (!function_exists('abort')) {
    /**
     * Stop the request with an HTTP status code (404, 403, ...).
     */
    function abort(int $status, string $message = ''): never
    {
        throw new HttpException($status, $message);
    }
}
