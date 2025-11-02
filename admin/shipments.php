<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$pageTitle = 'Spedizioni';
$adminActive = 'shipments';

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipmentId = isset($_POST['shipment_id']) ? (int) $_POST['shipment_id'] : 0;
    $status = $_POST['status'] ?? '';
    $tracking = isset($_POST['tracking_code']) ? trim($_POST['tracking_code']) : '';
    $note = isset($_POST['note']) ? trim($_POST['note']) : '';
    $allowed = ['created', 'in_transit', 'delivered', 'cancelled'];

    if ($shipmentId <= 0 || !in_array($status, $allowed, true)) {
        $errorMessage = 'Dati della spedizione non validi.';
    } else {
        try {
            $pdo->beginTransaction();

            $select = $pdo->prepare('SELECT * FROM shipments WHERE id = ? LIMIT 1');
            $select->execute([$shipmentId]);
            $shipment = $select->fetch(PDO::FETCH_ASSOC);

            if (!$shipment) {
                $pdo->rollBack();
                $errorMessage = 'Spedizione non trovata.';
            } else {
                $update = $pdo->prepare('UPDATE shipments SET status = ?, tracking_code = ?, updated_at = NOW() WHERE id = ?');
                $update->execute([
                    $status,
                    $tracking !== '' ? $tracking : $shipment['tracking_code'],
                    $shipmentId,
                ]);

                if ($note !== '') {
                    $notify = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
                    $notify->execute([
                        $shipment['user_id'],
                        'Aggiornamento spedizione',
                        $note,
                    ]);
                }

                recordAuditLog($pdo, $user['id'], 'shipment.update', ['shipment_id' => $shipmentId, 'status' => $status]);
                $pdo->commit();
                $successMessage = 'Spedizione aggiornata correttamente.';
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
    $shipments = getAllShipments($pdo);
} catch (PDOException $exception) {
    $shipments = [];
    $errorMessage = 'Impossibile caricare le spedizioni: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page">
    <div class="glass-container">
        <div class="admin-page-header">
            <h2 class="admin-page-title">Spedizioni clienti</h2>
            <p class="admin-page-subtitle">Monitora le consegne e invia aggiornamenti di tracking.</p>
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

        <?php if (empty($shipments)): ?>
            <p class="mb-0">Non sono presenti spedizioni.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Tracking</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Mittente</th>
                            <th scope="col">Destinatario</th>
                            <th scope="col">Stato</th>
                            <th scope="col">Aggiornato</th>
                            <th scope="col" class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $shipment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($shipment['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($shipment['tracking_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($shipment['user_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small><?php echo htmlspecialchars($shipment['user_email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($shipment['sender'], ENT_QUOTES, 'UTF-8')); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($shipment['recipient'], ENT_QUOTES, 'UTF-8')); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo htmlspecialchars(statusBadge($shipment['status']), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($shipment['status'])), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($shipment['updated_at'] ?? $shipment['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end">
                                    <form method="post" class="row g-2 align-items-center">
                                        <input type="hidden" name="shipment_id" value="<?php echo (int) $shipment['id']; ?>">
                                        <div class="col-md-3">
                                            <label class="visually-hidden" for="status-<?php echo (int) $shipment['id']; ?>">Stato</label>
                                            <select class="form-select form-select-sm" id="status-<?php echo (int) $shipment['id']; ?>" name="status">
                                                <option value="created" <?php echo $shipment['status'] === 'created' ? 'selected' : ''; ?>>Created</option>
                                                <option value="in_transit" <?php echo $shipment['status'] === 'in_transit' ? 'selected' : ''; ?>>In transit</option>
                                                <option value="delivered" <?php echo $shipment['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $shipment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="visually-hidden" for="tracking-<?php echo (int) $shipment['id']; ?>">Tracking</label>
                                            <input class="form-control form-control-sm" type="text" id="tracking-<?php echo (int) $shipment['id']; ?>" name="tracking_code" value="<?php echo htmlspecialchars($shipment['tracking_code'], ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="visually-hidden" for="note-<?php echo (int) $shipment['id']; ?>">Nota</label>
                                            <input class="form-control form-control-sm" type="text" id="note-<?php echo (int) $shipment['id']; ?>" name="note" placeholder="Messaggio al cliente">
                                        </div>
                                        <div class="col-md-2 text-end">
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
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
