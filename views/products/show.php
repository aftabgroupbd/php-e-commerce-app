<?php
/**
 * Product detail page.
 *
 * @var \App\Models\Product $product
 */
?>
<ol class="breadcrumb">
    <li><a href="<?= e(url('/')) ?>">Home</a></li>
    <li><a href="<?= e(url('/products')) ?>">Products</a></li>
    <li class="active"><?= e($product->name) ?></li>
</ol>

<div class="row">
    <div class="col-md-6">
        <img class="product-detail-image" src="<?= e($product->imageUrl()) ?>" alt="<?= e($product->name) ?>">
    </div>

    <div class="col-md-6">
        <h1><?= e($product->name) ?></h1>

        <p class="product-price" style="font-size:26px;"><?= e($product->formattedPrice()) ?></p>

        <p>
            <?php if ($product->isInStock()) : ?>
                <span class="label label-success"><?= e((string) $product->stock) ?> in stock</span>
            <?php else : ?>
                <span class="label label-danger">Out of stock</span>
            <?php endif; ?>
        </p>

        <?php if ($product->description !== null) : ?>
            <p><?= nl2br(e($product->description)) ?></p>
        <?php endif; ?>

        <?php if ($product->isInStock()) : ?>
            <form method="post" action="<?= e(url('/cart/add')) ?>" class="form-inline" style="margin-top:20px;">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= e((string) $product->id) ?>">

                <div class="form-group">
                    <label class="sr-only" for="quantity">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity"
                           value="1" min="1" max="<?= e((string) $product->stock) ?>">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-shopping-cart"></i> Add to cart
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
