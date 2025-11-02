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

?>
<div class="container-fluid">
    <div class="row align-items-start">
        <aside class="col-12 col-lg-3 col-xxl-2 sidebar-admin" id="adminSidebar" data-sidebar>
            <div class="sidebar-admin-inner">
                <div class="px-3 mb-4">
                    <h2 class="h5 text-uppercase text-muted mb-0">Pannello admin</h2>
                    <button type="button" class="btn btn-outline-accent w-100 mt-3 d-lg-none sidebar-toggle-mobile" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="false">
                        Menu amministratore
                    </button>
                </div>
                <nav class="nav flex-column px-3" data-sidebar-nav>
                    <?php foreach ($adminMenu as $item): ?>
                        <?php $isActive = strpos($_SERVER['REQUEST_URI'], $item['href']) === 0; ?>
                        <a class="nav-link<?= $isActive ? ' active' : '' ?>" href="<?= $item['href'] ?>">
                            <span><?= $item['icon'] ?></span>
                            <span><?= escape($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </aside>
        <section class="col-12 col-lg-9 col-xxl-10 py-4 py-lg-5 px-4 px-lg-5">
