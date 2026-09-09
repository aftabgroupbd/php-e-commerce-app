<?php
/**
 * The page every uncaught error ends up on (see App\Support\ErrorHandler).
 *
 * @var int         $status
 * @var string      $message
 * @var string|null $trace only filled in development
 */
?>
<div class="row">
    <div class="col-md-8 col-md-offset-2 text-center">
        <h1 style="font-size:64px;margin-bottom:0;"><?= e((string) $status) ?></h1>
        <p class="lead"><?= e($message) ?></p>
        <p><a class="btn btn-primary" href="<?= e(url('/')) ?>">Back to the shop</a></p>
    </div>
</div>

<?php if ($trace !== null) : ?>
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-danger">
                <div class="panel-heading">Stack trace (only shown when APP_ENV=development)</div>
                <pre style="margin:0;font-size:12px;"><?= e($trace) ?></pre>
            </div>
        </div>
    </div>
<?php endif; ?>
