<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\ProductService;
use App\Support\DomainException;
use App\Support\Request;
use App\Validation\ProductValidator;

/**
 * Product management (create / edit / delete) for administrators.
 *
 * Every route of this controller runs through AdminMiddleware first.
 */
final class ProductController
{
    private ProductValidator $validator;

    public function __construct(private readonly ProductService $products)
    {
        $this->validator = new ProductValidator();
    }

    public function index(Request $request): void
    {
        view('admin/products/index', [
            'title'    => 'Products',
            'products' => $this->products->all(),
        ], 'layouts/admin');
    }

    public function create(Request $request): void
    {
        view('admin/products/create', [
            'title' => 'Add product',
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        require_csrf($request->input('_token'));

        $input  = $request->only(['name', 'description', 'price', 'stock']);
        $errors = $this->validator->validate($input);
        $image  = $request->file('image');

        // The image is required when a product is created.
        if ($image === null) {
            $errors['image'] = 'Please choose a product image.';
        } elseif ($imageError = $this->imageError($image)) {
            $errors['image'] = $imageError;
        }

        if ($errors !== []) {
            redirect_with_errors('/admin/products/create', $errors, $input);
        }

        $this->products->create($this->toProductData($input), $image);

        flash('success', 'The product has been created.');

        redirect('/admin/products');
    }

    public function edit(Request $request, string $id): void
    {
        view('admin/products/edit', [
            'title'   => 'Edit product',
            'product' => $this->products->findOrFail($this->productId($id)),
        ], 'layouts/admin');
    }

    public function update(Request $request, string $id): void
    {
        require_csrf($request->input('_token'));

        $productId = $this->productId($id);
        $input     = $request->only(['name', 'description', 'price', 'stock']);
        $errors    = $this->validator->validate($input);
        $image     = $request->file('image');

        // On update the image is optional: no file means "keep the current one".
        if ($image !== null && ($imageError = $this->imageError($image))) {
            $errors['image'] = $imageError;
        }

        if ($errors !== []) {
            redirect_with_errors('/admin/products/' . $productId . '/edit', $errors, $input);
        }

        $this->products->update($productId, $this->toProductData($input), $image);

        flash('success', 'The product has been updated.');

        redirect('/admin/products');
    }

    /**
     * Deleting happens through a POST form, never through a link. A link can
     * be triggered by a crawler, a prefetching browser or another website.
     */
    public function destroy(Request $request, string $id): void
    {
        require_csrf($request->input('_token'));

        try {
            $this->products->delete($this->productId($id));
            flash('success', 'The product has been deleted.');
        } catch (DomainException $e) {
            flash('danger', $e->getMessage());
        }

        redirect('/admin/products');
    }

    /**
     * @param array<string, string|null> $input
     * @return array{name:string, description:?string, price:float, stock:int}
     */
    private function toProductData(array $input): array
    {
        $description = $input['description'] ?? '';

        return [
            'name'        => (string) $input['name'],
            'description' => $description === '' ? null : $description,
            'price'       => round((float) $input['price'], 2),
            'stock'       => (int) ($input['stock'] ?? 0),
        ];
    }

    /**
     * @param array<string, mixed> $image
     */
    private function imageError(array $image): ?string
    {
        return $this->products->validateImage($image);
    }

    private function productId(string $id): int
    {
        if (!ctype_digit($id)) {
            abort(404);
        }

        return (int) $id;
    }
}
