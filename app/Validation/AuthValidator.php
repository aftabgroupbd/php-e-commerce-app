<?php

declare(strict_types=1);

namespace App\Validation;

use App\Repositories\CustomerRepository;
use App\Support\Validator;

/**
 * Server side validation for login and registration.
 *
 * Client side validation (the "required" attribute in HTML) is only a comfort
 * feature - anybody can send a POST request without a browser, so the real
 * check has to happen here.
 */
final class AuthValidator extends Validator
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, string> field => error message
     */
    public function validateLogin(array $data): array
    {
        $this->reset();

        $email    = (string) ($data['email'] ?? '');
        $password = (string) ($data['password'] ?? '');

        if ($this->required('email', $email, 'Email')) {
            $this->email('email', $email);
        }

        $this->required('password', $password, 'Password');

        return $this->errors;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function validateRegistration(array $data, CustomerRepository $customers): array
    {
        $this->reset();

        $name                 = (string) ($data['name'] ?? '');
        $email                = (string) ($data['email'] ?? '');
        $password             = (string) ($data['password'] ?? '');
        $passwordConfirmation = (string) ($data['password_confirmation'] ?? '');

        if ($this->required('name', $name, 'Name')) {
            $this->between('name', $name, 2, 100, 'Name');
        }

        if ($this->required('email', $email, 'Email')) {
            $this->email('email', $email);

            // Only ask the database once the format is already correct.
            if (!isset($this->errors['email']) && $customers->emailExists($email)) {
                $this->addError('email', 'This email address is already registered.');
            }
        }

        if ($this->required('password', $password, 'Password')) {
            $this->between('password', $password, 8, 255, 'Password');

            if (!isset($this->errors['password']) && $password !== $passwordConfirmation) {
                $this->addError('password_confirmation', 'The two passwords do not match.');
            }
        }

        return $this->errors;
    }
}
