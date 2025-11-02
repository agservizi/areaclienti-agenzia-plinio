<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config = include __DIR__ . '/../config/env.php';
$appUrl = rtrim((string) ($config['APP_URL'] ?? ''), '/');
$basePath = $appUrl !== '' ? $appUrl : '';
$assetBase = ($basePath !== '' ? $basePath : '') . '/assets';

$currentUser = $_SESSION['user'] ?? null;
$pageTitle = $pageTitle ?? 'Area Amministrazione';
$adminActive = $adminActive ?? '';

$displayName = $currentUser['name'] ?? $currentUser['username'] ?? 'Admin';
$initial = $displayName !== '' ? strtoupper(substr($displayName, 0, 1)) : 'A';

if (function_exists('mb_substr') && function_exists('mb_strtoupper') && $displayName !== '') {
    $initial = mb_strtoupper(mb_substr($displayName, 0, 1, 'UTF-8'), 'UTF-8');
}

$adminNavigation = [
    'dashboard' => [
        'label' => 'Dashboard',
        'icon' => 'bi-speedometer2',
        'href' => $basePath . '/admin/dashboard.php',
    ],
    'users' => [
        'label' => 'Utenti',
        'icon' => 'bi-people',
        'href' => $basePath . '/admin/utenti.php',
    ],
    'services' => [
        'label' => 'Servizi',
        'icon' => 'bi-briefcase',
        'href' => $basePath . '/admin/servizi.php',
    ],
    'requests' => [
        'label' => 'Richieste',
        'icon' => 'bi-inboxes',
        'href' => $basePath . '/admin/requests.php',
    ],
    'tickets' => [
        'label' => 'Ticket',
        'icon' => 'bi-life-preserver',
        'href' => $basePath . '/admin/tickets.php',
    ],
    'spid' => [
        'label' => 'Pratiche SPID',
        'icon' => 'bi-shield-lock',
        'href' => $basePath . '/admin/spid_requests.php',
    ],
    'sim' => [
        'label' => 'Ordini SIM',
        'icon' => 'bi-phone',
        'href' => $basePath . '/admin/sim_orders.php',
    ],
    'shipments' => [
        'label' => 'Spedizioni',
        'icon' => 'bi-truck',
        'href' => $basePath . '/admin/shipments.php',
    ],
    'notifications' => [
        'label' => 'Notifiche',
        'icon' => 'bi-bell',
        'href' => $basePath . '/admin/notifications.php',
    ],
    'files' => [
        'label' => 'Documenti',
        'icon' => 'bi-folder2',
        'href' => $basePath . '/admin/files.php',
    ],
    'coverage' => [
        'label' => 'Copertura',
        'icon' => 'bi-rss',
        'href' => $basePath . '/admin/coverage_checks.php',
    ],
    'audit' => [
        'label' => 'Audit',
        'icon' => 'bi-clipboard-data',
        'href' => $basePath . '/admin/audit_logs.php',
    ],
    'login_attempts' => [
        'label' => 'Login',
        'icon' => 'bi-shield-check',
        'href' => $basePath . '/admin/login_attempts.php',
    ],
    'settings' => [
        'label' => 'Impostazioni',
        'icon' => 'bi-gear',
        'href' => $basePath . '/admin/impostazioni.php',
    ],
];

?><!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pannello amministrativo Agenzia Plinio">
    <title><?php echo htmlspecialchars($pageTitle . ' · Admin · Agenzia Plinio', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase . '/css/bootstrap.min.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase . '/css/ui-glass.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase . '/css/glass.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase . '/css/admin.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="ui-glass-app admin-app">
<div class="admin-shell" data-admin-shell>
    <aside class="admin-sidebar" data-admin-sidebar>
        <div class="admin-brand">
            <a class="admin-brand-link" href="<?php echo htmlspecialchars($basePath . '/admin/dashboard.php', ENT_QUOTES, 'UTF-8'); ?>">
                <img src="<?php echo htmlspecialchars($assetBase . '/img/logo.svg', ENT_QUOTES, 'UTF-8'); ?>" alt="Agenzia Plinio" width="44" height="44">
                <div class="admin-brand-text">
                    <span class="admin-brand-name">Agenzia Plinio</span>
                    <span class="admin-brand-section">Area Admin</span>
                </div>
            </a>
        </div>
        <nav class="admin-menu" aria-label="Navigazione amministrazione">
            <?php foreach ($adminNavigation as $key => $item): ?>
                <?php $isActive = $adminActive === $key; ?>
                <a class="admin-menu-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="bi <?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                    <span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <div class="admin-sidebar-backdrop" data-admin-backdrop></div>
    <div class="admin-main">
        <header class="admin-topbar">
            <button class="btn btn-outline-light admin-sidebar-toggle" type="button" data-admin-toggle aria-label="Apri menù">
                <i class="bi bi-list"></i>
            </button>
            <div class="admin-topbar-title">
                <h1 class="admin-topbar-heading"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
            </div>
            <div class="admin-topbar-actions">
                <a class="btn btn-sm btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/index.php', ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="bi bi-house"></i>
                    <span>Portale</span>
                </a>
                <div class="admin-user">
                    <span class="admin-user-avatar" aria-hidden="true"><?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?></span>
                    <div class="admin-user-meta">
                        <span class="admin-user-name"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="admin-user-role">Amministratore</span>
                    </div>
                    <a class="btn btn-sm btn-primary" href="<?php echo htmlspecialchars($basePath . '/auth/logout.php', ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </header>
        <main class="admin-content">