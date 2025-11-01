<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
$user = current_user();
?>
<div class="container mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/client/dashboard.php">Dashboard</a></li>
            <?php if (!empty($breadcrumbs) && is_array($breadcrumbs)): ?>
                <?php foreach ($breadcrumbs as $label => $url): ?>
                    <?php if ($url): ?>
                        <li class="breadcrumb-item"><a href="<?= escape($url) ?>"><?php echo escape($label); ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo escape($label); ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="h3 mb-1">Benvenuto, <?= escape($user['name'] ?? $user['email'] ?? 'Cliente') ?></h1>
            <p class="text-muted mb-0">Gestisci i tuoi servizi e le richieste in un’unica area personale.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-light" href="/client/profile.php">Profilo</a>
            <a class="btn btn-accent" href="/client/requests.php">Nuova richiesta</a>
        </div>
    </div>
</div>
