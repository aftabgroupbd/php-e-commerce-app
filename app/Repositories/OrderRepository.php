<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Order;
use PDO;

/**
 * All SQL for the "orders" table.
 */
final class OrderRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array{full_name:string, phone:string, address:string, city:string} $shipping
     */
    public function create(int $customerId, float $totalAmount, array $shipping, string $status = Order::STATUS_PENDING): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO orders (customer_id, total_amount, status, shipping_name, shipping_phone, shipping_address, shipping_city)
             VALUES (:customer_id, :total_amount, :status, :shipping_name, :shipping_phone, :shipping_address, :shipping_city)'
        );

        $statement->execute([
            'customer_id'      => $customerId,
            'total_amount'     => $totalAmount,
            'status'           => $status,
            'shipping_name'    => $shipping['full_name'],
            'shipping_phone'   => $shipping['phone'],
            'shipping_address' => $shipping['address'],
            'shipping_city'    => $shipping['city'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Orders of one customer, newest first (used for "My orders").
     *
     * @return Order[]
     */
    public function findByCustomer(int $customerId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM orders WHERE customer_id = :customer_id ORDER BY id DESC'
        );

        $statement->execute(['customer_id' => $customerId]);

        return array_map(
            static fn (array $row): Order => Order::fromArray($row),
            $statement->fetchAll()
        );
    }

    public function findById(int $id): ?Order
    {
        $statement = $this->pdo->prepare(
            'SELECT o.*, c.name AS customer_name, c.email AS customer_email
               FROM orders o
               JOIN customers c ON c.id = o.customer_id
              WHERE o.id = :id
              LIMIT 1'
        );

        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row ? Order::fromArray($row) : null;
    }

    /**
     * All orders with the customer name attached (admin order list).
     *
     * @return Order[]
     */
    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT o.*, c.name AS customer_name, c.email AS customer_email
               FROM orders o
               JOIN customers c ON c.id = o.customer_id
              ORDER BY o.id DESC'
        );

        return array_map(
            static fn (array $row): Order => Order::fromArray($row),
            $statement->fetchAll()
        );
    }

    public function updateStatus(int $id, string $status): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE orders SET status = :status WHERE id = :id'
        );

        $statement->execute([
            'id'     => $id,
            'status' => $status,
        ]);

        return $statement->rowCount() > 0;
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    }

    /**
     * Total money of all orders that were not cancelled.
     */
    public function totalRevenue(): float
    {
        $statement = $this->pdo->query(
            "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status <> 'cancelled'"
        );

        return (float) $statement->fetchColumn();
    }
}
