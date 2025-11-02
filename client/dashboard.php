<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

$requests = getUserRequests($user['id'], $pdo);
$tickets = getUserTickets($user['id'], $pdo);
$notifications = getUserNotifications($user['id'], $pdo);
$simOrders = getUserSimOrders($user['id'], $pdo);
$shipments = getUserShipments($user['id'], $pdo);
$spidRequests = getUserSpidRequests($user['id'], $pdo);

$pendingRequests = array_filter($requests, static fn ($request) => $request['status'] === 'pending');
$openTickets = array_filter($tickets, static fn ($ticket) => $ticket['status'] !== 'closed');
$unreadNotifications = array_filter($notifications, static fn ($notification) => (int) $notification['is_read'] === 0);

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1 class="mb-3">Ciao <?php echo htmlspecialchars($user['name'] ?? $user['email'], ENT_QUOTES, 'UTF-8'); ?>!</h1>
        <p>Da questa dashboard puoi controllare lo stato delle tue richieste e gestire i servizi digitali.</p>

        <div class="row g-3 mt-4">
            <div class="col-md-4">
                <div class="card glass-container h-100">
                    <h4>Richieste servizi</h4>
                    <p class="display-6 fw-bold"><?php echo count($requests); ?></p>
                    <p class="mb-3">di cui <?php echo count($pendingRequests); ?> in attesa</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/client/requests.php', ENT_QUOTES, 'UTF-8'); ?>">Vai alle richieste</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card glass-container h-100">
                    <h4>Ticket aperti</h4>
                    <p class="display-6 fw-bold"><?php echo count($openTickets); ?></p>
                    <p class="mb-3">su <?php echo count($tickets); ?> totali</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/client/supporto.php', ENT_QUOTES, 'UTF-8'); ?>">Supporto clienti</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card glass-container h-100">
                    <h4>Notifiche</h4>
                    <p class="display-6 fw-bold"><?php echo count($unreadNotifications); ?></p>
                    <p class="mb-3">non lette</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/client/notifications.php', ENT_QUOTES, 'UTF-8'); ?>">Visualizza</a>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-4">
            <div class="col-md-4">
                <div class="card glass-container h-100">
                    <h4>Ordini SIM</h4>
                    <p class="display-6 fw-bold"><?php echo count($simOrders); ?></p>
                    <p class="mb-3">Gestisci le tue richieste telefonia</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/client/sim.php', ENT_QUOTES, 'UTF-8'); ?>">Gestisci</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card glass-container h-100">
                    <h4>Spedizioni</h4>
                    <p class="display-6 fw-bold"><?php echo count($shipments); ?></p>
                    <p class="mb-3">Controlla lo stato dei tuoi invii</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/client/shipments.php', ENT_QUOTES, 'UTF-8'); ?>">Vai</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card glass-container h-100">
                    <h4>Pratiche SPID</h4>
                    <p class="display-6 fw-bold"><?php echo count($spidRequests); ?></p>
                    <p class="mb-3">Gestisci attivazioni e rinnovi</p>
                    <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/client/spid.php', ENT_QUOTES, 'UTF-8'); ?>">Dettagli</a>
                </div>
            </div>
        </div>
    </div>
</div>
<footer class="footer-glass mt-5">
    <div class="container text-center">
        <small>&copy; <span data-current-year></span> Agenzia Plinio - Area Clienti</small>
    </div>
</footer>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/client.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
