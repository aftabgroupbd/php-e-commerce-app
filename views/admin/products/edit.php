<?php
/**
 * "Edit product" form.
 *
 * @var \App\Models\Product $product
 */
?>
<h2>Edit product</h2>

<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-body">
                <form method="post" action="<?= e(url('/admin/products/' . $product->id)) ?>"
                      enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <?php require __DIR__ . '/_form.php'; ?>

                    <button type="submit" class="btn btn-primary">Update product</button>
                    <a href="<?= e(url('/admin/products')) ?>" class="btn btn-link">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
