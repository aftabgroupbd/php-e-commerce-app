<?php

declare(strict_types=1);

namespace App\Models;

/**
 * A single row of the "customers" table, as a typed object.
 *
 * Models are plain data holders: no SQL, no business rules. They exist so the
 * rest of the application works with $customer->email instead of
 * $customer['email'] and so a typo becomes an error instead of an empty value.
 */
final class Customer
{
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_ADMIN    = 'admin';

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $role,
        public readonly ?string $createdAt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $row a row fetched with PDO::FETCH_ASSOC
     */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['email'],
            (string) ($row['password'] ?? ''),
            (string) ($row['role'] ?? self::ROLE_CUSTOMER),
            isset($row['created_at']) ? (string) $row['created_at'] : null,
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * The small snapshot that is kept in the session after login.
     * The password hash is deliberately NOT part of it.
     *
     * @return array{id:int, name:string, email:string, role:string}
     */
    public function toSession(): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
            'role'  => $this->role,
        ];
    }
}
