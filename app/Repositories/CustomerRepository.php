<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Customer;
use PDO;

/**
 * All SQL for the "customers" table lives here.
 *
 * Repository rule: every value that comes from the user is bound as a
 * parameter (:email), never glued into the SQL string. That is what makes
 * SQL injection impossible.
 */
final class CustomerRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?Customer
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM customers WHERE email = :email LIMIT 1'
        );

        $statement->execute(['email' => $email]);
        $row = $statement->fetch();

        return $row ? Customer::fromArray($row) : null;
    }

    public function findById(int $id): ?Customer
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM customers WHERE id = :id LIMIT 1'
        );

        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row ? Customer::fromArray($row) : null;
    }

    public function emailExists(string $email): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM customers WHERE email = :email LIMIT 1'
        );

        $statement->execute(['email' => $email]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * @param string $passwordHash already hashed with password_hash()
     */
    public function create(string $name, string $email, string $passwordHash, string $role = Customer::ROLE_CUSTOMER): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO customers (name, email, password, role)
             VALUES (:name, :email, :password, :role)'
        );

        $statement->execute([
            'name'     => $name,
            'email'    => $email,
            'password' => $passwordHash,
            'role'     => $role,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function deleteById(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM customers WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    /**
     * Customer list for the admin panel, newest first.
     *
     * @return Customer[]
     */
    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT * FROM customers ORDER BY created_at DESC, id DESC'
        );

        return array_map(
            static fn (array $row): Customer => Customer::fromArray($row),
            $statement->fetchAll()
        );
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
    }
}
