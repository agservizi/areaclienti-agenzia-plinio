<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login(ROLE_ADMIN);

$adminMenu = [
    ['label' => 'Dashboard', 'href' => '/admin/dashboard.php', 'icon' => '📊'],
    ['label' => 'Servizi', 'href' => '/admin/manage-services.php', 'icon' => '🛠️'],
    ['label' => 'Richieste', 'href' => '/admin/manage-requests.php', 'icon' => '📬'],
    ['label' => 'Utenti', 'href' => '/admin/manage-users.php', 'icon' => '👥'],
    ['label' => 'Report', 'href' => '/admin/reports.php', 'icon' => '📈'],
];

$currentUser = current_user();
$pageHeading = 'Area amministrativa';
if (isset($pageTitle)) {
    $fragments = explode('|', (string) $pageTitle);
    $pageHeading = trim($fragments[0] ?? (string) $pageTitle);
}
?>
<div class="admin-shell" data-admin-shell>
    <aside class="sidebar-admin" id="adminSidebar" aria-label="Menu amministratore">
        <div class="sidebar-admin__header px-3">
            <h2 class="h6 text-uppercase text-muted mb-0">Pannello admin</h2>
        </div>
        <nav class="nav flex-column px-2" data-sidebar-nav>
            <?php foreach ($adminMenu as $item): ?>
                <?php $isActive = strpos($_SERVER['REQUEST_URI'], $item['href']) === 0; ?>
                <a class="nav-link<?= $isActive ? ' active' : '' ?>" href="<?= $item['href'] ?>" title="<?= escape($item['label']) ?>">
                    <span class="nav-link-icon" aria-hidden="true"><?= $item['icon'] ?></span>
                    <span class="nav-link-label"><?= escape($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <div class="admin-main">
        <div class="admin-topbar" data-admin-topbar>
            <button type="button" class="admin-topbar-toggle" id="sidebarToggle" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true">
                <span class="admin-topbar-toggle__icon" aria-hidden="true"></span>
                <span class="visually-hidden">Mostra o nascondi il menu</span>
            </button>
            <div class="admin-topbar-info">
                <h1 class="h5 mb-0"><?= escape($pageHeading) ?></h1>
                <?php if ($currentUser): ?>
                    <span class="admin-topbar-user text-muted">Connesso come <?= escape($currentUser['name'] ?? $currentUser['email']) ?></span>
                <?php endif; ?>
            </div>
            <div class="admin-topbar-actions">
                <a class="btn btn-outline-accent btn-sm" href="/">Vai al sito</a>
                <a class="btn btn-outline-accent btn-sm" href="/logout.php">Esci</a>
            </div>
        </div>
        <div class="admin-sidebar-backdrop" data-sidebar-backdrop aria-hidden="true"></div>
        <section class="admin-content py-4 py-lg-5 px-4 px-lg-5" data-admin-content>
