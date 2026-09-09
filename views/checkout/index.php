<?php
/**
 * Checkout page: shipping details + order summary.
 *
 * @var \App\Models\CartItem[]                          $items
 * @var float                                           $subtotal
 * @var array{id:int,name:string,email:string,role:string}|null $customer
 */
?>
<h2>Checkout</h2>

<form method="post" action="<?= e(url('/checkout')) ?>">
    <?= csrf_field() ?>

    <div class="row">
        <div class="col-md-7">
            <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">Shipping details</h3></div>

                <div class="panel-body">
                    <div class="form-group <?= error_for('full_name') ? 'has-error' : '' ?>">
                        <label for="full_name">Full name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name"
                               value="<?= old('full_name', $customer['name'] ?? '') ?>" required>
                        <?php if ($message = error_for('full_name')) : ?>
                            <span class="field-error"><?= e($message) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group <?= error_for('phone') ? 'has-error' : '' ?>">
                        <label for="phone">Phone number</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                               value="<?= old('phone') ?>" required>
                        <?php if ($message = error_for('phone')) : ?>
                            <span class="field-error"><?= e($message) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group <?= error_for('address') ? 'has-error' : '' ?>">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" id="address" name="address"
                               value="<?= old('address') ?>" required>
                        <?php if ($message = error_for('address')) : ?>
                            <span class="field-error"><?= e($message) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group <?= error_for('city') ? 'has-error' : '' ?>">
                        <label for="city">City</label>
                        <input type="text" class="form-control" id="city" name="city"
                               value="<?= old('city') ?>" required>
                        <?php if ($message = error_for('city')) : ?>
                            <span class="field-error"><?= e($message) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">Your order</h3></div>

                <table class="table" style="margin-bottom:0;">
                    <tbody>
                    <?php foreach ($items as $item) : ?>
                        <tr>
                            <td>
                                <?= e($item->product->name) ?>
                                <span class="text-muted">&times; <?= e((string) $item->quantity) ?></span>
                            </td>
                            <td class="text-right"><?= e($item->formattedSubtotal()) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>Total</th>
                        <th class="text-right"><?= e(format_price($subtotal)) ?></th>
                    </tr>
                    </tfoot>
                </table>

                <div class="panel-footer">
                    <!--
                        The total shown here is only for the customer's eyes.
                        CheckoutService recalculates it from the database, so a
                        manipulated form can never change what is charged.
                    -->
                    <button type="submit" class="btn btn-success btn-block">Place order</button>
                    <a href="<?= e(url('/cart')) ?>" class="btn btn-link btn-block">Back to cart</a>
                </div>
            </div>
        </div>
    </div>
</form>
