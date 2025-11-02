<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h1 class="h5 mb-3">Utenti registrati</h1>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Ruolo</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="4" class="text-center text-muted">Nessun utente registrato.</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><span class="badge bg-primary-subtle text-primary text-uppercase"><?= htmlspecialchars($user['role']) ?></span></td>
                                        <td>
                                            <form class="d-inline" method="post" action="/admin/users/update-role">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                                                <select class="form-select form-select-sm d-inline w-auto" name="role">
                                                    <option value="client" <?= $user['role'] === 'client' ? 'selected' : '' ?>>Client</option>
                                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
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
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h5 mb-3">Crea nuovo utente</h2>
                <form method="post" action="/admin/users/create">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="user-name">Nome</label>
                        <input class="form-control" type="text" id="user-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="user-email">Email</label>
                        <input class="form-control" type="email" id="user-email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="user-phone">Telefono</label>
                        <input class="form-control" type="tel" id="user-phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="user-role">Ruolo</label>
                        <select class="form-select" id="user-role" name="role" required>
                            <option value="client">Client</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="user-password">Password (opzionale)</label>
                        <input class="form-control" type="text" id="user-password" name="password" placeholder="Lascia vuoto per generare automaticamente">
                    </div>
                    <button class="btn btn-primary" type="submit">Crea utente</button>
                </form>
            </div>
        </div>
    </div>
</div>
