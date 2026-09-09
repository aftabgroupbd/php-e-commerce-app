<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CustomerRepository;
use App\Services\AuthService;
use App\Support\Request;
use App\Support\Session;
use App\Validation\AuthValidator;

/**
 * Login, registration and logout.
 *
 * Notice what a controller does: read the request, validate it, call a
 * service, decide where to go next. There is no SQL and no password logic
 * here - that lives in the repository and the service.
 */
final class AuthController
{
    private AuthValidator $validator;

    public function __construct(
        private readonly AuthService $auth,
        private readonly CustomerRepository $customers,
    ) {
        $this->validator = new AuthValidator();
    }

    public function showLogin(Request $request): void
    {
        view('auth/login', ['title' => 'Login']);
    }

    public function login(Request $request): void
    {
        require_csrf($request->input('_token'));

        $data   = $request->only(['email', 'password']);
        $errors = $this->validator->validateLogin($data);

        if ($errors !== []) {
            redirect_with_errors('/login', $errors, $data);
        }

        if (!$this->auth->attempt((string) $data['email'], (string) $data['password'])) {
            // One generic message for both cases: never reveal which emails exist.
            redirect_with_errors('/login', ['email' => 'These credentials do not match our records.'], $data);
        }

        flash('success', 'Welcome back!');

        $intended = Session::getFlash('intended');

        redirect(is_string($intended) && $intended !== '' ? $intended : '/account');
    }

    public function showRegister(Request $request): void
    {
        view('auth/registration', ['title' => 'Create an account']);
    }

    public function register(Request $request): void
    {
        require_csrf($request->input('_token'));

        $data   = $request->only(['name', 'email', 'password', 'password_confirmation']);
        $errors = $this->validator->validateRegistration($data, $this->customers);

        if ($errors !== []) {
            redirect_with_errors('/register', $errors, $data);
        }

        $this->auth->register(
            (string) $data['name'],
            (string) $data['email'],
            (string) $data['password']
        );

        flash('success', 'Your account has been created.');

        redirect('/account');
    }

    public function logout(Request $request): void
    {
        // Even logging out is a POST with a token, so another site cannot
        // log your visitors out behind their back.
        require_csrf($request->input('_token'));

        $this->auth->logout();

        flash('success', 'You have been logged out.');

        redirect('/login');
    }
}
