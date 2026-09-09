<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\OrderItem;
use PDO;

/**
 * All SQL for the "order_items" table.
 */
final class OrderItemRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Insert every line of an order.
     *
     * Called inside the checkout transaction, so either all items are stored
     * or none of them are.
     *
     * @param array<int, array{product_id:?int, product_name:string, price:float, quantity:int, subtotal:float}> $items
     */
    public function createMany(int $orderId, array $items): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal)
             VALUES (:order_id, :product_id, :product_name, :price, :quantity, :subtotal)'
        );

        foreach ($items as $item) {
            $statement->execute([
                'order_id'     => $orderId,
                'product_id'   => $item['product_id'],
                'product_name' => $item['product_name'],
                'price'        => $item['price'],
                'quantity'     => $item['quantity'],
                'subtotal'     => $item['subtotal'],
            ]);
        }
    }

    /**
     * @return OrderItem[]
     */
    public function findByOrderId(int $orderId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id'
        );

        $statement->execute(['order_id' => $orderId]);

        return array_map(
            static fn (array $row): OrderItem => OrderItem::fromArray($row),
            $statement->fetchAll()
        );
    }

    /**
     * Items for several orders at once, grouped by order id.
     * Avoids running one query per order when a list is rendered.
     *
     * @param int[] $orderIds
     * @return array<int, OrderItem[]>
     */
    public function findByOrderIds(array $orderIds): array
    {
        $orderIds = array_values(array_unique(array_filter($orderIds, static fn ($id): bool => $id > 0)));

        if ($orderIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($orderIds), '?'));

        $statement = $this->pdo->prepare(
            "SELECT * FROM order_items WHERE order_id IN ({$placeholders}) ORDER BY id"
        );
        $statement->execute($orderIds);

        $grouped = [];

        foreach ($statement->fetchAll() as $row) {
            $item = OrderItem::fromArray($row);
            $grouped[$item->orderId][] = $item;
        }

        return $grouped;
    }
}
