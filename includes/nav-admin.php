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
    <div class="row">
        <aside class="col-12 col-lg-3 col-xxl-2 sidebar-admin">
            <div class="px-3 mb-4">
                <h2 class="h5 text-uppercase text-muted">Pannello admin</h2>
            </div>
            <nav class="nav flex-column px-3">
                <?php foreach ($adminMenu as $item): ?>
                    <?php $isActive = strpos($_SERVER['REQUEST_URI'], $item['href']) === 0; ?>
                    <a class="nav-link<?= $isActive ? ' active' : '' ?>" href="<?= $item['href'] ?>">
                        <span><?= $item['icon'] ?></span>
                        <span><?= escape($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <section class="col-12 col-lg-9 col-xxl-10 py-4 py-lg-5 px-4 px-lg-5">
