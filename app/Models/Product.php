<?php

declare(strict_types=1);

namespace App\Models;

/**
 * A single row of the "products" table.
 */
final class Product
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly float $price,
        public readonly int $stock,
        public readonly ?string $image,
        public readonly ?string $createdAt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['name'],
            isset($row['description']) ? (string) $row['description'] : null,
            (float) $row['price'],
            (int) ($row['stock'] ?? 0),
            ($row['image'] ?? null) !== null && $row['image'] !== '' ? (string) $row['image'] : null,
            isset($row['created_at']) ? (string) $row['created_at'] : null,
        );
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function imageUrl(): string
    {
        return product_image_url($this->image);
    }

    public function formattedPrice(): string
    {
        return format_price($this->price);
    }
}
