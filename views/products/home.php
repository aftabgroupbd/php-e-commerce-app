<?php
/**
 * Home page.
 *
 * @var \App\Models\Product[] $products
 */
?>
<div class="jumbotron" style="background:#fff;border:1px solid #e3e6ea;">
    <h1>Welcome to <?= e(config('app.name')) ?></h1>
    <p>A small demo shop built with plain PHP, PDO and a layered architecture.</p>
    <p><a class="btn btn-primary btn-lg" href="<?= e(url('/products')) ?>">Browse all products</a></p>
</div>

<h2>Featured products</h2>

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
