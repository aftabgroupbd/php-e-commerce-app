<?php
/**
 * One product card. Included by the home page and the catalogue.
 *
 * @var \App\Models\Product $product
 */
?>
<div class="product-card">
    <a href="<?= e(url('/products/' . $product->id)) ?>">
        <img class="product-thumb" src="<?= e($product->imageUrl()) ?>" alt="<?= e($product->name) ?>">
    </a>

    <div class="product-body">
        <h4 class="product-title">
            <a href="<?= e(url('/products/' . $product->id)) ?>"><?= e($product->name) ?></a>
        </h4>

        <div class="product-price"><?= e($product->formattedPrice()) ?></div>

        <div class="product-actions">
            <?php if ($product->isInStock()) : ?>
                <form method="post" action="<?= e(url('/cart/add')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= e((string) $product->id) ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa fa-shopping-cart"></i> Add to cart
                    </button>
                </form>
            <?php else : ?>
                <button class="btn btn-default btn-block" disabled>Out of stock</button>
            <?php endif; ?>
        </div>
    </div>
</div>
