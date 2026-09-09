<?php
/**
 * Admin customer list.
 *
 * @var \App\Models\Customer[] $customers
 */
?>
<h2>Customers</h2>

<div class="panel panel-default">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Registered</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($customers === []) : ?>
            <tr><td colspan="5" class="text-center text-muted">No customers yet.</td></tr>
        <?php endif; ?>

        <?php foreach ($customers as $customer) : ?>
            <tr>
                <td><?= e((string) $customer->id) ?></td>
                <td><?= e($customer->name) ?></td>
                <td><?= e($customer->email) ?></td>
                <td>
                    <span class="label label-<?= $customer->isAdmin() ? 'warning' : 'default' ?>">
                        <?= e($customer->role) ?>
                    </span>
                </td>
                <td><?= e($customer->createdAt ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
