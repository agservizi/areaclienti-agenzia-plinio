<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login(ROLE_CLIENT);

$pageTitle = 'Dashboard Cliente';
$breadcrumbs = ['Dashboard' => null];

$stmt = db()->prepare('SELECT r.*, s.title AS service_title FROM requests r JOIN services s ON r.service_id = s.id WHERE r.user_id = :user ORDER BY r.created_at DESC LIMIT 6');
$stmt->execute(['user' => $_SESSION['auth_user_id']]);
$recentRequests = $stmt->fetchAll();

$stmtServices = db()->query('SELECT id, slug, title, description, category FROM services WHERE enabled = 1 ORDER BY created_at DESC LIMIT 6');
$topServices = $stmtServices->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav-client.php';
?>
<div class="container">
    <section class="mb-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <p class="text-muted mb-1">Richieste totali</p>
                    <h2 class="display-6">
                        <?php
                        $count = db()->prepare('SELECT COUNT(*) FROM requests WHERE user_id = :user');
                        $count->execute(['user' => $_SESSION['auth_user_id']]);
                        echo (int) $count->fetchColumn();
                        ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <p class="text-muted mb-1">Richieste in corso</p>
                    <h2 class="display-6">
                        <?php
                        $countPending = db()->prepare("SELECT COUNT(*) FROM requests WHERE user_id = :user AND status IN ('pending', 'processing')");
                        $countPending->execute(['user' => $_SESSION['auth_user_id']]);
                        echo (int) $countPending->fetchColumn();
                        ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <p class="text-muted mb-1">Ultimo accesso</p>
                    <h2 class="h5 mb-0"><?= escape(format_date(date('Y-m-d H:i:s', $_SESSION['auth_last_seen'] ?? time()))) ?></h2>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">Richieste recenti</h2>
            <a class="btn btn-outline-accent btn-sm" href="/client/requests.php">Visualizza tutte</a>
        </div>
        <?php $requests = $recentRequests; $isAdmin = false; include __DIR__ . '/../templates/partials/request-table.php'; ?>
    </section>

    <section>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">Servizi consigliati</h2>
            <a class="btn btn-outline-accent btn-sm" href="/client/service-details.php">Esplora catalogo</a>
        </div>
        <div class="row">
            <?php foreach ($topServices as $service): ?>
                <?php include __DIR__ . '/../templates/partials/service-card.php'; ?>
            <?php endforeach; ?>
            <?php if (!$topServices): ?>
                <p class="text-muted">Nessun servizio disponibile al momento.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
