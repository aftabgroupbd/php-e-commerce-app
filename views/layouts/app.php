<?php
/**
 * Layout for the public shop.
 *
 * The controller calls view('products/index', [...]) - that template is
 * rendered first and arrives here as $content. This is why the <html>
 * boilerplate exists only once in the whole project.
 *
 * @var string $content
 */
?>
<?php require __DIR__ . '/header.php'; ?>
<?php require __DIR__ . '/navbar.php'; ?>

<main class="container page-content">
    <?php require __DIR__ . '/flash.php'; ?>
    <?= $content ?>
</main>

<?php require __DIR__ . '/footer.php'; ?>
