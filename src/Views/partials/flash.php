<?php
/** @var array $flashes */
if (empty($flashes)) {
    return;
}
?>
<?php foreach ($flashes as $type => $messages): ?>
    <?php foreach ($messages as $message): ?>
        <div class="alert alert-<?= htmlspecialchars($type) ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
