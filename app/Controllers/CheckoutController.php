<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CartService;
use App\Services\CheckoutService;
use App\Support\DomainException;
use App\Support\Request;
use App\Validation\CheckoutValidator;

/**
 * Placing an order.
 *
 * Both routes are behind AuthMiddleware: you have to be logged in to buy.
 */
final class CheckoutController
{
    private CheckoutValidator $validator;

    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
    ) {
        $this->validator = new CheckoutValidator();
    }

    public function index(Request $request): void
    {
        if ($this->cart->isEmpty()) {
            flash('warning', 'Your cart is empty.');

            redirect('/cart');
        }

        view('checkout/index', [
            'title'    => 'Checkout',
            'items'    => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
            'customer' => current_user(),
        ]);
    }

    public function store(Request $request): void
    {
        require_csrf($request->input('_token'));

        $data   = $request->only(['full_name', 'phone', 'address', 'city']);
        $errors = $this->validator->validate($data);

        if ($errors !== []) {
            redirect_with_errors('/checkout', $errors, $data);
        }

        try {
            $orderId = $this->checkout->placeOrder((int) current_user_id(), [
                'full_name' => (string) $data['full_name'],
                'phone'     => (string) $data['phone'],
                'address'   => (string) $data['address'],
                'city'      => (string) $data['city'],
            ]);
        } catch (DomainException $e) {
            // An expected problem (empty cart, not enough stock).
            flash('danger', $e->getMessage());

            redirect('/cart');
        }

        flash('success', sprintf('Thank you! Your order #%d has been placed.', $orderId));

        redirect('/orders');
    }
}
