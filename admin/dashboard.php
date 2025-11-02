<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$pageTitle = 'Dashboard';
$adminActive = 'dashboard';

$stats = [];

try {
    $stats['users'] = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $stats['services'] = (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    $stats['requests_pending'] = (int) $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn();
    $stats['tickets_open'] = (int) $pdo->query("SELECT COUNT(*) FROM tickets WHERE status <> 'closed'")->fetchColumn();
    $stats['spid_pending'] = (int) $pdo->query("SELECT COUNT(*) FROM spid_requests WHERE status = 'pending'")->fetchColumn();
    $stats['sim_processing'] = (int) $pdo->query("SELECT COUNT(*) FROM sim_orders WHERE status IN ('pending','processing')")->fetchColumn();
    $stats['shipments_transit'] = (int) $pdo->query("SELECT COUNT(*) FROM shipments WHERE status IN ('created','in_transit')")->fetchColumn();
    $stats['notifications_unread'] = (int) $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();
    $stats['coverage_checks'] = (int) $pdo->query('SELECT COUNT(*) FROM coverage_checks')->fetchColumn();
    $stats['audit_today'] = (int) $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE DATE(created_at) = CURRENT_DATE()")->fetchColumn();
    $stats['login_attempts'] = (int) $pdo->query('SELECT COUNT(*) FROM login_attempts')->fetchColumn();
    $stats['files_shared'] = (int) $pdo->query('SELECT COUNT(*) FROM files')->fetchColumn();
} catch (PDOException $exception) {
    $statsError = $exception->getMessage();
}

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page">
    <div class="glass-container">
        <div class="admin-page-header">
            <h2 class="admin-page-title">Pannello Amministrativo</h2>
            <p class="admin-page-subtitle">Gestisci utenti, servizi e operazioni di back-office.</p>
        </div>

        <?php if (!empty($statsError)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($statsError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="row mt-4 dashboard-panel">
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Utenti</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['users'] ?? '-'; ?></p>
                    <p>Gestisci anagrafiche e ruoli</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/utenti.php', ENT_QUOTES, 'UTF-8'); ?>">Apri</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Servizi</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['services'] ?? '-'; ?></p>
                    <p>Catalogo servizi attivi</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/servizi.php', ENT_QUOTES, 'UTF-8'); ?>">Gestisci</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Richieste aperte</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['requests_pending'] ?? '-'; ?></p>
                    <p>Richieste servizi da evadere</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/requests.php', ENT_QUOTES, 'UTF-8'); ?>">Dettagli</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Ticket aperti</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['tickets_open'] ?? '-'; ?></p>
                    <p>Supporto clienti</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/tickets.php', ENT_QUOTES, 'UTF-8'); ?>">Assistenza</a>
                </div>
            </div>
        </div>

        <div class="row mt-4 dashboard-panel">
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Pratiche SPID</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['spid_pending'] ?? '-'; ?></p>
                    <p>Richieste in attesa</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/spid_requests.php', ENT_QUOTES, 'UTF-8'); ?>">Gestisci</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Ordini SIM</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['sim_processing'] ?? '-'; ?></p>
                    <p>Pratiche telefonia da processare</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/sim_orders.php', ENT_QUOTES, 'UTF-8'); ?>">Vai</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Spedizioni attive</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['shipments_transit'] ?? '-'; ?></p>
                    <p>Monitoraggio logistica</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/shipments.php', ENT_QUOTES, 'UTF-8'); ?>">Monitora</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Notifiche</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['notifications_unread'] ?? '-'; ?></p>
                    <p>Messaggi non letti dai clienti</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/notifications.php', ENT_QUOTES, 'UTF-8'); ?>">Invia</a>
                </div>
            </div>
        </div>

        <div class="row mt-4 dashboard-panel">
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Audit odierni</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['audit_today'] ?? '-'; ?></p>
                    <p>Eventi registrati oggi</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/audit_logs.php', ENT_QUOTES, 'UTF-8'); ?>">Storico</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Verifiche copertura</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['coverage_checks'] ?? '-'; ?></p>
                    <p>Numero totale richieste</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/coverage_checks.php', ENT_QUOTES, 'UTF-8'); ?>">Consulta</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Tentativi login</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['login_attempts'] ?? '-'; ?></p>
                    <p>Contatori attivi</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/login_attempts.php', ENT_QUOTES, 'UTF-8'); ?>">Gestisci</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card glass-container h-100">
                    <h5>Documenti</h5>
                    <p class="display-6 fw-bold mb-2"><?php echo $stats['files_shared'] ?? '-'; ?></p>
                    <p>File condivisi totali</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/files.php', ENT_QUOTES, 'UTF-8'); ?>">Apri</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
