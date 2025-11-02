<div class="card shadow-sm border-0">
    <div class="card-body">
        <h1 class="h5 mb-3">Spedizioni</h1>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Tracking</th>
                        <th>Cliente</th>
                        <th>Destinatario</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($shipments)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Nessuna spedizione.</td></tr>
                    <?php else: ?>
                        <?php foreach ($shipments as $shipment): ?>
                            <tr>
                                <td><?= htmlspecialchars($shipment['tracking_code']) ?></td>
                                <td><?= htmlspecialchars($shipment['user_name'] ?? 'N/D') ?></td>
                                <td><?= htmlspecialchars($shipment['recipient']['name'] ?? '-') ?></td>
                                <td><span class="badge bg-primary-subtle text-primary text-uppercase"><?= htmlspecialchars($shipment['status']) ?></span></td>
                                <td>
                                    <form class="d-flex gap-2" method="post" action="/admin/shipments/update">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $shipment['id'] ?>">
                                        <select class="form-select form-select-sm" name="status">
                                            <option value="created" <?= $shipment['status'] === 'created' ? 'selected' : '' ?>>Created</option>
                                            <option value="in_transit" <?= $shipment['status'] === 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                                            <option value="delivered" <?= $shipment['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="cancelled" <?= $shipment['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Aggiorna</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
