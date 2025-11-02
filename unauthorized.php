<?php
declare(strict_types=1);

$pageTitle = 'Non autorizzato';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5 text-center">
    <h1 class="display-5 mb-3">Accesso non autorizzato</h1>
    <p class="text-muted">Non disponi dei permessi necessari per accedere a questa sezione.</p>
    <a class="btn btn-outline-accent" href="/client/dashboard.php">Torna alla dashboard</a>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
