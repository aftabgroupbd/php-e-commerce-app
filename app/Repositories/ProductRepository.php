<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use PDO;

/**
 * All SQL for the "products" table.
 */
final class ProductRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return Product[]
     */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM products ORDER BY id DESC');

        return $this->hydrateMany($statement->fetchAll());
    }

    /**
     * @return Product[]
     */
    public function latest(int $limit): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM products ORDER BY id DESC LIMIT :limit');
        // LIMIT cannot be a string parameter, so it is bound as an integer.
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $this->hydrateMany($statement->fetchAll());
    }

    public function findById(int $id): ?Product
    {
        $statement = $this->pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row ? Product::fromArray($row) : null;
    }

    /**
     * Load several products at once (used by the cart and by checkout).
     *
     * @param int[] $ids
     * @return array<int, Product> keyed by product id
     */
    public function findManyByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids, static fn ($id): bool => $id > 0)));

        if ($ids === []) {
            return [];
        }

        // Build "?, ?, ?" so the ids stay bound parameters instead of raw SQL.
        $placeholders = implode(', ', array_fill(0, count($ids), '?'));

        $statement = $this->pdo->prepare(
            "SELECT * FROM products WHERE id IN ({$placeholders})"
        );
        $statement->execute($ids);

        $products = [];

        foreach ($statement->fetchAll() as $row) {
            $product = Product::fromArray($row);
            $products[$product->id] = $product;
        }

        return $products;
    }

    /**
     * Same as findManyByIds() but locks the rows for the running transaction,
     * so two people cannot buy the last item at the same time.
     *
     * @param int[] $ids
     * @return array<int, Product>
     */
    public function findManyByIdsForUpdate(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids, static fn ($id): bool => $id > 0)));

        if ($ids === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));

        $statement = $this->pdo->prepare(
            "SELECT * FROM products WHERE id IN ({$placeholders}) ORDER BY id FOR UPDATE"
        );
        $statement->execute($ids);

        $products = [];

        foreach ($statement->fetchAll() as $row) {
            $product = Product::fromArray($row);
            $products[$product->id] = $product;
        }

        return $products;
    }

    /**
     * @param array{name:string, description:?string, price:float, stock:int, image:?string} $data
     */
    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO products (name, description, price, stock, image)
             VALUES (:name, :description, :price, :stock, :image)'
        );

        $statement->execute([
            'name'        => $data['name'],
            'description' => $data['description'],
            'price'       => $data['price'],
            'stock'       => $data['stock'],
            'image'       => $data['image'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array{name:string, description:?string, price:float, stock:int, image:?string} $data
     */
    public function update(int $id, array $data): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE products
                SET name = :name,
                    description = :description,
                    price = :price,
                    stock = :stock,
                    image = :image
              WHERE id = :id'
        );

        return $statement->execute([
            'id'          => $id,
            'name'        => $data['name'],
            'description' => $data['description'],
            'price'       => $data['price'],
            'stock'       => $data['stock'],
            'image'       => $data['image'],
        ]);
    }

    /**
     * Lower the stock, but only while enough items are left.
     * The "AND stock >= ..." makes this safe even under concurrency.
     *
     * The quantity is bound twice under two different names: with real
     * prepared statements (ATTR_EMULATE_PREPARES = false) the same named
     * placeholder may not be reused inside one query.
     */
    public function decreaseStock(int $id, int $quantity): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE products SET stock = stock - :quantity
              WHERE id = :id AND stock >= :required_stock'
        );

        $statement->execute([
            'id'             => $id,
            'quantity'       => $quantity,
            'required_stock' => $quantity,
        ]);

        return $statement->rowCount() === 1;
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return Product[]
     */
    private function hydrateMany(array $rows): array
    {
        return array_map(
            static fn (array $row): Product => Product::fromArray($row),
            $rows
        );
    }
}
