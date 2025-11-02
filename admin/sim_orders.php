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
    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $status = $_POST['status'] ?? '';
    $note = isset($_POST['note']) ? trim($_POST['note']) : '';
    $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    if ($orderId <= 0 || !in_array($status, $allowed, true)) {
        $errorMessage = 'Dati dell\'ordine non validi.';
    } else {
        try {
            $pdo->beginTransaction();

            $select = $pdo->prepare('SELECT * FROM sim_orders WHERE id = ? LIMIT 1');
            $select->execute([$orderId]);
            $order = $select->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $pdo->rollBack();
                $errorMessage = 'Ordine non trovato.';
            } else {
                $update = $pdo->prepare('UPDATE sim_orders SET status = ?, updated_at = NOW() WHERE id = ?');
                $update->execute([$status, $orderId]);

                if ($note !== '') {
                    $notify = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
                    $notify->execute([
                        $order['user_id'],
                        'Aggiornamento ordine SIM',
                        $note,
                    ]);
                }

                recordAuditLog($pdo, $user['id'], 'sim.update', ['order_id' => $orderId, 'status' => $status]);
                $pdo->commit();
                $successMessage = 'Ordine aggiornato correttamente.';
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
    $orders = getAllSimOrders($pdo);
} catch (PDOException $exception) {
    $orders = [];
    $errorMessage = 'Impossibile caricare gli ordini SIM: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1 class="text-white mb-4">Ordini SIM / Telefonia</h1>
        <p>Supervisiona le richieste di attivazione e aggiorna lo stato di evasione.</p>

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

        <?php if (empty($orders)): ?>
            <p class="mb-0">Non sono presenti ordini.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Cliente</th>
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
                                <td>
                                    <strong><?php echo htmlspecialchars($order['user_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small><?php echo htmlspecialchars($order['user_email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
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
                                    <form method="post" class="row g-2 align-items-center">
                                        <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                        <div class="col-md-4">
                                            <label class="visually-hidden" for="status-<?php echo (int) $order['id']; ?>">Stato</label>
                                            <select class="form-select form-select-sm" id="status-<?php echo (int) $order['id']; ?>" name="status">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="visually-hidden" for="note-<?php echo (int) $order['id']; ?>">Nota</label>
                                            <input class="form-control form-control-sm" type="text" id="note-<?php echo (int) $order['id']; ?>" name="note" placeholder="Messaggio al cliente">
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
        <small>&copy; <span data-current-year></span> Agenzia Plinio - Ordini SIM</small>
    </div>
</footer>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/admin.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
