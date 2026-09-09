<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CartService;
use App\Support\DomainException;
use App\Support\Request;

/**
 * The shopping cart pages.
 *
 * All four write actions are POST + CSRF token, because they change state.
 */
final class CartController
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(Request $request): void
    {
        view('cart/index', [
            'title'    => 'Shopping cart',
            'items'    => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
        ]);
    }

    public function add(Request $request): void
    {
        require_csrf($request->input('_token'));

        $productId = $request->integer('product_id');
        $quantity  = max(1, $request->integer('quantity', 1));

        if ($productId <= 0) {
            flash('danger', 'Please choose a product first.');

            redirect('/products');
        }

        try {
            $this->cart->add($productId, $quantity);
            flash('success', 'The product was added to your cart.');
        } catch (DomainException $e) {
            flash('danger', $e->getMessage());
        }

        redirect('/cart');
    }

    public function update(Request $request): void
    {
        require_csrf($request->input('_token'));

        try {
            $this->cart->updateQuantity($request->integer('product_id'), $request->integer('quantity'));
            flash('success', 'Your cart has been updated.');
        } catch (DomainException $e) {
            flash('danger', $e->getMessage());
        }

        redirect('/cart');
    }

    public function remove(Request $request): void
    {
        require_csrf($request->input('_token'));

        $this->cart->remove($request->integer('product_id'));

        flash('success', 'The product was removed from your cart.');

        redirect('/cart');
    }

    public function clear(Request $request): void
    {
        require_csrf($request->input('_token'));

        $this->cart->clear();

        flash('success', 'Your cart is now empty.');

        redirect('/cart');
    }
}
