<?php
/**
 * Layout for the admin panel.
 *
 * @var string $content
 */
?>
<?php require __DIR__ . '/header.php'; ?>

<nav class="navbar navbar-default navbar-static-top admin-navbar">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="<?= e(url('/admin')) ?>">
                <i class="fa fa-dashboard"></i> Admin panel
            </a>
        </div>

        <ul class="nav navbar-nav">
            <li><a href="<?= e(url('/admin/products')) ?>"><i class="fa fa-cube"></i> Products</a></li>
            <li><a href="<?= e(url('/admin/orders')) ?>"><i class="fa fa-shopping-basket"></i> Orders</a></li>
            <li><a href="<?= e(url('/admin/customers')) ?>"><i class="fa fa-users"></i> Customers</a></li>
        </ul>

        <ul class="nav navbar-nav navbar-right">
            <li><a href="<?= e(url('/')) ?>"><i class="fa fa-external-link"></i> View shop</a></li>
            <li>
                <form method="post" action="<?= e(url('/logout')) ?>" class="navbar-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-link navbar-link-button">
                        Logout (<?= e(current_user()['name'] ?? '') ?>)
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>

<main class="container-fluid page-content">
    <?php require __DIR__ . '/flash.php'; ?>
    <?= $content ?>
</main>

<?php require __DIR__ . '/footer.php'; ?>
