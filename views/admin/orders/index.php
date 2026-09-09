<?php
/**
 * Admin order list.
 *
 * @var \App\Models\Order[] $orders
 */
?>
<h2>Orders</h2>

<div class="panel panel-default">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Placed</th>
            <th>Status</th>
            <th class="text-right">Total</th>
            <th class="text-right"></th>
        </tr>
        </thead>
        <tbody>
        <?php if ($orders === []) : ?>
            <tr><td colspan="6" class="text-center text-muted">No orders yet.</td></tr>
        <?php endif; ?>

        <?php foreach ($orders as $order) : ?>
            <tr>
                <td><?= e((string) $order->id) ?></td>
                <td>
                    <?= e($order->customerName ?? '-') ?><br>
                    <small class="text-muted"><?= e($order->customerEmail ?? '') ?></small>
                </td>
                <td><?= e($order->createdAt ?? '') ?></td>
                <td><span class="label label-<?= e($order->statusColour()) ?>"><?= e($order->status) ?></span></td>
                <td class="text-right"><?= e($order->formattedTotal()) ?></td>
                <td class="text-right">
                    <a class="btn btn-default btn-sm" href="<?= e(url('/admin/orders/' . $order->id)) ?>">
                        Details
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
