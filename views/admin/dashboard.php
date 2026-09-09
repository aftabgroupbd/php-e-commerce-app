<?php
/**
 * Admin dashboard.
 *
 * @var int   $products
 * @var int   $customers
 * @var int   $orders
 * @var float $revenue
 */
?>
<h2>Dashboard</h2>

<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-value"><?= e((string) $products) ?></div>
            <div class="stat-label">Products</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-value"><?= e((string) $orders) ?></div>
            <div class="stat-label">Orders</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-value"><?= e((string) $customers) ?></div>
            <div class="stat-label">Customers</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-value"><?= e(format_price($revenue)) ?></div>
            <div class="stat-label">Revenue</div>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">Quick actions</h3></div>
    <div class="panel-body">
        <a class="btn btn-primary" href="<?= e(url('/admin/products/create')) ?>">
            <i class="fa fa-plus"></i> Add a product
        </a>
        <a class="btn btn-default" href="<?= e(url('/admin/orders')) ?>">View orders</a>
        <a class="btn btn-default" href="<?= e(url('/admin/customers')) ?>">View customers</a>
    </div>
</div>
