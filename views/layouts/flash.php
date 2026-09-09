<?php
/**
 * Shows the one-time status message set with flash('success', '...').
 */
$message = flash_message();
?>
<?php if ($message !== null) : ?>
    <div class="alert alert-<?= e($message['type']) ?> alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <?= e($message['text']) ?>
    </div>
<?php endif; ?>
