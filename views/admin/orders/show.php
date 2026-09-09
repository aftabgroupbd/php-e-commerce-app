<?php
/**
 * A single order with its items and a status form.
 *
 * @var \App\Models\Order       $order
 * @var \App\Models\OrderItem[] $items
 */

use App\Models\Order;
?>
<h2>Order #<?= e((string) $order->id) ?></h2>

<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Items</h3></div>

            <table class="table" style="margin-bottom:0;">
                <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-right">Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Subtotal</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item) : ?>
                    <tr>
                        <td><?= e($item->productName) ?></td>
                        <td class="text-right"><?= e(format_price($item->price)) ?></td>
                        <td class="text-center"><?= e((string) $item->quantity) ?></td>
                        <td class="text-right"><?= e(format_price($item->subtotal)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="3" class="text-right">Total</th>
                    <th class="text-right"><?= e($order->formattedTotal()) ?></th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Customer &amp; shipping</h3></div>
            <div class="panel-body">
                <p>
                    <strong><?= e($order->customerName ?? '') ?></strong><br>
                    <span class="text-muted"><?= e($order->customerEmail ?? '') ?></span>
                </p>
                <p>
                    <?= e($order->shippingName) ?><br>
                    <?= e($order->shippingAddress) ?><br>
                    <?= e($order->shippingCity) ?><br>
                    <?= e($order->shippingPhone) ?>
                </p>
                <p class="text-muted">Placed on <?= e($order->createdAt ?? '') ?></p>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Status</h3></div>
            <div class="panel-body">
                <form method="post" action="<?= e(url('/admin/orders/' . $order->id . '/status')) ?>">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <select name="status" class="form-control">
                            <?php foreach (Order::statuses() as $status) : ?>
                                <option value="<?= e($status) ?>" <?= $status === $order->status ? 'selected' : '' ?>>
                                    <?= e(ucfirst($status)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Update status</button>
                </form>
            </div>
        </div>

        <a href="<?= e(url('/admin/orders')) ?>" class="btn btn-link">&larr; Back to orders</a>
    </div>
</div>
