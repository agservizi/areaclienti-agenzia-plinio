<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$pageTitle = 'Audit';
$adminActive = 'audit';

$successMessage = null;
$errorMessage = null;

$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 100;
if ($limit <= 0 || $limit > 500) {
    $limit = 100;
}

try {
    $logs = getAuditLogs($pdo, $limit);
} catch (PDOException $exception) {
    $logs = [];
    $errorMessage = 'Impossibile recuperare i log: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page">
    <div class="glass-container">
        <div class="admin-page-header">
            <h2 class="admin-page-title">Audit trail</h2>
            <p class="admin-page-subtitle">Storico delle operazioni effettuate dagli utenti sul portale.</p>
        </div>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="get" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label" for="limit">Numero record</label>
                <select class="form-select" id="limit" name="limit" onchange="this.form.submit()">
                    <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100</option>
                    <option value="250" <?php echo $limit === 250 ? 'selected' : ''; ?>>250</option>
                    <option value="500" <?php echo $limit === 500 ? 'selected' : ''; ?>>500</option>
                </select>
            </div>
        </form>

        <?php if (empty($logs)): ?>
            <p class="mb-0">Nessun log disponibile.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Timestamp</th>
                            <th scope="col">Utente</th>
                            <th scope="col">Azione</th>
                            <th scope="col">Dettagli</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log):
                            $payload = [];
                            if (!empty($log['payload'])) {
                                $decoded = json_decode($log['payload'], true);
                                if (is_array($decoded)) {
                                    $payload = $decoded;
                                }
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($log['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($log['user_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small><?php echo htmlspecialchars($log['user_email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td><code><?php echo htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                <td>
                                    <?php if (empty($payload)): ?>
                                        <span class="text-muted">-</span>
                                    <?php else: ?>
                                        <pre class="mb-0 small text-white bg-dark p-2 rounded"><?php echo htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'); ?></pre>
                                    <?php endif; ?>
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
