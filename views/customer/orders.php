<?php
/**
 * Order history of the logged in customer.
 *
 * @var \App\Models\Order[]                 $orders
 * @var array<int, \App\Models\OrderItem[]> $items order id => items
 */
?>
<h2>My orders</h2>

<?php if ($orders === []) : ?>
    <div class="empty-state">
        <p>You have not placed an order yet.</p>
        <a class="btn btn-primary" href="<?= e(url('/products')) ?>">Start shopping</a>
    </div>
<?php else : ?>
    <?php foreach ($orders as $order) : ?>
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <strong>Order #<?= e((string) $order->id) ?></strong>
                <span class="text-muted">&middot; <?= e($order->createdAt ?? '') ?></span>
                <span class="pull-right">
                    <span class="label label-<?= e($order->statusColour()) ?>"><?= e($order->status) ?></span>
                    <strong><?= e($order->formattedTotal()) ?></strong>
                </span>
            </div>

            <table class="table table-striped" style="margin-bottom:0;">
                <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-right">Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Subtotal</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items[$order->id] ?? [] as $item) : ?>
                    <tr>
                        <td><?= e($item->productName) ?></td>
                        <td class="text-right"><?= e(format_price($item->price)) ?></td>
                        <td class="text-center"><?= e((string) $item->quantity) ?></td>
                        <td class="text-right"><?= e(format_price($item->subtotal)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="panel-footer">
                Shipping to: <?= e($order->shippingName) ?>,
                <?= e($order->shippingAddress) ?>, <?= e($order->shippingCity) ?>
                (<?= e($order->shippingPhone) ?>)
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
