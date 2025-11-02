<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$pageTitle = 'Richieste';
$adminActive = 'requests';

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
    $status = $_POST['status'] ?? '';

    if (!in_array($status, ['pending', 'processing', 'completed', 'rejected'], true)) {
        $errorMessage = 'Stato non valido.';
    } else {
        try {
            $update = $pdo->prepare('UPDATE requests SET status = ?, updated_at = NOW() WHERE id = ?');
            $update->execute([$status, $requestId]);
            recordAuditLog($pdo, $user['id'], 'request.update_status', ['request_id' => $requestId, 'status' => $status]);
            $successMessage = 'Stato aggiornato correttamente.';
        } catch (PDOException $exception) {
            $errorMessage = 'Errore durante l\'aggiornamento: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
        }
    }
}

try {
    $requests = getAllRequests($pdo);
} catch (PDOException $exception) {
    $requests = [];
    $errorMessage = 'Impossibile caricare le richieste: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page">
    <div class="glass-container">
        <div class="admin-page-header">
            <h2 class="admin-page-title">Richieste servizi</h2>
            <p class="admin-page-subtitle">Aggiorna gli stati delle pratiche inviate dai clienti.</p>
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

        <?php if (empty($requests)): ?>
            <p class="mb-0">Non sono presenti richieste.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Servizio</th>
                            <th scope="col">Stato</th>
                            <th scope="col">Dati</th>
                            <th scope="col">Aggiornato</th>
                            <th scope="col" class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request):
                            $data = [];
                            if (!empty($request['data'])) {
                                $decoded = json_decode($request['data'], true);
                                if (is_array($decoded)) {
                                    $data = $decoded;
                                }
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($request['user_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small><?php echo htmlspecialchars($request['user_email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($request['service_title'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo htmlspecialchars(statusBadge($request['status']), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars(ucfirst($request['status']), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (empty($data)): ?>
                                        <span class="text-muted">-</span>
                                    <?php else: ?>
                                        <ul class="mb-0">
                                            <?php foreach ($data as $key => $value): ?>
                                                <li><strong><?php echo htmlspecialchars(ucfirst((string) $key), ENT_QUOTES, 'UTF-8'); ?>:</strong> <?php echo htmlspecialchars(is_scalar($value) ? (string) $value : json_encode($value), ENT_QUOTES, 'UTF-8'); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($request['updated_at'] ?? $request['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end">
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="request_id" value="<?php echo (int) $request['id']; ?>">
                                        <select class="form-select form-select-sm d-inline w-auto" name="status">
                                            <option value="pending" <?php echo $request['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $request['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="completed" <?php echo $request['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="rejected" <?php echo $request['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                        <button class="btn btn-outline-light btn-sm" type="submit">Aggiorna</button>
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
