<?php
/**
 * Admin product list.
 *
 * @var \App\Models\Product[] $products
 */
?>
<div class="clearfix" style="margin-bottom:15px;">
    <h2 class="pull-left" style="margin-top:0;">Products</h2>
    <a class="btn btn-primary pull-right" href="<?= e(url('/admin/products/create')) ?>">
        <i class="fa fa-plus"></i> Add product
    </a>
</div>

<div class="panel panel-default">
    <table class="table table-striped">
        <thead>
        <tr>
            <th style="width:90px;">Image</th>
            <th>#</th>
            <th>Name</th>
            <th class="text-right">Price</th>
            <th class="text-center">Stock</th>
            <th class="text-right" style="width:170px;">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($products === []) : ?>
            <tr><td colspan="6" class="text-center text-muted">No products yet.</td></tr>
        <?php endif; ?>

        <?php foreach ($products as $product) : ?>
            <tr>
                <td><img class="cart-thumb" src="<?= e($product->imageUrl()) ?>" alt="<?= e($product->name) ?>"></td>
                <td><?= e((string) $product->id) ?></td>
                <td><?= e($product->name) ?></td>
                <td class="text-right"><?= e($product->formattedPrice()) ?></td>
                <td class="text-center">
                    <?php if ($product->isInStock()) : ?>
                        <?= e((string) $product->stock) ?>
                    <?php else : ?>
                        <span class="label label-danger">0</span>
                    <?php endif; ?>
                </td>
                <td class="text-right">
                    <a class="btn btn-info btn-sm" href="<?= e(url('/admin/products/' . $product->id . '/edit')) ?>">
                        <i class="fa fa-pencil"></i> Edit
                    </a>

                    <!-- Deleting is a POST form, never a link. -->
                    <form method="post" action="<?= e(url('/admin/products/' . $product->id . '/delete')) ?>"
                          style="display:inline;"
                          onsubmit="return confirm('Delete this product?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
