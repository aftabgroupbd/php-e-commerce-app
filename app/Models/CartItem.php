<?php

declare(strict_types=1);

namespace App\Models;

/**
 * One line of the shopping cart.
 *
 * This is not a database table - the cart lives in the session. The price
 * always comes from the Product that was just loaded from the database, never
 * from the browser.
 */
final class CartItem
{
    public function __construct(
        public readonly Product $product,
        public readonly int $quantity,
    ) {
    }

    public function subtotal(): float
    {
        return round($this->product->price * $this->quantity, 2);
    }

    public function formattedSubtotal(): string
    {
        return format_price($this->subtotal());
    }
}
