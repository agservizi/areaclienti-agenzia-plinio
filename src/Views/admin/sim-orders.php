<div class="card shadow-sm border-0">
    <div class="card-body">
        <h1 class="h5 mb-3">Pratiche telefonia</h1>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Operatore</th>
                        <th>Piano</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Nessuna pratica.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['user_name'] ?? 'N/D') ?></td>
                                <td><?= htmlspecialchars($order['operator']) ?></td>
                                <td><?= htmlspecialchars($order['plan']) ?></td>
                                <td><span class="badge bg-primary-subtle text-primary text-uppercase"><?= htmlspecialchars($order['status']) ?></span></td>
                                <td>
                                    <form class="d-flex gap-2" method="post" action="/admin/sim-orders/update">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
                                        <select class="form-select form-select-sm" name="status">
                                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="active" <?= $order['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
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
