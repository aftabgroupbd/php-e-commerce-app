<?php

declare(strict_types=1);

namespace App\Support;

use App\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\OrderController as AdminOrderController;
use App\Controllers\Admin\ProductController as AdminProductController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\CustomerController;
use App\Controllers\ProductController;
use App\Database\Database;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\AuthService;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\FileUploadService;
use App\Services\ProductService;
use PDO;
use RuntimeException;

/**
 * A very small dependency injection container.
 *
 * "Dependency injection" simply means: a class does not create the objects it
 * needs, it receives them through its constructor. This container is the one
 * place where all those objects are wired together.
 *
 * There is no magic and no reflection on purpose - you can read this file top
 * to bottom and see exactly what depends on what.
 */
final class Container
{
    /** @var array<string, object> */
    private array $shared = [];

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public function make(string $class): object
    {
        if (isset($this->shared[$class])) {
            /** @var T */
            return $this->shared[$class];
        }

        $instance = $this->build($class);
        $this->shared[$class] = $instance;

        /** @var T */
        return $instance;
    }

    private function build(string $class): object
    {
        return match ($class) {
            // ---------------------------------------------------------- database
            Database::class            => new Database(config('database')),

            // ------------------------------------------------------ repositories
            CustomerRepository::class  => new CustomerRepository($this->pdo()),
            ProductRepository::class   => new ProductRepository($this->pdo()),
            OrderRepository::class     => new OrderRepository($this->pdo()),
            OrderItemRepository::class => new OrderItemRepository($this->pdo()),

            // ----------------------------------------------------------- services
            FileUploadService::class   => new FileUploadService(
                config('app.upload_path')
            ),
            AuthService::class         => new AuthService(
                $this->make(CustomerRepository::class)
            ),
            ProductService::class      => new ProductService(
                $this->make(ProductRepository::class),
                $this->make(FileUploadService::class)
            ),
            CartService::class         => new CartService(
                $this->make(ProductRepository::class)
            ),
            CheckoutService::class     => new CheckoutService(
                $this->pdo(),
                $this->make(ProductRepository::class),
                $this->make(OrderRepository::class),
                $this->make(OrderItemRepository::class),
                $this->make(CartService::class)
            ),

            // -------------------------------------------------------- controllers
            AuthController::class      => new AuthController(
                $this->make(AuthService::class),
                $this->make(CustomerRepository::class)
            ),
            ProductController::class   => new ProductController(
                $this->make(ProductService::class)
            ),
            CustomerController::class  => new CustomerController(
                $this->make(AuthService::class),
                $this->make(OrderRepository::class),
                $this->make(OrderItemRepository::class)
            ),
            CartController::class      => new CartController(
                $this->make(CartService::class)
            ),
            CheckoutController::class  => new CheckoutController(
                $this->make(CartService::class),
                $this->make(CheckoutService::class)
            ),

            AdminDashboardController::class => new AdminDashboardController(
                $this->make(ProductRepository::class),
                $this->make(CustomerRepository::class),
                $this->make(OrderRepository::class)
            ),
            AdminProductController::class   => new AdminProductController(
                $this->make(ProductService::class)
            ),
            AdminCustomerController::class  => new AdminCustomerController(
                $this->make(CustomerRepository::class)
            ),
            AdminOrderController::class     => new AdminOrderController(
                $this->make(OrderRepository::class),
                $this->make(OrderItemRepository::class)
            ),

            default => throw new RuntimeException("Container does not know how to build [{$class}]."),
        };
    }

    private function pdo(): PDO
    {
        return $this->make(Database::class)->connection();
    }
}
