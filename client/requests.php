<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'cancel') {
        $requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;

        if ($requestId <= 0) {
            $errorMessage = 'Richiesta non valida.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT status FROM requests WHERE id = ? AND user_id = ? LIMIT 1');
                $stmt->execute([$requestId, $user['id']]);
                $record = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$record) {
                    $errorMessage = 'Richiesta non trovata.';
                } elseif ($record['status'] !== 'pending') {
                    $errorMessage = 'Solo le richieste in attesa possono essere annullate.';
                } else {
                    $update = $pdo->prepare('UPDATE requests SET status = ?, updated_at = NOW() WHERE id = ?');
                    $update->execute(['rejected', $requestId]);

                    recordAuditLog($pdo, $user['id'], 'request.cancel', ['request_id' => $requestId]);
                    $successMessage = 'Richiesta annullata con successo.';
                }
            } catch (PDOException $exception) {
                $errorMessage = 'Errore durante l\'annullamento: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
            }
        }
    }
}

try {
    $requests = getUserRequests($user['id'], $pdo);
} catch (PDOException $exception) {
    $requests = [];
    $errorMessage = 'Impossibile recuperare le richieste: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Richieste servizi</h1>
                <p>Monitora lo stato delle tue pratiche o avvia una nuova richiesta dal catalogo servizi.</p>
            </div>
            <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/client/servizi.php', ENT_QUOTES, 'UTF-8'); ?>">Nuova richiesta</a>
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
            <p class="mb-0">Non hai ancora inviato richieste. Visita la sezione servizi per iniziare.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Servizio</th>
                            <th scope="col">Stato</th>
                            <th scope="col">Dettagli</th>
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
                                <td><?php echo htmlspecialchars($request['service_title'] ?? 'Servizio', ENT_QUOTES, 'UTF-8'); ?></td>
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
                                                <li><strong><?php echo htmlspecialchars(is_string($key) ? ucfirst($key) : 'Nota', ENT_QUOTES, 'UTF-8'); ?>:</strong> <?php echo htmlspecialchars(is_scalar($value) ? (string) $value : json_encode($value), ENT_QUOTES, 'UTF-8'); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($request['updated_at'] ?? $request['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end">
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="request_id" value="<?php echo (int) $request['id']; ?>">
                                            <button class="btn btn-outline-light btn-sm" type="submit" onclick="return confirm('Vuoi annullare questa richiesta?');">Annulla</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
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
<footer class="footer-glass mt-5">
    <div class="container text-center">
        <small>&copy; <span data-current-year></span> Agenzia Plinio - Richieste</small>
    </div>
</footer>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/client.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
