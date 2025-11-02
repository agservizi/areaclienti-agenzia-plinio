<?php
declare(strict_types=1);

/**
 * @var array $service  service array containing: id, title, description, category, slug
 */
?>
<div class="col-12 col-md-6 col-xl-4 mb-4">
    <div class="card-service h-100 p-4 d-flex flex-column justify-content-between">
        <div>
            <div class="d-flex justify-content-between align-items-start mb-3">
                <span class="badge-category"><?= escape($service['category'] ?? 'Servizio') ?></span>
                <span class="text-muted small">ID #<?= escape((string) $service['id']) ?></span>
            </div>
            <h3 class="card-title h5 mb-2"><?= escape($service['title'] ?? '-') ?></h3>
            <p class="text-muted mb-0"><?= escape(mb_strimwidth((string) ($service['description'] ?? ''), 0, 140, '…')) ?></p>
        </div>
        <div class="mt-4 d-flex justify-content-between align-items-center">
            <a class="btn btn-outline-accent" href="/client/service-details.php?slug=<?= urlencode((string) ($service['slug'] ?? '')) ?>">Dettagli</a>
            <a class="btn btn-accent" href="/client/requests.php?service=<?= urlencode((string) ($service['slug'] ?? '')) ?>">Richiedi</a>
        </div>
    </div>
</div>
