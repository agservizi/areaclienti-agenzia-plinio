<div class="row g-4">
    <div class="col-xl-2 col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Utenti totali</div>
                <div class="h4 mb-0 text-primary"><?= (int) ($stats['users'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">SPID pendenti</div>
                <div class="h4 mb-0 text-primary"><?= (int) ($stats['spid_pending'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">SIM in lavorazione</div>
                <div class="h4 mb-0 text-primary"><?= (int) ($stats['sim_processing'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Spedizioni oggi</div>
                <div class="h4 mb-0 text-primary"><?= (int) ($stats['shipments_today'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Ticket aperti</div>
                <div class="h4 mb-0 text-primary"><?= (int) ($stats['tickets_open'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0">
                <h2 class="h5 mb-0">Richieste SPID recenti</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Stato</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentSpid)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Nessuna richiesta recente.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentSpid as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['user_name'] ?? '') ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary text-uppercase"><?= htmlspecialchars($request['status']) ?></span></td>
                                    <td><?= htmlspecialchars($request['created_at'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0">
                <h2 class="h5 mb-0">Ticket recenti</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Oggetto</th>
                            <th>Stato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentTickets)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Nessun ticket recente.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentTickets as $ticket): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ticket['user_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($ticket['subject'] ?? '') ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary text-uppercase"><?= htmlspecialchars($ticket['status']) ?></span></td>
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
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h5 mb-3">Invia comunicazione email</h2>
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
                    <button class="btn btn-primary" type="submit">Invia</button>
                </form>
            </div>
        </div>
    </div>
</div>
