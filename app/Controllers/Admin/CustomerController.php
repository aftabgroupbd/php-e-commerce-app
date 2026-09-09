<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\CustomerRepository;
use App\Support\Request;

/**
 * The customer list for administrators.
 *
 * This page was an empty file in the old project.
 */
final class CustomerController
{
    public function __construct(private readonly CustomerRepository $customers)
    {
    }

    public function index(Request $request): void
    {
        view('admin/customers/index', [
            'title'     => 'Customers',
            'customers' => $this->customers->all(),
        ], 'layouts/admin');
    }
}
