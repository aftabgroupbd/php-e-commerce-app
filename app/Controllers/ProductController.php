<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ProductService;
use App\Support\Request;

/**
 * The public product pages (home page, catalogue, product detail).
 */
final class ProductController
{
    public function __construct(private readonly ProductService $products)
    {
    }

    /**
     * Home page: a few featured products.
     */
    public function home(Request $request): void
    {
        view('products/home', [
            'title'    => 'Home',
            'products' => $this->products->latest(8),
        ]);
    }

    /**
     * The full catalogue.
     */
    public function index(Request $request): void
    {
        view('products/index', [
            'title'    => 'All products',
            'products' => $this->products->all(),
        ]);
    }

    /**
     * A single product. The {id} from the URL arrives as a string, so it is
     * validated before it ever reaches the database.
     */
    public function show(Request $request, string $id): void
    {
        if (!ctype_digit($id)) {
            abort(404);
        }

        view('products/show', [
            'title'   => 'Product',
            'product' => $this->products->findOrFail((int) $id),
        ]);
    }
}
