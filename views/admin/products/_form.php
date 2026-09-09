<?php
/**
 * Shared fields of the "create product" and "edit product" forms.
 *
 * @var \App\Models\Product|null $product null when creating
 */
$product = $product ?? null;
?>
<div class="form-group <?= error_for('name') ? 'has-error' : '' ?>">
    <label for="name">Product name</label>
    <input type="text" class="form-control" id="name" name="name"
           value="<?= old('name', $product->name ?? '') ?>" required>
    <?php if ($message = error_for('name')) : ?>
        <span class="field-error"><?= e($message) ?></span>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group <?= error_for('price') ? 'has-error' : '' ?>">
            <label for="price">Price</label>
            <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price"
                   value="<?= old('price', $product !== null ? number_format($product->price, 2, '.', '') : '') ?>" required>
            <?php if ($message = error_for('price')) : ?>
                <span class="field-error"><?= e($message) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group <?= error_for('stock') ? 'has-error' : '' ?>">
            <label for="stock">Stock</label>
            <input type="number" step="1" min="0" class="form-control" id="stock" name="stock"
                   value="<?= old('stock', (string) ($product->stock ?? 0)) ?>" required>
            <?php if ($message = error_for('stock')) : ?>
                <span class="field-error"><?= e($message) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="form-group <?= error_for('description') ? 'has-error' : '' ?>">
    <label for="description">Description</label>
    <textarea class="form-control" id="description" name="description" rows="4"><?= old('description', $product->description ?? '') ?></textarea>
    <?php if ($message = error_for('description')) : ?>
        <span class="field-error"><?= e($message) ?></span>
    <?php endif; ?>
</div>

<div class="form-group <?= error_for('image') ? 'has-error' : '' ?>">
    <label for="image">Product image</label>

    <?php if ($product !== null) : ?>
        <div style="margin-bottom:8px;">
            <img src="<?= e($product->imageUrl()) ?>" alt="<?= e($product->name) ?>"
                 style="height:90px;border-radius:3px;">
        </div>
    <?php endif; ?>

    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
    <span class="help-block">
        JPG, PNG or WEBP, max 2 MB.
        <?= $product !== null ? 'Leave empty to keep the current image.' : '' ?>
    </span>

    <?php if ($message = error_for('image')) : ?>
        <span class="field-error"><?= e($message) ?></span>
    <?php endif; ?>
</div>
