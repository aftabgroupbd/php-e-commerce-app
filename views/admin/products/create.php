<?php
/**
 * "Add product" form.
 */
?>
<h2>Add product</h2>

<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-body">
                <!-- enctype is required, otherwise the file never reaches PHP. -->
                <form method="post" action="<?= e(url('/admin/products')) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <?php require __DIR__ . '/_form.php'; ?>

                    <button type="submit" class="btn btn-primary">Save product</button>
                    <a href="<?= e(url('/admin/products')) ?>" class="btn btn-link">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
