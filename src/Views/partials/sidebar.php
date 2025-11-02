<?php
/** @var array|null $currentUser */
/** @var array $flashes */
?>
<aside class="admin-sidebar text-white">
    <div class="admin-sidebar-inner d-flex flex-column h-100">
        <div class="sidebar-brand p-4 border-bottom border-secondary">
            <h2 class="h5 mb-1">Agenzia Plinio</h2>
            <span class="badge bg-primary">Admin</span>
        </div>
        <nav class="nav flex-column px-3 py-4 gap-1 flex-grow-1">
            <a class="nav-link text-white" href="/admin/dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a class="nav-link text-white" href="/admin/users"><i class="bi bi-people me-2"></i>Utenti</a>
            <a class="nav-link text-white" href="/admin/spid"><i class="bi bi-shield-lock me-2"></i>SPID</a>
            <a class="nav-link text-white" href="/admin/sim-orders"><i class="bi bi-phone me-2"></i>Telefonia</a>
            <a class="nav-link text-white" href="/admin/shipments"><i class="bi bi-box-seam me-2"></i>Spedizioni</a>
            <a class="nav-link text-white" href="/admin/tickets"><i class="bi bi-life-preserver me-2"></i>Ticket</a>
        </nav>
    </div>
</aside>
<div class="admin-main flex-grow-1 d-flex flex-column bg-light position-relative">
    <header class="admin-topbar d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
        <div>
            <h1 class="h4 mb-0">Pannello di controllo</h1>
            <small class="text-muted">Benvenuto, <?= htmlspecialchars($currentUser['name'] ?? '') ?></small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form class="m-0" method="post" action="/auth/logout">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <button class="btn btn-outline-light" type="submit">Esci</button>
            </form>
        </div>
    </header>
    <?php if (!empty($flashes)): ?>
        <div class="admin-flashes px-4 pt-3">
            <?php include __DIR__ . '/flash.php'; ?>
        </div>
    <?php endif; ?>
    <div class="admin-content container-fluid py-4 flex-grow-1">
