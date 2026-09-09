<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Order;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Support\Request;

/**
 * Order management for administrators.
 *
 * This page was an empty file in the old project.
 */
final class OrderController
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderItemRepository $orderItems,
    ) {
    }

    public function index(Request $request): void
    {
        view('admin/orders/index', [
            'title'  => 'Orders',
            'orders' => $this->orders->all(),
        ], 'layouts/admin');
    }

    public function show(Request $request, string $id): void
    {
        $order = $this->orders->findById($this->orderId($id));

        if ($order === null) {
            abort(404, 'This order does not exist.');
        }

        view('admin/orders/show', [
            'title' => 'Order #' . $order->id,
            'order' => $order,
            'items' => $this->orderItems->findByOrderId($order->id),
        ], 'layouts/admin');
    }

    public function updateStatus(Request $request, string $id): void
    {
        require_csrf($request->input('_token'));

        $orderId = $this->orderId($id);
        $status  = (string) $request->input('status', '');

        // Only a value from our own list is accepted, never a free string.
        if (!in_array($status, Order::statuses(), true)) {
            flash('danger', 'That order status does not exist.');

            redirect('/admin/orders/' . $orderId);
        }

        $this->orders->updateStatus($orderId, $status);

        flash('success', 'The order status has been updated.');

        redirect('/admin/orders/' . $orderId);
    }

    private function orderId(string $id): int
    {
        if (!ctype_digit($id)) {
            abort(404);
        }

        return (int) $id;
    }
}
