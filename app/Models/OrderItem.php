<?php

declare(strict_types=1);

namespace App\Models;

/**
 * A single row of the "order_items" table: one product inside one order.
 *
 * The product name and price are stored per item so an old order still shows
 * the values that were valid at the moment of purchase.
 */
final class OrderItem
{
    public function __construct(
        public readonly int $id,
        public readonly int $orderId,
        public readonly ?int $productId,
        public readonly string $productName,
        public readonly float $price,
        public readonly int $quantity,
        public readonly float $subtotal,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['order_id'],
            $row['product_id'] === null ? null : (int) $row['product_id'],
            (string) $row['product_name'],
            (float) $row['price'],
            (int) $row['quantity'],
            (float) $row['subtotal'],
        );
    }
}
