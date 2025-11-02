<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    try {
        if ($action === 'cancel') {
            $requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
            $stmt = $pdo->prepare('SELECT status FROM spid_requests WHERE id = ? AND user_id = ? LIMIT 1');
            $stmt->execute([$requestId, $user['id']]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $errorMessage = 'Richiesta non trovata.';
            } elseif ($record['status'] !== 'pending') {
                $errorMessage = 'Solo le pratiche in attesa possono essere annullate.';
            } else {
                $update = $pdo->prepare('UPDATE spid_requests SET status = ?, updated_at = NOW() WHERE id = ?');
                $update->execute(['rejected', $requestId]);
                recordAuditLog($pdo, $user['id'], 'spid.cancel', ['request_id' => $requestId]);
                $successMessage = 'Pratica annullata con successo.';
            }
        } else {
            $documentType = isset($_POST['document_type']) ? trim($_POST['document_type']) : '';
            $documentNumber = isset($_POST['document_number']) ? trim($_POST['document_number']) : '';
            $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

            if ($documentType === '' || $documentNumber === '') {
                $errorMessage = 'Inserisci i dati del documento di identità.';
            } else {
                $payload = [
                    'document_type' => $documentType,
                    'document_number' => $documentNumber,
                    'notes' => $notes,
                ];

                $insert = $pdo->prepare('INSERT INTO spid_requests (user_id, status, data, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
                $insert->execute([
                    $user['id'],
                    'pending',
                    json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

                recordAuditLog($pdo, $user['id'], 'spid.create', ['document_type' => $documentType]);
                $successMessage = 'Richiesta SPID inviata correttamente.';
            }
        }
    } catch (PDOException $exception) {
        $errorMessage = 'Errore durante il salvataggio: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
    }
}

try {
    $spidRequests = getUserSpidRequests($user['id'], $pdo);
} catch (PDOException $exception) {
    $spidRequests = [];
    $errorMessage = 'Impossibile recuperare le pratiche SPID: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1>Pratiche SPID</h1>
        <p>Gestisci le richieste di attivazione o rinnovo delle credenziali SPID/PEC.</p>

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
                <label class="form-label" for="document_type">Tipo documento</label>
                <select class="form-select" id="document_type" name="document_type" required>
                    <option value="">Seleziona</option>
                    <option value="Carta d'identità">Carta d'identità</option>
                    <option value="Passaporto">Passaporto</option>
                    <option value="Patente">Patente</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="document_number">Numero documento</label>
                <input class="form-control" type="text" id="document_number" name="document_number" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="notes">Note</label>
                <input class="form-control" type="text" id="notes" name="notes" placeholder="Informazioni aggiuntive">
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-outline-light" type="submit">Invia richiesta</button>
            </div>
        </form>

        <h2 class="h4 mb-3">Situazione pratiche</h2>
        <?php if (empty($spidRequests)): ?>
            <p class="mb-0">Non ci sono pratiche registrate.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Stato</th>
                            <th scope="col">Dettagli</th>
                            <th scope="col">Aggiornato</th>
                            <th scope="col" class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($spidRequests as $record):
                            $data = [];
                            if (!empty($record['data'])) {
                                $decoded = json_decode($record['data'], true);
                                if (is_array($decoded)) {
                                    $data = $decoded;
                                }
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo htmlspecialchars(statusBadge($record['status']), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars(ucfirst($record['status']), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (empty($data)): ?>
                                        <span class="text-muted">-</span>
                                    <?php else: ?>
                                        <ul class="mb-0">
                                            <?php foreach ($data as $key => $value): ?>
                                                <li><strong><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $key)), ENT_QUOTES, 'UTF-8'); ?>:</strong> <?php echo htmlspecialchars(is_scalar($value) ? (string) $value : json_encode($value), ENT_QUOTES, 'UTF-8'); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($record['updated_at'] ?? $record['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end">
                                    <?php if ($record['status'] === 'pending'): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="request_id" value="<?php echo (int) $record['id']; ?>">
                                            <button class="btn btn-outline-light btn-sm" type="submit" onclick="return confirm('Annullare la pratica SPID?');">Annulla</button>
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
        <small>&copy; <span data-current-year></span> Agenzia Plinio - Pratiche SPID</small>
    </div>
</footer>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/client.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
