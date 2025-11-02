<?php $client = current_user(); ?>

<section class="dashboard-hero rounded-4 p-4 p-lg-5 mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <div class="card-section-title mb-2">Il tuo portale</div>
            <h1 class="mb-3">Ciao <?= htmlspecialchars($client['name'] ?? '') ?>, benvenuto nella tua area clienti</h1>
            <p class="lead text-muted mb-4">Controlla lo stato delle richieste SPID, gestisci le spedizioni e resta in contatto con il supporto dedicato dell'Agenzia Plinio.</p>
            <div class="dashboard-quick-actions d-flex flex-wrap gap-2">
                <a class="btn btn-primary" href="/client/services"><i class="bi bi-grid-1x2-fill me-2"></i>Nuova richiesta</a>
                <a class="btn btn-outline-primary" href="/client/shipments"><i class="bi bi-box-seam me-2"></i>Spedizioni</a>
                <a class="btn btn-outline-primary" href="/client/tickets"><i class="bi bi-chat-square-text me-2"></i>Assistenza</a>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <div class="card card-bordered shadow-sm">
                <div class="card-body">
                    <div class="card-section-title">Prossimi passi</div>
                    <ul class="list-unstyled mb-0 mt-3 small text-muted text-start text-lg-end">
                        <li class="mb-2"><i class="bi bi-check2-circle text-primary me-2"></i>Carica i documenti mancanti</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-primary me-2"></i>Controlla l'avanzamento delle pratiche</li>
                        <li><i class="bi bi-check2-circle text-primary me-2"></i>Contatta il consulente dedicato</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$clientStats = [
    ['label' => 'Richieste SPID', 'value' => (int) $spidCount, 'icon' => 'bi-shield-check'],
    ['label' => 'Pratiche telefonia', 'value' => (int) $simCount, 'icon' => 'bi-phone'],
    ['label' => 'Spedizioni', 'value' => (int) $shipmentCount, 'icon' => 'bi-box'],
    ['label' => 'Ticket aperti', 'value' => (int) $openTickets, 'icon' => 'bi-life-preserver'],
];
?>

<section class="row g-4">
    <?php foreach ($clientStats as $card): ?>
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card">
                <div class="card-body d-flex flex-column gap-3">
                    <div class="stat-icon"><i class="bi <?= htmlspecialchars($card['icon']) ?>"></i></div>
                    <div class="stat-label"><?= htmlspecialchars($card['label']) ?></div>
                    <div class="stat-value"><?= number_format((int) $card['value'], 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card card-bordered shadow-sm h-100">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-section-title">Logistica</div>
                        <h2 class="h5 mb-0">Ultime spedizioni</h2>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="/client/shipments">Tutte le spedizioni</a>
                </div>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($recentShipments)): ?>
                    <div class="list-group-item empty-state">Nessuna spedizione recente.</div>
                <?php else: ?>
                    <?php foreach ($recentShipments as $shipment): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold mb-1">Tracking <?= htmlspecialchars($shipment['tracking_code']) ?></div>
                                <small class="text-muted">Destinatario: <?= htmlspecialchars($shipment['recipient']['name'] ?? '-') ?></small>
                            </div>
                            <span class="status-pill" data-status="<?= htmlspecialchars(strtolower((string) ($shipment['status'] ?? ''))) ?>"><?= htmlspecialchars($shipment['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-bordered shadow-sm h-100">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-section-title">Supporto</div>
                        <h2 class="h5 mb-0">Ticket di assistenza</h2>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="/client/tickets">Apri ticket</a>
                </div>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($recentTickets)): ?>
                    <div class="list-group-item empty-state">Nessun ticket recente.</div>
                <?php else: ?>
                    <?php foreach ($recentTickets as $ticket): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold mb-1"><?= htmlspecialchars($ticket['subject']) ?></div>
                                <small class="text-muted">Aggiornato: <?= htmlspecialchars($ticket['updated_at'] ?? '-') ?></small>
                            </div>
                            <span class="status-pill" data-status="<?= htmlspecialchars(strtolower((string) ($ticket['status'] ?? ''))) ?>"><?= htmlspecialchars($ticket['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
