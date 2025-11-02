<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
    $status = $_POST['status'] ?? '';
    $note = isset($_POST['note']) ? trim($_POST['note']) : '';
    $allowed = ['pending', 'in_review', 'approved', 'rejected'];

    if ($requestId <= 0 || !in_array($status, $allowed, true)) {
        $errorMessage = 'Dati non validi per l\'aggiornamento.';
    } else {
        try {
            $pdo->beginTransaction();

            $select = $pdo->prepare('SELECT * FROM spid_requests WHERE id = ? LIMIT 1');
            $select->execute([$requestId]);
            $record = $select->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $pdo->rollBack();
                $errorMessage = 'Pratica non trovata.';
            } else {
                $data = [];
                if (!empty($record['data'])) {
                    $decoded = json_decode($record['data'], true);
                    if (is_array($decoded)) {
                        $data = $decoded;
                    }
                }

                if ($note !== '') {
                    $data['admin_note'] = $note;
                }

                $update = $pdo->prepare('UPDATE spid_requests SET status = ?, data = ?, updated_at = NOW() WHERE id = ?');
                $update->execute([
                    $status,
                    !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                    $requestId,
                ]);

                if ($note !== '') {
                    $notify = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
                    $notify->execute([
                        $record['user_id'],
                        'Aggiornamento pratica SPID',
                        $note,
                    ]);
                }

                recordAuditLog($pdo, $user['id'], 'spid.update', ['request_id' => $requestId, 'status' => $status]);

                $pdo->commit();
                $successMessage = 'Pratica aggiornata correttamente.';
            }
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMessage = 'Errore durante l\'aggiornamento: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
        }
    }
}

try {
    $spidRequests = getAllSpidRequests($pdo);
} catch (PDOException $exception) {
    $spidRequests = [];
    $errorMessage = 'Impossibile caricare le pratiche SPID: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1 class="text-white mb-4">Pratiche SPID</h1>
        <p>Coordina le richieste SPID/PEC e aggiorna lo stato delle pratiche.</p>

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

        <?php if (empty($spidRequests)): ?>
            <p class="mb-0">Non ci sono pratiche registrate.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Cliente</th>
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
                                    <strong><?php echo htmlspecialchars($record['user_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small><?php echo htmlspecialchars($record['user_email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo htmlspecialchars(statusBadge($record['status']), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $record['status'])), ENT_QUOTES, 'UTF-8'); ?>
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
                                    <form method="post" class="row g-2 align-items-center">
                                        <input type="hidden" name="request_id" value="<?php echo (int) $record['id']; ?>">
                                        <div class="col-md-4">
                                            <label class="visually-hidden" for="status-<?php echo (int) $record['id']; ?>">Stato</label>
                                            <select class="form-select form-select-sm" id="status-<?php echo (int) $record['id']; ?>" name="status">
                                                <option value="pending" <?php echo $record['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in_review" <?php echo $record['status'] === 'in_review' ? 'selected' : ''; ?>>In review</option>
                                                <option value="approved" <?php echo $record['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="rejected" <?php echo $record['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="visually-hidden" for="note-<?php echo (int) $record['id']; ?>">Nota</label>
                                            <input class="form-control form-control-sm" type="text" id="note-<?php echo (int) $record['id']; ?>" name="note" placeholder="Messaggio al cliente">
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <button class="btn btn-outline-light btn-sm" type="submit">Salva</button>
                                        </div>
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
<footer class="footer-glass mt-5">
    <div class="container text-center">
        <small>&copy; <span data-current-year></span> Agenzia Plinio - Pratiche SPID</small>
    </div>
</footer>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/admin.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
