<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$pageTitle = 'Notifiche';
$adminActive = 'notifications';

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    try {
        if ($action === 'create') {
            $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';

            if ($userId <= 0 || $title === '' || $message === '') {
                $errorMessage = 'Compila destinatario, titolo e messaggio.';
            } else {
                $insert = $pdo->prepare('INSERT INTO notifications (user_id, title, message, is_read) VALUES (?, ?, ?, 0)');
                $insert->execute([$userId, $title, $message]);
                recordAuditLog($pdo, $user['id'], 'notification.create', ['user_id' => $userId]);
                $successMessage = 'Notifica inviata correttamente.';
            }
        } elseif ($action === 'toggle') {
            $notificationId = isset($_POST['notification_id']) ? (int) $_POST['notification_id'] : 0;
            $status = isset($_POST['is_read']) ? (int) $_POST['is_read'] : 0;

            if ($notificationId <= 0) {
                $errorMessage = 'Notifica non valida.';
            } else {
                $update = $pdo->prepare('UPDATE notifications SET is_read = ?, read_at = CASE WHEN ? = 1 THEN NOW() ELSE NULL END WHERE id = ?');
                $update->execute([$status, $status, $notificationId]);
                recordAuditLog($pdo, $user['id'], 'notification.toggle', ['notification_id' => $notificationId, 'is_read' => $status]);
                $successMessage = 'Stato aggiornato.';
            }
        }
    } catch (PDOException $exception) {
        $errorMessage = 'Errore durante l\'operazione: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
    }
}

try {
    $notifications = getAllNotifications($pdo);
    $users = getAllUsers($pdo);
} catch (PDOException $exception) {
    $notifications = [];
    $users = [];
    $errorMessage = 'Impossibile recuperare i dati: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page">
    <div class="glass-container">
        <div class="admin-page-header">
            <h2 class="admin-page-title">Notifiche clienti</h2>
            <p class="admin-page-subtitle">Invia comunicazioni e tieni traccia della lettura.</p>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="row g-3 mb-5">
            <input type="hidden" name="action" value="create">
            <div class="col-md-4">
                <label class="form-label" for="user_id">Destinatario</label>
                <select class="form-select" id="user_id" name="user_id" required>
                    <option value="">Seleziona utente</option>
                    <?php foreach ($users as $record): ?>
                        <option value="<?php echo (int) $record['id']; ?>"><?php echo htmlspecialchars($record['name'] ?? $record['email'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($record['email'], ENT_QUOTES, 'UTF-8'); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="title">Titolo</label>
                <input class="form-control" type="text" id="title" name="title" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="message">Messaggio</label>
                <input class="form-control" type="text" id="message" name="message" required>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-outline-light" type="submit">Invia</button>
            </div>
        </form>

        <?php if (empty($notifications)): ?>
            <p class="mb-0">Non sono presenti notifiche.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Titolo</th>
                            <th scope="col">Messaggio</th>
                            <th scope="col">Stato</th>
                            <th scope="col">Inviata</th>
                            <th scope="col" class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['user_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small><?php echo htmlspecialchars($item['user_email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($item['message'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo (int) ($item['is_read'] ?? 0) === 1 ? 'success' : 'warning'; ?>">
                                        <?php echo (int) ($item['is_read'] ?? 0) === 1 ? 'Letta' : 'Da leggere'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(isset($item['created_at']) ? date('d/m/Y H:i', strtotime($item['created_at'])) : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end">
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="notification_id" value="<?php echo (int) $item['id']; ?>">
                                        <input type="hidden" name="is_read" value="<?php echo (int) ($item['is_read'] ?? 0) === 1 ? 0 : 1; ?>">
                                        <button class="btn btn-outline-light btn-sm" type="submit">
                                            Segna come <?php echo (int) ($item['is_read'] ?? 0) === 1 ? 'non letto' : 'letto'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
