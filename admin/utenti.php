<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$pageTitle = 'Utenti';
$adminActive = 'users';

$successMessage = null;
$usersError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $newRole = $_POST['role'] ?? 'client';

    if ($userId === $user['id']) {
        $usersError = 'Non puoi modificare il tuo stesso ruolo.';
    } elseif (!in_array($newRole, ['client', 'admin'], true)) {
        $usersError = 'Ruolo non valido.';
    } else {
        try {
            $update = $pdo->prepare('UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?');
            $update->execute([$newRole, $userId]);
            recordAuditLog($pdo, $user['id'], 'user.role_change', ['target_id' => $userId, 'role' => $newRole]);
            $successMessage = 'Ruolo aggiornato correttamente.';
        } catch (PDOException $exception) {
            $usersError = 'Impossibile aggiornare il ruolo: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
        }
    }
}

try {
    $users = getAllUsers($pdo);
} catch (PDOException $exception) {
    $users = [];
    $usersError = 'Impossibile caricare gli utenti: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'errore inatteso.');
}

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page">
    <section class="admin-section">
        <div class="admin-section-header">
            <h2 class="admin-section-title">Utenti registrati</h2>
            <p class="admin-section-subtitle">Gestisci ruoli e stato degli account attivi nel portale.</p>
        </div>
        <div class="admin-section-body">
            <?php if ($successMessage): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($usersError): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($usersError, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nome</th>
                            <th scope="col">Email</th>
                            <th scope="col">Username</th>
                            <th scope="col">Ruolo</th>
                            <th scope="col">Ultimo accesso</th>
                            <th scope="col">Registrato</th>
                            <th scope="col" class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Nessun utente registrato.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($record['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($record['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($record['username'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $record['role'] === 'admin' ? 'bg-warning text-dark' : 'bg-info'; ?>">
                                            <?php echo htmlspecialchars(strtoupper($record['role']), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['last_login_at'] ? date('d/m/Y H:i', strtotime($record['last_login_at'])) : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($record['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-end">
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo (int) $record['id']; ?>">
                                            <div class="input-group input-group-sm">
                                                <select class="form-select" name="role">
                                                    <option value="client" <?php echo $record['role'] === 'client' ? 'selected' : ''; ?>>Client</option>
                                                    <option value="admin" <?php echo $record['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                                <button class="btn btn-outline-light" type="submit">Aggiorna</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
