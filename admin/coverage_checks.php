<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$pageTitle = 'Copertura';
$adminActive = 'coverage';

$successMessage = null;
$errorMessage = null;

try {
    $checks = getCoverageChecks($pdo);
} catch (PDOException $exception) {
    $checks = [];
    $errorMessage = 'Impossibile recuperare le verifiche copertura: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page">
    <div class="glass-container">
        <div class="admin-page-header">
            <h2 class="admin-page-title">Verifiche copertura</h2>
            <p class="admin-page-subtitle">Storico delle richieste di verifica e degli esiti ricevuti.</p>
        </div>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($checks)): ?>
            <p class="mb-0">Non sono presenti richieste di verifica.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Indirizzo</th>
                            <th scope="col">Tecnologia</th>
                            <th scope="col">Esito</th>
                            <th scope="col">Verifica</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checks as $check):
                            $result = [];
                            if (!empty($check['result'])) {
                                $decoded = json_decode($check['result'], true);
                                if (is_array($decoded)) {
                                    $result = $decoded;
                                }
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($check['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($check['user_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small><?php echo htmlspecialchars($check['user_email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($check['address'] ?? '-', ENT_QUOTES, 'UTF-8'); ?><br>
                                    <small>Lat: <?php echo htmlspecialchars($check['latitude'] ?? '-', ENT_QUOTES, 'UTF-8'); ?> | Lng: <?php echo htmlspecialchars($check['longitude'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($check['technology'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if (empty($result)): ?>
                                        <span class="text-muted">N/D</span>
                                    <?php else: ?>
                                        <pre class="mb-0 small text-white bg-dark p-2 rounded"><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'); ?></pre>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(isset($check['checked_at']) ? date('d/m/Y H:i', strtotime($check['checked_at'])) : '-', ENT_QUOTES, 'UTF-8'); ?></td>
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
