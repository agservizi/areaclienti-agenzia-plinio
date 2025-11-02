<?php
declare(strict_types=1);

/**
 * Modal confirm component.
 * @var string $modalId
 * @var string $title
 * @var string $body
 * @var string $confirmLabel
 */
?>
<div class="modal fade" id="<?= escape($modalId) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-surface">
            <div class="modal-header">
                <h5 class="modal-title"><?= escape($title) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= $body ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-accent" data-bs-dismiss="modal">Annulla</button>
                <button type="submit" class="btn btn-accent"><?= escape($confirmLabel) ?></button>
            </div>
        </div>
    </div>
</div>
