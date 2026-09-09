<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use App\Support\Session;
use RuntimeException;

/**
 * Everything about logging in, registering and logging out.
 *
 * The old project compared the password with a second SQL query, which meant
 * "email of user A + password of user B" was accepted. Here the customer is
 * loaded once and the password is checked against that customer's own hash.
 */
final class AuthService
{
    public function __construct(private readonly CustomerRepository $customers)
    {
    }

    /**
     * Try to log a user in. Returns false for both "unknown email" and
     * "wrong password" so an attacker cannot find out which emails exist.
     */
    public function attempt(string $email, string $password): bool
    {
        $customer = $this->customers->findByEmail($email);

        if ($customer === null) {
            // Spend the same amount of time as a real check would (timing attacks).
            password_verify($password, '$2y$10$usesomesillystringfoeleventwoseventeenchar');

            return false;
        }

        if (!password_verify($password, $customer->passwordHash)) {
            return false;
        }

        $this->login($customer);

        return true;
    }

    /**
     * Create a new customer account and log the person in immediately.
     */
    public function register(string $name, string $email, string $password): Customer
    {
        $id = $this->customers->create(
            $name,
            $email,
            // password_hash() adds a random salt and is slow on purpose.
            password_hash($password, PASSWORD_DEFAULT),
            Customer::ROLE_CUSTOMER
        );

        $customer = $this->customers->findById($id);

        if ($customer === null) {
            throw new RuntimeException('The account was created but could not be loaded.');
        }

        $this->login($customer);

        return $customer;
    }

    public function logout(): void
    {
        Session::invalidate();
    }

    public function deleteAccount(int $customerId): bool
    {
        $deleted = $this->customers->deleteById($customerId);

        if ($deleted) {
            $this->logout();
        }

        return $deleted;
    }

    public function findById(int $id): ?Customer
    {
        return $this->customers->findById($id);
    }

    /**
     * Put the user in the session.
     */
    private function login(Customer $customer): void
    {
        // A brand new session id prevents session fixation attacks.
        Session::regenerate();
        Session::put('user', $customer->toSession());
    }
}
