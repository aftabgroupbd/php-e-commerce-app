<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Services\AuthService;
use App\Support\Request;

/**
 * The logged in customer's own pages: account details, order history and
 * deleting the account.
 *
 * Every route of this controller is protected by AuthMiddleware, so
 * current_user_id() is guaranteed to exist here.
 */
final class CustomerController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly OrderRepository $orders,
        private readonly OrderItemRepository $orderItems,
    ) {
    }

    public function account(Request $request): void
    {
        $customer = $this->auth->findById((int) current_user_id());

        if ($customer === null) {
            // The account was deleted in another tab or by an admin.
            $this->auth->logout();

            redirect('/login');
        }

        view('customer/account', [
            'title'    => 'My account',
            'customer' => $customer,
            'orders'   => $this->orders->findByCustomer($customer->id),
        ]);
    }

    /**
     * Order history with the purchased items.
     */
    public function orders(Request $request): void
    {
        $orders = $this->orders->findByCustomer((int) current_user_id());

        view('customer/orders', [
            'title'  => 'My orders',
            'orders' => $orders,
            'items'  => $this->orderItems->findByOrderIds(
                array_map(static fn ($order): int => $order->id, $orders)
            ),
        ]);
    }

    /**
     * Delete the account. This is a POST route with a CSRF token: in the old
     * project a simple link (?deleteCustomer=true) was enough, which meant any
     * other website could delete a logged in customer's account.
     */
    public function destroy(Request $request): void
    {
        require_csrf($request->input('_token'));

        $this->auth->deleteAccount((int) current_user_id());

        flash('success', 'Your account has been deleted.');

        redirect('/register');
    }
}
