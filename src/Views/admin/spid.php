<div class="card shadow-sm border-0">
    <div class="card-body">
        <h1 class="h5 mb-3">Pratiche SPID</h1>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Stato</th>
                        <th>Dati</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="4" class="text-center text-muted">Nessuna pratica.</td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?= htmlspecialchars($request['user_name'] ?? '') ?><br><small class="text-muted"><?= htmlspecialchars($request['email'] ?? '') ?></small></td>
                                <td><span class="badge bg-primary-subtle text-primary text-uppercase"><?= htmlspecialchars($request['status']) ?></span></td>
                                <td>
                                    <div class="small text-muted">Livello: <?= htmlspecialchars($request['data']['service_level'] ?? '-') ?></div>
                                    <div class="small text-muted">Documento: <?= htmlspecialchars($request['data']['document_number'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <form class="d-flex gap-2" method="post" action="/admin/spid/update">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $request['id'] ?>">
                                        <select class="form-select form-select-sm" name="status">
                                            <option value="pending" <?= $request['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="approved" <?= $request['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                            <option value="rejected" <?= $request['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
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
