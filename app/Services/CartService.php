<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CartItem;
use App\Repositories\ProductRepository;
use App\Support\DomainException;
use App\Support\Session;

/**
 * A shopping cart that lives in the session.
 *
 * The session only stores [product id => quantity]. Names and prices are
 * always read from the database, so a manipulated form field can never change
 * what a product costs.
 */
final class CartService
{
    public const SESSION_KEY   = 'cart';
    private const MAX_QUANTITY = 99;

    public function __construct(private readonly ProductRepository $products)
    {
    }

    public function add(int $productId, int $quantity): void
    {
        $product = $this->products->findById($productId);

        if ($product === null) {
            throw new DomainException('That product does not exist.');
        }

        if (!$product->isInStock()) {
            throw new DomainException(sprintf('"%s" is out of stock.', $product->name));
        }

        $cart     = $this->rawCart();
        $newTotal = ($cart[$productId] ?? 0) + $quantity;

        if ($newTotal > $product->stock) {
            throw new DomainException(sprintf(
                'Only %d item(s) of "%s" are available.',
                $product->stock,
                $product->name
            ));
        }

        $cart[$productId] = min($newTotal, self::MAX_QUANTITY);

        $this->save($cart);
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($productId);

            return;
        }

        $product = $this->products->findById($productId);

        if ($product === null) {
            throw new DomainException('That product does not exist.');
        }

        if ($quantity > $product->stock) {
            throw new DomainException(sprintf(
                'Only %d item(s) of "%s" are available.',
                $product->stock,
                $product->name
            ));
        }

        $cart              = $this->rawCart();
        $cart[$productId]  = min($quantity, self::MAX_QUANTITY);

        $this->save($cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->rawCart();
        unset($cart[$productId]);

        $this->save($cart);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function isEmpty(): bool
    {
        return $this->rawCart() === [];
    }

    /**
     * The cart lines, with fresh product data from the database.
     * Products that no longer exist are silently dropped from the cart.
     *
     * @return CartItem[]
     */
    public function items(): array
    {
        $cart = $this->rawCart();

        if ($cart === []) {
            return [];
        }

        $products = $this->products->findManyByIds(array_keys($cart));
        $items    = [];
        $changed  = false;

        foreach ($cart as $productId => $quantity) {
            if (!isset($products[$productId])) {
                unset($cart[$productId]);
                $changed = true;

                continue;
            }

            $items[] = new CartItem($products[$productId], $quantity);
        }

        if ($changed) {
            $this->save($cart);
        }

        return $items;
    }

    public function subtotal(): float
    {
        $total = 0.0;

        foreach ($this->items() as $item) {
            $total += $item->subtotal();
        }

        return round($total, 2);
    }

    /**
     * Total number of articles, used for the "Cart (3)" badge in the navbar.
     */
    public function count(): int
    {
        return array_sum($this->rawCart());
    }

    /**
     * @return array<int, int> [product id => quantity]
     */
    public function rawCart(): array
    {
        $cart = Session::get(self::SESSION_KEY, []);

        if (!is_array($cart)) {
            return [];
        }

        $clean = [];

        // The session is trusted less than you think: normalise it anyway.
        foreach ($cart as $productId => $quantity) {
            $productId = (int) $productId;
            $quantity  = (int) $quantity;

            if ($productId > 0 && $quantity > 0) {
                $clean[$productId] = min($quantity, self::MAX_QUANTITY);
            }
        }

        return $clean;
    }

    /**
     * @param array<int, int> $cart
     */
    private function save(array $cart): void
    {
        if ($cart === []) {
            $this->clear();

            return;
        }

        Session::put(self::SESSION_KEY, $cart);
    }
}
