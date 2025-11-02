<section class="row g-4">
    <div class="col-md-3">
        <div class="card tile-card border-0 shadow-sm">
            <div class="card-body">
                <span class="text-muted">Richieste SPID</span>
                <h3 class="mt-2 mb-0 text-primary"><?= (int) $spidCount ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card tile-card border-0 shadow-sm">
            <div class="card-body">
                <span class="text-muted">Pratiche telefonia</span>
                <h3 class="mt-2 mb-0 text-primary"><?= (int) $simCount ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card tile-card border-0 shadow-sm">
            <div class="card-body">
                <span class="text-muted">Spedizioni</span>
                <h3 class="mt-2 mb-0 text-primary"><?= (int) $shipmentCount ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card tile-card border-0 shadow-sm">
            <div class="card-body">
                <span class="text-muted">Ticket aperti</span>
                <h3 class="mt-2 mb-0 text-primary"><?= (int) $openTickets ?></h3>
            </div>
        </div>
    </div>
</section>

<section class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Ultime spedizioni</h2>
                    <a class="btn btn-sm btn-outline-primary" href="/client/shipments">Tutte le spedizioni</a>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                <?php if (empty($recentShipments)): ?>
                    <li class="list-group-item">Nessuna spedizione recente.</li>
                <?php else: ?>
                    <?php foreach ($recentShipments as $shipment): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($shipment['tracking_code']) ?></strong>
                                <div class="text-muted small">Destinatario: <?= htmlspecialchars($shipment['recipient']['name'] ?? '-') ?></div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary text-uppercase"><?= htmlspecialchars($shipment['status']) ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Ticket di supporto</h2>
                    <a class="btn btn-sm btn-outline-primary" href="/client/tickets">Apri ticket</a>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                <?php if (empty($recentTickets)): ?>
                    <li class="list-group-item">Nessun ticket recente.</li>
                <?php else: ?>
                    <?php foreach ($recentTickets as $ticket): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($ticket['subject']) ?></strong>
                                <div class="text-muted small">Stato: <?= htmlspecialchars($ticket['status']) ?></div>
                            </div>
                            <span class="badge bg-light text-primary"><?= count($ticket['messages'] ?? []) ?> messaggi</span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</section>
