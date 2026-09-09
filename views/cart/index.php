<?php
/**
 * Shopping cart.
 *
 * @var \App\Models\CartItem[] $items
 * @var float                  $subtotal
 */
?>
<h2>Shopping cart</h2>

<?php if ($items === []) : ?>
    <div class="empty-state">
        <p>Your cart is empty.</p>
        <a class="btn btn-primary" href="<?= e(url('/products')) ?>">Browse products</a>
    </div>
<?php else : ?>
    <div class="panel panel-default">
        <table class="table">
            <thead>
            <tr>
                <th colspan="2">Product</th>
                <th class="text-right">Price</th>
                <th style="width:190px;">Quantity</th>
                <th class="text-right">Subtotal</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item) : ?>
                <tr>
                    <td style="width:80px;">
                        <img class="cart-thumb" src="<?= e($item->product->imageUrl()) ?>"
                             alt="<?= e($item->product->name) ?>">
                    </td>
                    <td>
                        <a href="<?= e(url('/products/' . $item->product->id)) ?>">
                            <?= e($item->product->name) ?>
                        </a>
                    </td>
                    <td class="text-right"><?= e($item->product->formattedPrice()) ?></td>
                    <td>
                        <form method="post" action="<?= e(url('/cart/update')) ?>" class="form-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= e((string) $item->product->id) ?>">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" name="quantity"
                                       value="<?= e((string) $item->quantity) ?>"
                                       min="1" max="<?= e((string) $item->product->stock) ?>">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit">Update</button>
                                </span>
                            </div>
                        </form>
                    </td>
                    <td class="text-right"><strong><?= e($item->formattedSubtotal()) ?></strong></td>
                    <td class="text-right">
                        <form method="post" action="<?= e(url('/cart/remove')) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= e((string) $item->product->id) ?>">
                            <button type="submit" class="btn btn-link text-danger" title="Remove">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total</th>
                <th class="text-right"><?= e(format_price($subtotal)) ?></th>
                <th></th>
            </tr>
            </tfoot>
        </table>
    </div>

    <div class="clearfix">
        <form method="post" action="<?= e(url('/cart/clear')) ?>" class="pull-left">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-default">Empty cart</button>
        </form>

        <a href="<?= e(url('/checkout')) ?>" class="btn btn-success pull-right">
            Proceed to checkout <i class="fa fa-arrow-right"></i>
        </a>
    </div>
<?php endif; ?>
