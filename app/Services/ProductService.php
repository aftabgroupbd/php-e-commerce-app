<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Support\DomainException;

/**
 * Business rules around products.
 *
 * The controller says "create this product", the service decides what that
 * means (store the image first, keep the old image when no new one is sent,
 * delete the image file when the product is removed).
 */
final class ProductService
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly FileUploadService $uploads,
    ) {
    }

    /**
     * @return Product[]
     */
    public function all(): array
    {
        return $this->products->all();
    }

    /**
     * @return Product[]
     */
    public function latest(int $limit = 8): array
    {
        return $this->products->latest($limit);
    }

    public function find(int $id): ?Product
    {
        return $this->products->findById($id);
    }

    /**
     * Same as find(), but stops the request with a 404 page when the product
     * does not exist.
     */
    public function findOrFail(int $id): Product
    {
        $product = $this->products->findById($id);

        if ($product === null) {
            abort(404, 'This product does not exist (any more).');
        }

        return $product;
    }

    /**
     * Check an uploaded image without storing it yet, so the controller can
     * show the error next to the form field.
     *
     * @param array<string, mixed> $image
     * @return string|null an error message, or null when the image is fine
     */
    public function validateImage(array $image): ?string
    {
        return $this->uploads->validate($image);
    }

    /**
     * @param array{name:string, description:?string, price:float, stock:int} $data
     * @param array<string, mixed>|null $image one entry of $_FILES
     */
    public function create(array $data, ?array $image): int
    {
        $filename = $image === null ? null : $this->uploads->store($image);

        return $this->products->create($data + ['image' => $filename]);
    }

    /**
     * Update a product. A new image replaces the old file; sending no image
     * keeps the current one.
     *
     * @param array{name:string, description:?string, price:float, stock:int} $data
     * @param array<string, mixed>|null $image
     */
    public function update(int $id, array $data, ?array $image): void
    {
        $product  = $this->findOrFail($id);
        $filename = $product->image;

        if ($image !== null) {
            $filename = $this->uploads->store($image);

            // Only remove the old file once the new one is safely stored.
            $this->uploads->delete($product->image);
        }

        $this->products->update($id, $data + ['image' => $filename]);
    }

    public function delete(int $id): void
    {
        $product = $this->findOrFail($id);

        if (!$this->products->delete($id)) {
            throw new DomainException('The product could not be deleted.');
        }

        $this->uploads->delete($product->image);
    }
}
