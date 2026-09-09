<?php
/**
 * Login form.
 *
 * old('email') refills the field after a failed attempt,
 * error_for('email') shows the message the validator produced.
 */
?>
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Customer login</h3></div>

            <div class="panel-body">
                <form method="post" action="<?= e(url('/login')) ?>">
                    <?= csrf_field() ?>

                    <div class="form-group <?= error_for('email') ? 'has-error' : '' ?>">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= old('email') ?>" required autofocus>
                        <?php if ($message = error_for('email')) : ?>
                            <span class="field-error"><?= e($message) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group <?= error_for('password') ? 'has-error' : '' ?>">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <?php if ($message = error_for('password')) : ?>
                            <span class="field-error"><?= e($message) ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Log in</button>
                </form>
            </div>

            <div class="panel-footer text-center">
                No account yet? <a href="<?= e(url('/register')) ?>">Create one</a>
            </div>
        </div>
    </div>
</div>
