<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Support\DomainException;
use PDO;
use Throwable;

/**
 * Turns a session cart into a real order.
 *
 * The whole thing runs inside ONE database transaction: the order row, all
 * order items and the stock updates either all succeed, or the database is
 * rolled back as if nothing happened. Without that you can end up with an
 * order that has no items, or stock that was reduced for a failed order.
 */
final class CheckoutService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly ProductRepository $products,
        private readonly OrderRepository $orders,
        private readonly OrderItemRepository $orderItems,
        private readonly CartService $cart,
    ) {
    }

    /**
     * @param array{full_name:string, phone:string, address:string, city:string} $shipping
     * @return int the id of the created order
     */
    public function placeOrder(int $customerId, array $shipping): int
    {
        $cart = $this->cart->rawCart();

        if ($cart === []) {
            throw new DomainException('Your cart is empty.');
        }

        $this->pdo->beginTransaction();

        try {
            // Lock the product rows so the stock cannot change underneath us.
            $products = $this->products->findManyByIdsForUpdate(array_keys($cart));

            $items = [];
            $total = 0.0;

            foreach ($cart as $productId => $quantity) {
                $product = $products[$productId] ?? null;

                if ($product === null) {
                    throw new DomainException('A product in your cart is no longer available.');
                }

                if ($product->stock < $quantity) {
                    throw new DomainException(sprintf(
                        'Only %d item(s) of "%s" are left in stock.',
                        $product->stock,
                        $product->name
                    ));
                }

                // The price ALWAYS comes from the database, never from the form.
                $subtotal = round($product->price * $quantity, 2);
                $total   += $subtotal;

                $items[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'price'        => $product->price,
                    'quantity'     => $quantity,
                    'subtotal'     => $subtotal,
                ];
            }

            $total   = round($total, 2);
            $orderId = $this->orders->create($customerId, $total, $shipping);

            $this->orderItems->createMany($orderId, $items);

            foreach ($items as $item) {
                if (!$this->products->decreaseStock($item['product_id'], $item['quantity'])) {
                    throw new DomainException(sprintf(
                        'Stock for "%s" changed while you were checking out. Please try again.',
                        $item['product_name']
                    ));
                }
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }

        // Only empty the cart once the order is safely stored.
        $this->cart->clear();

        return $orderId;
    }
}
