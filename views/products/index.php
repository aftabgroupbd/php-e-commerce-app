<?php
/**
 * Product catalogue.
 *
 * @var \App\Models\Product[] $products
 */
?>
<h2>All products <small><?= e((string) count($products)) ?> item(s)</small></h2>

<?php if ($products === []) : ?>
    <div class="empty-state">There are no products yet.</div>
<?php else : ?>
    <div class="row product-grid">
        <?php foreach ($products as $product) : ?>
            <div class="col-sm-6 col-md-3">
                <?php require __DIR__ . '/_card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
