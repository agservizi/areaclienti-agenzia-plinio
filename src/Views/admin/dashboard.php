<?php $admin = current_user(); ?>

<section class="dashboard-hero rounded-4 p-4 p-lg-5 mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-8">
            <div class="card-section-title mb-2">Situazione aggiornata</div>
            <h1 class="mb-3">Bentornato, <?= htmlspecialchars($admin['name'] ?? 'Admin') ?></h1>
            <p class="lead text-muted mb-4">Monitora l'operatività dell'agenzia: richieste SPID, pratiche telefoniche, spedizioni e ticket sono a portata di clic.</p>
            <div class="dashboard-quick-actions d-flex flex-wrap gap-2">
                <a class="btn btn-primary" href="/admin/users"><i class="bi bi-people-fill me-2"></i>Gestisci utenti</a>
                <a class="btn btn-outline-primary" href="/admin/spid"><i class="bi bi-shield-check me-2"></i>Richieste SPID</a>
                <a class="btn btn-outline-primary" href="/admin/tickets"><i class="bi bi-life-preserver me-2"></i>Assistenza</a>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <div class="card card-bordered shadow-sm">
                <div class="card-body">
                    <div class="card-section-title">Ultimo aggiornamento</div>
                    <div class="display-6 text-primary mb-1"><?= date('H:i') ?></div>
                    <div class="text-muted"><?= date('d F Y') ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$statCards = [
    ['label' => 'Utenti totali', 'value' => (int) ($stats['users'] ?? 0), 'icon' => 'bi-people-fill'],
    ['label' => 'SPID pendenti', 'value' => (int) ($stats['spid_pending'] ?? 0), 'icon' => 'bi-shield-check'],
    ['label' => 'SIM in lavorazione', 'value' => (int) ($stats['sim_processing'] ?? 0), 'icon' => 'bi-phone'],
    ['label' => 'Spedizioni oggi', 'value' => (int) ($stats['shipments_today'] ?? 0), 'icon' => 'bi-box-seam'],
    ['label' => 'Ticket aperti', 'value' => (int) ($stats['tickets_open'] ?? 0), 'icon' => 'bi-life-preserver'],
];
?>

<div class="row g-4">
    <?php foreach ($statCards as $card): ?>
        <div class="col-xxl-2 col-md-4 col-sm-6">
            <div class="card stats-card">
                <div class="card-body d-flex flex-column gap-3">
                    <div class="stat-icon"><i class="bi <?= htmlspecialchars($card['icon']) ?>"></i></div>
                    <div class="stat-label"><?= htmlspecialchars($card['label']) ?></div>
                    <div class="stat-value"><?= number_format((int) $card['value'], 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card card-bordered shadow-sm h-100">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-section-title">Pratiche digitali</div>
                        <h2 class="h5 mb-0">Richieste SPID recenti</h2>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="/admin/spid">Vedi tutte</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Stato</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentSpid)): ?>
                            <tr><td colspan="3"><div class="empty-state">Nessuna richiesta recente.</div></td></tr>
                        <?php else: ?>
                            <?php foreach ($recentSpid as $request): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold mb-1"><?= htmlspecialchars($request['user_name'] ?? '-') ?></div>
                                        <small class="text-muted">ID <?= htmlspecialchars((string) ($request['id'] ?? '')) ?></small>
                                    </td>
                                    <td><span class="status-pill" data-status="<?= htmlspecialchars(strtolower((string) ($request['status'] ?? ''))) ?>"><?= htmlspecialchars($request['status'] ?? '-') ?></span></td>
                                    <td><?= htmlspecialchars($request['created_at'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-bordered shadow-sm h-100">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-section-title">Supporto clienti</div>
                        <h2 class="h5 mb-0">Ticket recenti</h2>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="/admin/tickets">Gestisci ticket</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Oggetto</th>
                            <th>Stato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentTickets)): ?>
                            <tr><td colspan="3"><div class="empty-state">Nessun ticket recente.</div></td></tr>
                        <?php else: ?>
                            <?php foreach ($recentTickets as $ticket): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold mb-1"><?= htmlspecialchars($ticket['user_name'] ?? '-') ?></div>
                                        <small class="text-muted">ID <?= htmlspecialchars((string) ($ticket['id'] ?? '')) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($ticket['subject'] ?? '-') ?></td>
                                    <td><span class="status-pill" data-status="<?= htmlspecialchars(strtolower((string) ($ticket['status'] ?? ''))) ?>"><?= htmlspecialchars($ticket['status'] ?? '-') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card card-bordered shadow-sm">
            <div class="card-body">
                <div class="card-section-title mb-2">Comunicazioni</div>
                <h2 class="h5 mb-3">Invia email ai clienti</h2>
                <form method="post" action="/admin/notifications/broadcast">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="broadcast-subject">Oggetto</label>
                        <input class="form-control" type="text" id="broadcast-subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="broadcast-body">Messaggio</label>
                        <textarea class="form-control" id="broadcast-body" name="body" rows="4" required></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">L'invio raggiungerà tutti i clienti attivi.</span>
                        <button class="btn btn-primary" type="submit">Invia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-bordered shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="card-section-title mb-2">Promemoria rapidi</div>
                <h2 class="h5 mb-3">Azioni suggerite</h2>
                <ul class="list-group list-group-flush flex-grow-1">
                    <li class="list-group-item d-flex align-items-center">
                        <i class="bi bi-person-fill-add text-primary me-3"></i>
                        <div>
                            <div class="fw-semibold">Invita nuovi utenti</div>
                            <small class="text-muted">Registra manualmente clienti o collaboratori</small>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <i class="bi bi-envelope-paper text-primary me-3"></i>
                        <div>
                            <div class="fw-semibold">Aggiorna i modelli email</div>
                            <small class="text-muted">Personalizza i testi di comunicazione</small>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <i class="bi bi-bar-chart-line text-primary me-3"></i>
                        <div>
                            <div class="fw-semibold">Scarica i report mensili</div>
                            <small class="text-muted">Condividi i progressi con il team</small>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
