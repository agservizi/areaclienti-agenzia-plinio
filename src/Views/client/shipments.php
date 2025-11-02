<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h1 class="h5 mb-3">Storico spedizioni</h1>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Tracking</th>
                                <th>Destinatario</th>
                                <th>Stato</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($shipments)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Nessuna spedizione registrata.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($shipments as $shipment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($shipment['tracking_code']) ?></td>
                                        <td><?= htmlspecialchars($shipment['recipient']['name'] ?? '-') ?></td>
                                        <td><span class="badge bg-primary-subtle text-primary text-uppercase"><?= htmlspecialchars($shipment['status']) ?></span></td>
                                        <td><?= htmlspecialchars($shipment['created_at'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Crea spedizione</h2>
                <form method="post" action="/client/shipments" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="sender_name">Mittente</label>
                        <input class="form-control" type="text" id="sender_name" name="sender_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="sender_address">Indirizzo mittente</label>
                        <input class="form-control" type="text" id="sender_address" name="sender_address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="recipient_name">Destinatario</label>
                        <input class="form-control" type="text" id="recipient_name" name="recipient_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="recipient_address">Indirizzo destinatario</label>
                        <input class="form-control" type="text" id="recipient_address" name="recipient_address">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="weight">Peso (kg)</label>
                            <input class="form-control" type="number" step="0.1" id="weight" name="weight" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="dimensions">Dimensioni</label>
                            <input class="form-control" type="text" id="dimensions" name="dimensions" placeholder="es. 30x20x15">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-4" type="submit">Genera spedizione</button>
                </form>
            </div>
        </div>
    </div>
</div>
