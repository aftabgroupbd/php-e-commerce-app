<?php

declare(strict_types=1);

namespace App\Models;

/**
 * A single row of the "orders" table.
 *
 * $customerName / $customerEmail are only filled when the row was loaded with
 * a JOIN (used by the admin order list).
 */
final class Order
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_PAID      = 'paid';
    public const STATUS_SHIPPED   = 'shipped';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /** @return string[] */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PAID,
            self::STATUS_SHIPPED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public function __construct(
        public readonly int $id,
        public readonly int $customerId,
        public readonly float $totalAmount,
        public readonly string $status,
        public readonly string $shippingName = '',
        public readonly string $shippingPhone = '',
        public readonly string $shippingAddress = '',
        public readonly string $shippingCity = '',
        public readonly ?string $createdAt = null,
        public readonly ?string $customerName = null,
        public readonly ?string $customerEmail = null,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['customer_id'],
            (float) $row['total_amount'],
            (string) $row['status'],
            (string) ($row['shipping_name'] ?? ''),
            (string) ($row['shipping_phone'] ?? ''),
            (string) ($row['shipping_address'] ?? ''),
            (string) ($row['shipping_city'] ?? ''),
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['customer_name']) ? (string) $row['customer_name'] : null,
            isset($row['customer_email']) ? (string) $row['customer_email'] : null,
        );
    }

    public function formattedTotal(): string
    {
        return format_price($this->totalAmount);
    }

    /**
     * Bootstrap badge colour for the status label.
     */
    public function statusColour(): string
    {
        return match ($this->status) {
            self::STATUS_PAID, self::STATUS_COMPLETED => 'success',
            self::STATUS_SHIPPED                      => 'info',
            self::STATUS_CANCELLED                    => 'danger',
            default                                   => 'warning',
        };
    }
}
