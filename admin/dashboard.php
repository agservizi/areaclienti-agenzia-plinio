<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login(ROLE_ADMIN);

$pageTitle = 'Dashboard Admin';

$stats = [
    'pending_requests' => (int) db()->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn(),
    'requests_last_7' => (int) db()->query("SELECT COUNT(*) FROM requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
    'requests_last_30' => (int) db()->query("SELECT COUNT(*) FROM requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
    'users_total' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
];

$recentRequests = db()->query('SELECT r.id, r.status, r.created_at, s.title AS service_title, u.email AS user_email FROM requests r JOIN services s ON r.service_id = s.id JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC LIMIT 10')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav-admin.php';
?>
<h1 class="h3 mb-4">Riepilogo generale</h1>
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="stat-card">
            <p class="text-muted mb-1">Richieste pendenti</p>
            <h2 class="display-6 mb-0"><?= $stats['pending_requests'] ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <p class="text-muted mb-1">Richieste 7 giorni</p>
            <h2 class="display-6 mb-0"><?= $stats['requests_last_7'] ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <p class="text-muted mb-1">Richieste 30 giorni</p>
            <h2 class="display-6 mb-0"><?= $stats['requests_last_30'] ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <p class="text-muted mb-1">Utenti registrati</p>
            <h2 class="display-6 mb-0"><?= $stats['users_total'] ?></h2>
        </div>
    </div>
</div>

<h2 class="h4 mb-3">Richieste recenti</h2>
<?php $requests = $recentRequests; $isAdmin = true; include __DIR__ . '/../templates/partials/request-table.php'; ?>
<?php require_once __DIR__ . '/../includes/nav-admin-end.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
