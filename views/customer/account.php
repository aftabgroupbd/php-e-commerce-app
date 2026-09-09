<?php
/**
 * "My account" page.
 *
 * @var \App\Models\Customer $customer
 * @var \App\Models\Order[]  $orders
 */
?>
<h2>My account</h2>

<div class="row">
    <div class="col-md-7">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Details</h3></div>

            <table class="table">
                <tbody>
                <tr>
                    <th style="width:35%;">Name</th>
                    <td><?= e($customer->name) ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= e($customer->email) ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td><span class="label label-default"><?= e($customer->role) ?></span></td>
                </tr>
                <tr>
                    <th>Member since</th>
                    <td><?= e($customer->createdAt ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Orders placed</th>
                    <td><?= e((string) count($orders)) ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-5">
        <div class="panel panel-danger">
            <div class="panel-heading"><h3 class="panel-title">Delete account</h3></div>

            <div class="panel-body">
                <p>This removes your account and your order history. It cannot be undone.</p>

                <!--
                    A POST form with a CSRF token. In the old project this was a
                    plain link, so any other website could delete your account
                    just by making your browser open a URL.
                -->
                <form method="post" action="<?= e(url('/account/delete')) ?>"
                      onsubmit="return confirm('Delete your account permanently?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">Delete my account</button>
                </form>
            </div>
        </div>
    </div>
</div>
