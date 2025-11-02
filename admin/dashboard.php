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

$formatStat = static function ($value) {
    return number_format((int) $value, 0, ',', '.');
};

$now = new DateTime();
$todayDate = $now->format('d/m/Y');
$currentTime = $now->format('H:i');
$adminWelcome = $_SESSION['user']['name'] ?? ($_SESSION['user']['username'] ?? 'Amministratore');
$openItems = (int) ($stats['requests_pending'] ?? 0) + (int) ($stats['tickets_open'] ?? 0) + (int) ($stats['spid_pending'] ?? 0) + (int) ($stats['sim_processing'] ?? 0);

include __DIR__ . '/../includes/admin_header.php';

$summaryMetrics = [
    [
        'label' => 'Utenti registrati',
        'value' => $stats['users'] ?? 0,
        'icon' => 'bi-people',
        'hint' => 'Account presenti nel portale',
        'href' => $basePath . '/admin/utenti.php',
        'action' => 'Gestisci utenti',
    ],
    [
        'label' => 'Servizi attivi',
        'value' => $stats['services'] ?? 0,
        'icon' => 'bi-briefcase',
        'hint' => 'Offerte disponibili per i clienti',
        'href' => $basePath . '/admin/servizi.php',
        'action' => 'Aggiorna catalogo',
    ],
    [
        'label' => 'Richieste in attesa',
        'value' => $stats['requests_pending'] ?? 0,
        'icon' => 'bi-inboxes',
        'hint' => 'Pratiche da assegnare o concludere',
        'href' => $basePath . '/admin/requests.php',
        'action' => 'Apri richieste',
    ],
    [
        'label' => 'Ticket aperti',
        'value' => $stats['tickets_open'] ?? 0,
        'icon' => 'bi-life-preserver',
        'hint' => 'Supporto clienti in corso',
        'href' => $basePath . '/admin/tickets.php',
        'action' => 'Monitora ticket',
    ],
    [
        'label' => 'Notifiche da leggere',
        'value' => $stats['notifications_unread'] ?? 0,
        'icon' => 'bi-bell',
        'hint' => 'Messaggi in attesa di lettura',
        'href' => $basePath . '/admin/notifications.php',
        'action' => 'Gestisci notifiche',
    ],
    [
        'label' => 'Log odierni',
        'value' => $stats['audit_today'] ?? 0,
        'icon' => 'bi-clipboard-data',
        'hint' => 'Eventi registrati nelle ultime ore',
        'href' => $basePath . '/admin/audit_logs.php',
        'action' => 'Consulta audit',
    ],
];

$quickActions = [
    [
        'label' => 'Nuova notifica',
        'icon' => 'bi-megaphone',
        'href' => $basePath . '/admin/notifications.php',
    ],
    [
        'label' => 'Aggiungi servizio',
        'icon' => 'bi-plus-circle',
        'href' => $basePath . '/admin/servizi.php',
    ],
    [
        'label' => 'Verifica richieste',
        'icon' => 'bi-ui-checks',
        'href' => $basePath . '/admin/requests.php',
    ],
    [
        'label' => 'Esporta audit',
        'icon' => 'bi-arrow-down-circle',
        'href' => $basePath . '/admin/audit_logs.php?limit=250',
    ],
];

$operationsHighlights = [
    [
        'label' => 'Richieste da gestire',
        'hint' => 'Clienti in attesa di risposta',
        'value' => $stats['requests_pending'] ?? 0,
        'href' => $basePath . '/admin/requests.php',
        'linkLabel' => 'Apri richieste',
    ],
    [
        'label' => 'Ticket aperti',
        'hint' => 'Assistenza tecnica da seguire',
        'value' => $stats['tickets_open'] ?? 0,
        'href' => $basePath . '/admin/tickets.php',
        'linkLabel' => 'Vai ai ticket',
    ],
    [
        'label' => 'Notifiche non lette',
        'hint' => 'Messaggi destinati ai clienti',
        'value' => $stats['notifications_unread'] ?? 0,
        'href' => $basePath . '/admin/notifications.php',
        'linkLabel' => 'Gestisci notifiche',
    ],
];

$monitoringHighlights = [
    [
        'label' => 'Pratiche SPID in revisione',
        'hint' => 'Richieste identità digitale da completare',
        'value' => $stats['spid_pending'] ?? 0,
        'href' => $basePath . '/admin/spid_requests.php',
        'linkLabel' => 'Apri pratiche',
    ],
    [
        'label' => 'Ordini SIM in lavorazione',
        'hint' => 'Attivazioni telefono e connettività',
        'value' => $stats['sim_processing'] ?? 0,
        'href' => $basePath . '/admin/sim_orders.php',
        'linkLabel' => 'Gestisci ordini',
    ],
    [
        'label' => 'Spedizioni in transito',
        'hint' => 'Invii logistici ancora da consegnare',
        'value' => $stats['shipments_transit'] ?? 0,
        'href' => $basePath . '/admin/shipments.php',
        'linkLabel' => 'Monitora spedizioni',
    ],
];

$securityHighlights = [
    [
        'label' => 'Tentativi login registrati',
        'hint' => 'Verificare eventuali blocchi',
        'value' => $stats['login_attempts'] ?? 0,
        'href' => $basePath . '/admin/login_attempts.php',
        'linkLabel' => 'Controlla accessi',
    ],
    [
        'label' => 'Documenti condivisi',
        'hint' => 'File disponibili nell\'area clienti',
        'value' => $stats['files_shared'] ?? 0,
        'href' => $basePath . '/admin/files.php',
        'linkLabel' => 'Vai ai documenti',
    ],
    [
        'label' => 'Log di oggi',
        'hint' => 'Eventi di audit registrati',
        'value' => $stats['audit_today'] ?? 0,
        'href' => $basePath . '/admin/audit_logs.php',
        'linkLabel' => 'Consulta audit',
    ],
];
?>
<div class="admin-page">
    <section class="admin-dashboard-hero glass-container">
        <div class="admin-dashboard-hero-copy">
            <p class="admin-hero-greeting">Benvenuto, <?php echo htmlspecialchars($adminWelcome, ENT_QUOTES, 'UTF-8'); ?></p>
            <h2>Panoramica operativa del portale</h2>
            <p class="admin-hero-subtitle">Tieniti aggiornato su stati, richieste e indicatori chiave delle attività digitali dell'agenzia.</p>
            <div class="admin-hero-meta">
                <span class="admin-hero-pill"><i class="bi bi-calendar-event"></i> <?php echo htmlspecialchars($todayDate, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="admin-hero-pill"><i class="bi bi-clock-history"></i> Aggiornato alle <?php echo htmlspecialchars($currentTime, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="admin-hero-pill"><i class="bi bi-lightning-charge"></i> <?php echo htmlspecialchars($formatStat($openItems), ENT_QUOTES, 'UTF-8'); ?> attività aperte</span>
            </div>
        </div>
        <div class="admin-dashboard-hero-side">
            <div class="admin-hero-highlight">
                <span class="admin-hero-highlight-label">Notifiche non lette</span>
                <span class="admin-hero-highlight-value"><?php echo htmlspecialchars($formatStat($stats['notifications_unread'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></span>
                <a class="btn btn-sm btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/notifications.php', ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="bi bi-bell"></i>
                    Gestisci notifiche
                </a>
            </div>
            <div class="admin-hero-highlight">
                <span class="admin-hero-highlight-label">Verifiche copertura totali</span>
                <span class="admin-hero-highlight-value"><?php echo htmlspecialchars($formatStat($stats['coverage_checks'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></span>
                <a class="btn btn-sm btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/admin/coverage_checks.php', ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="bi bi-rss"></i>
                    Consulta storico
                </a>
            </div>
        </div>
    </section>

    <?php if (!empty($statsError)): ?>
        <div class="alert alert-danger mt-3" role="alert">
            <?php echo htmlspecialchars($statsError, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="admin-quick-actions">
        <span class="admin-quick-actions-title">Azioni rapide</span>
        <div class="admin-quick-actions-list">
            <?php foreach ($quickActions as $action): ?>
                <a class="btn-ghost" href="<?php echo htmlspecialchars($action['href'], ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="bi <?php echo htmlspecialchars($action['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                    <span><?php echo htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="admin-summary-grid">
        <?php foreach ($summaryMetrics as $metric): ?>
            <div class="admin-summary-card">
                <div class="admin-summary-card-top">
                    <span class="admin-summary-card-icon"><i class="bi <?php echo htmlspecialchars($metric['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span>
                    <p class="admin-summary-card-title"><?php echo htmlspecialchars($metric['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="admin-summary-card-value"><?php echo htmlspecialchars($formatStat($metric['value']), ENT_QUOTES, 'UTF-8'); ?></div>
                <p class="admin-summary-card-hint"><?php echo htmlspecialchars($metric['hint'], ENT_QUOTES, 'UTF-8'); ?></p>
                <a class="admin-summary-card-action" href="<?php echo htmlspecialchars($metric['href'], ENT_QUOTES, 'UTF-8'); ?>">
                    <span><?php echo htmlspecialchars($metric['action'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <i class="bi bi-arrow-right-short"></i>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="admin-panels-grid">
        <section class="admin-panel">
            <div class="admin-panel-header">
                <h3 class="admin-panel-title">Front office</h3>
                <p class="admin-panel-subtitle">Richieste e assistenza clienti</p>
            </div>
            <ul class="admin-highlight-list">
                <?php foreach ($operationsHighlights as $item): ?>
                    <li class="admin-highlight-item">
                        <div class="admin-highlight-meta">
                            <span class="admin-highlight-label"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="admin-highlight-hint"><?php echo htmlspecialchars($item['hint'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="admin-highlight-stats">
                            <span class="admin-highlight-value"><?php echo htmlspecialchars($formatStat($item['value']), ENT_QUOTES, 'UTF-8'); ?></span>
                            <a class="admin-highlight-link" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>">
                                <span><?php echo htmlspecialchars($item['linkLabel'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-header">
                <h3 class="admin-panel-title">Logistica & servizi</h3>
                <p class="admin-panel-subtitle">Monitoraggio pratiche e consegne</p>
            </div>
            <ul class="admin-highlight-list">
                <?php foreach ($monitoringHighlights as $item): ?>
                    <li class="admin-highlight-item">
                        <div class="admin-highlight-meta">
                            <span class="admin-highlight-label"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="admin-highlight-hint"><?php echo htmlspecialchars($item['hint'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="admin-highlight-stats">
                            <span class="admin-highlight-value"><?php echo htmlspecialchars($formatStat($item['value']), ENT_QUOTES, 'UTF-8'); ?></span>
                            <a class="admin-highlight-link" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>">
                                <span><?php echo htmlspecialchars($item['linkLabel'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-header">
                <h3 class="admin-panel-title">Controlli & sicurezza</h3>
                <p class="admin-panel-subtitle">Stato log, file e accessi</p>
            </div>
            <ul class="admin-highlight-list">
                <?php foreach ($securityHighlights as $item): ?>
                    <li class="admin-highlight-item">
                        <div class="admin-highlight-meta">
                            <span class="admin-highlight-label"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="admin-highlight-hint"><?php echo htmlspecialchars($item['hint'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="admin-highlight-stats">
                            <span class="admin-highlight-value"><?php echo htmlspecialchars($formatStat($item['value']), ENT_QUOTES, 'UTF-8'); ?></span>
                            <a class="admin-highlight-link" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>">
                                <span><?php echo htmlspecialchars($item['linkLabel'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
