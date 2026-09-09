<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\CustomerRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Support\Request;

/**
 * Admin home page with a few counters.
 */
final class DashboardController
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly CustomerRepository $customers,
        private readonly OrderRepository $orders,
    ) {
    }

    public function index(Request $request): void
    {
        view('admin/dashboard', [
            'title'     => 'Dashboard',
            'products'  => $this->products->count(),
            'customers' => $this->customers->count(),
            'orders'    => $this->orders->count(),
            'revenue'   => $this->orders->totalRevenue(),
        ], 'layouts/admin');
    }
}
