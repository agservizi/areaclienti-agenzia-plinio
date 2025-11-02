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
            $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;

            $stmt = $pdo->prepare('SELECT status FROM sim_orders WHERE id = ? AND user_id = ? LIMIT 1');
            $stmt->execute([$orderId, $user['id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $errorMessage = 'Ordine non trovato.';
            } elseif ($order['status'] !== 'pending' && $order['status'] !== 'processing') {
                $errorMessage = 'Questo ordine non può essere annullato.';
            } else {
                $update = $pdo->prepare('UPDATE sim_orders SET status = ?, updated_at = NOW() WHERE id = ?');
                $update->execute(['cancelled', $orderId]);
                recordAuditLog($pdo, $user['id'], 'sim.cancel', ['order_id' => $orderId]);
                $successMessage = 'Ordine annullato con successo.';
            }
        } else {
            $operator = $_POST['operator'] ?? '';
            $plan = isset($_POST['plan']) ? trim($_POST['plan']) : '';
            $details = isset($_POST['details']) ? trim($_POST['details']) : '';

            if (!in_array($operator, ['WindTre', 'Fastweb', 'Iliad'], true) || $plan === '') {
                $errorMessage = 'Compila tutti i campi richiesti.';
            } else {
                $insert = $pdo->prepare('INSERT INTO sim_orders (user_id, operator, plan, status, details, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
                $insert->execute([$user['id'], $operator, $plan, 'pending', $details !== '' ? $details : null]);
                recordAuditLog($pdo, $user['id'], 'sim.create', ['operator' => $operator]);
                $successMessage = 'Ordine SIM inviato correttamente.';
            }
        }
    } catch (PDOException $exception) {
        $errorMessage = 'Errore durante il salvataggio: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
    }
}

try {
    $orders = getUserSimOrders($user['id'], $pdo);
} catch (PDOException $exception) {
    $orders = [];
    $errorMessage = 'Impossibile recuperare gli ordini SIM: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1>Ordini SIM & Telefonia</h1>
        <p>Richiedi l'attivazione di una nuova SIM o monitora le richieste in corso.</p>

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
                <label class="form-label" for="operator">Operatore</label>
                <select class="form-select" id="operator" name="operator" required>
                    <option value="">Seleziona</option>
                    <option value="WindTre">WindTre</option>
                    <option value="Fastweb">Fastweb</option>
                    <option value="Iliad">Iliad</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="plan">Piano desiderato</label>
                <input class="form-control" type="text" id="plan" name="plan" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="details">Dettagli (opzionali)</label>
                <input class="form-control" type="text" id="details" name="details" placeholder="Numero da portare, note...">
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-outline-light" type="submit">Invia richiesta</button>
            </div>
        </form>

        <h2 class="h4 mb-3">Le tue richieste SIM</h2>
        <?php if (empty($orders)): ?>
            <p class="mb-0">Non hai ancora richiesto alcuna SIM.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Operatore</th>
                            <th scope="col">Piano</th>
                            <th scope="col">Stato</th>
                            <th scope="col">Dettagli</th>
                            <th scope="col">Aggiornato</th>
                            <th scope="col" class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($order['operator'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($order['plan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo htmlspecialchars(statusBadge($order['status']), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars(ucfirst($order['status']), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($order['details'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['updated_at'] ?? $order['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end">
                                    <?php if (in_array($order['status'], ['pending', 'processing'], true)): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                            <button class="btn btn-outline-light btn-sm" type="submit" onclick="return confirm('Annullare la richiesta?');">Annulla</button>
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
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/client.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
