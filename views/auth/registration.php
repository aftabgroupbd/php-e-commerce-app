<?php
/**
 * Registration form.
 */
?>
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Create an account</h3></div>

            <div class="panel-body">
                <form method="post" action="<?= e(url('/register')) ?>">
                    <?= csrf_field() ?>

                    <div class="form-group <?= error_for('name') ? 'has-error' : '' ?>">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= old('name') ?>" required autofocus>
                        <?php if ($message = error_for('name')) : ?>
                            <span class="field-error"><?= e($message) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group <?= error_for('email') ? 'has-error' : '' ?>">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= old('email') ?>" required>
                        <?php if ($message = error_for('email')) : ?>
                            <span class="field-error"><?= e($message) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group <?= error_for('password') ? 'has-error' : '' ?>">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                               minlength="8" required>
                        <span class="help-block">At least 8 characters.</span>
                        <?php if ($message = error_for('password')) : ?>
                            <span class="field-error"><?= e($message) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group <?= error_for('password_confirmation') ? 'has-error' : '' ?>">
                        <label for="password_confirmation">Repeat password</label>
                        <input type="password" class="form-control" id="password_confirmation"
                               name="password_confirmation" required>
                        <?php if ($message = error_for('password_confirmation')) : ?>
                            <span class="field-error"><?= e($message) ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Sign up</button>
                </form>
            </div>

            <div class="panel-footer text-center">
                Already registered? <a href="<?= e(url('/login')) ?>">Log in</a>
            </div>
        </div>
    </div>
</div>
