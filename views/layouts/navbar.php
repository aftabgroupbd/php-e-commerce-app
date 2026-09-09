<?php
/**
 * Navigation for the public shop.
 *
 * The menu changes depending on who is looking at it:
 * guest / customer / admin.
 */
?>
<nav class="navbar navbar-inverse navbar-static-top shop-navbar">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-nav">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?= e(url('/')) ?>">
                <?= e(config('app.name')) ?>
            </a>
        </div>

        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="nav navbar-nav">
                <li><a href="<?= e(url('/')) ?>">Home</a></li>
                <li><a href="<?= e(url('/products')) ?>">Products</a></li>
            </ul>

            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="<?= e(url('/cart')) ?>">
                        <i class="fa fa-shopping-cart"></i> Cart
                        <?php $count = cart_count(); ?>
                        <?php if ($count > 0) : ?>
                            <span class="badge"><?= e((string) $count) ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <?php if (is_authenticated()) : ?>
                    <?php if (is_admin()) : ?>
                        <li><a href="<?= e(url('/admin')) ?>"><i class="fa fa-cogs"></i> Admin</a></li>
                    <?php endif; ?>
                    <li><a href="<?= e(url('/orders')) ?>">My orders</a></li>
                    <li><a href="<?= e(url('/account')) ?>">
                        <i class="fa fa-user"></i> <?= e(current_user()['name']) ?>
                    </a></li>
                    <li>
                        <!-- Logging out changes state, so it is a POST form with a CSRF token. -->
                        <form method="post" action="<?= e(url('/logout')) ?>" class="navbar-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-link navbar-link-button">Logout</button>
                        </form>
                    </li>
                <?php else : ?>
                    <li><a href="<?= e(url('/login')) ?>">Login</a></li>
                    <li><a href="<?= e(url('/register')) ?>">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
