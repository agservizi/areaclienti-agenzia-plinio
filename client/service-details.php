<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login(ROLE_CLIENT);

$slug = sanitize_text($_GET['slug'] ?? '');
$services = db()->query('SELECT id, slug, title, description, category FROM services WHERE enabled = 1 ORDER BY category, title')->fetchAll();
$selected = null;
foreach ($services as $service) {
    if ($service['slug'] === $slug) {
        $selected = $service;
        break;
    }
}

$pageTitle = 'Catalogo servizi';
$breadcrumbs = ['Servizi' => null];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav-client.php';
?>
<div class="container">
    <div class="row g-5">
        <div class="col-lg-4">
            <div class="card-service p-3">
                <h2 class="h5 mb-3">Catalogo</h2>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($services as $service): ?>
                        <li class="mb-2">
                            <a href="?slug=<?= urlencode($service['slug']) ?>" class="d-flex justify-content-between align-items-center<?= $service['slug'] === $slug ? ' text-accent fw-semibold' : '' ?>">
                                <span><?= escape($service['title']) ?></span>
                                <span class="badge badge-soft-accent"><?= escape($service['category']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="col-lg-8">
            <?php if ($selected): ?>
                <div class="card-service p-4">
                    <h2 class="h4 mb-3"><?= escape($selected['title']) ?></h2>
                    <p class="text-muted">Categoria: <?= escape($selected['category']) ?></p>
                    <p><?= nl2br(escape($selected['description'] ?? '')) ?></p>
                    <hr>
                    <p class="text-muted">Per avviare una pratica, compila il modulo nella sezione "Nuova richiesta".</p>
                    <a class="btn btn-accent" href="/client/requests.php?service=<?= urlencode($selected['slug']) ?>">Avvia richiesta</a>
                </div>
            <?php else: ?>
                <div class="placeholder-card">
                    Seleziona un servizio per visualizzare i dettagli.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
