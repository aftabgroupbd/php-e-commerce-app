<?php

declare(strict_types=1);

/**
 * All URLs of the application in one place.
 *
 * Read it as: METHOD  path  ->  Controller@method  (protected by middleware)
 *
 * Rule of thumb used here:
 *   GET  = show something, never changes data
 *   POST = changes data, therefore always protected by a CSRF token
 */

use App\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\OrderController as AdminOrderController;
use App\Controllers\Admin\ProductController as AdminProductController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\CustomerController;
use App\Controllers\ProductController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Support\Router;

$router = new Router();

// ---------------------------------------------------------------- public shop
$router->get('/', [ProductController::class, 'home']);
$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/{id}', [ProductController::class, 'show']);

// ------------------------------------------------------------ authentication
$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);
$router->get('/register', [AuthController::class, 'showRegister'], [GuestMiddleware::class]);
$router->post('/register', [AuthController::class, 'register'], [GuestMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

// ------------------------------------------------------------------- customer
$router->get('/account', [CustomerController::class, 'account'], [AuthMiddleware::class]);
$router->get('/orders', [CustomerController::class, 'orders'], [AuthMiddleware::class]);
$router->post('/account/delete', [CustomerController::class, 'destroy'], [AuthMiddleware::class]);

// ----------------------------------------------------------------------- cart
// The cart lives in the session, so a guest may use it too.
$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);
$router->post('/cart/clear', [CartController::class, 'clear']);

// ------------------------------------------------------------------- checkout
$router->get('/checkout', [CheckoutController::class, 'index'], [AuthMiddleware::class]);
$router->post('/checkout', [CheckoutController::class, 'store'], [AuthMiddleware::class]);

// ------------------------------------------------------- admin (role = admin)
$router->get('/admin', [AdminDashboardController::class, 'index'], [AdminMiddleware::class]);

$router->get('/admin/products', [AdminProductController::class, 'index'], [AdminMiddleware::class]);
$router->get('/admin/products/create', [AdminProductController::class, 'create'], [AdminMiddleware::class]);
$router->post('/admin/products', [AdminProductController::class, 'store'], [AdminMiddleware::class]);
$router->get('/admin/products/{id}/edit', [AdminProductController::class, 'edit'], [AdminMiddleware::class]);
$router->post('/admin/products/{id}', [AdminProductController::class, 'update'], [AdminMiddleware::class]);
$router->post('/admin/products/{id}/delete', [AdminProductController::class, 'destroy'], [AdminMiddleware::class]);

$router->get('/admin/customers', [AdminCustomerController::class, 'index'], [AdminMiddleware::class]);

$router->get('/admin/orders', [AdminOrderController::class, 'index'], [AdminMiddleware::class]);
$router->get('/admin/orders/{id}', [AdminOrderController::class, 'show'], [AdminMiddleware::class]);
$router->post('/admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus'], [AdminMiddleware::class]);

return $router;
