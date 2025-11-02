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
            $shipmentId = isset($_POST['shipment_id']) ? (int) $_POST['shipment_id'] : 0;

            $stmt = $pdo->prepare('SELECT status FROM shipments WHERE id = ? AND user_id = ? LIMIT 1');
            $stmt->execute([$shipmentId, $user['id']]);
            $shipment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$shipment) {
                $errorMessage = 'Spedizione non trovata.';
            } elseif (!in_array($shipment['status'], ['created', 'in_transit'], true)) {
                $errorMessage = 'Non è possibile annullare questa spedizione.';
            } else {
                $update = $pdo->prepare('UPDATE shipments SET status = ?, updated_at = NOW() WHERE id = ?');
                $update->execute(['cancelled', $shipmentId]);
                recordAuditLog($pdo, $user['id'], 'shipment.cancel', ['shipment_id' => $shipmentId]);
                $successMessage = 'Spedizione annullata.';
            }
        } else {
            $sender = isset($_POST['sender']) ? trim($_POST['sender']) : '';
            $recipient = isset($_POST['recipient']) ? trim($_POST['recipient']) : '';
            $weight = isset($_POST['weight']) ? (float) $_POST['weight'] : 0.0;
            $dimensions = isset($_POST['dimensions']) ? trim($_POST['dimensions']) : null;

            if ($sender === '' || $recipient === '') {
                $errorMessage = 'Compila mittente e destinatario.';
            } else {
                $tracking = 'SHP-' . strtoupper(bin2hex(random_bytes(4)));

                $insert = $pdo->prepare('INSERT INTO shipments (user_id, tracking_code, sender, recipient, weight, dimensions, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
                $insert->execute([
                    $user['id'],
                    $tracking,
                    $sender,
                    $recipient,
                    $weight,
                    $dimensions !== '' ? $dimensions : null,
                    'created',
                ]);

                recordAuditLog($pdo, $user['id'], 'shipment.create', ['tracking' => $tracking]);
                $successMessage = 'Spedizione registrata. Codice tracking: ' . $tracking;
            }
        }
    } catch (PDOException $exception) {
        $errorMessage = 'Errore durante il salvataggio: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
    }
}

try {
    $shipments = getUserShipments($user['id'], $pdo);
} catch (PDOException $exception) {
    $shipments = [];
    $errorMessage = 'Impossibile caricare le spedizioni: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1>Spedizioni</h1>
        <p>Prenota nuovi invii o controlla lo stato delle spedizioni in corso.</p>

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
            <div class="col-md-6">
                <label class="form-label" for="sender">Mittente</label>
                <textarea class="form-control" id="sender" name="sender" rows="3" required></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="recipient">Destinatario</label>
                <textarea class="form-control" id="recipient" name="recipient" rows="3" required></textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="weight">Peso (Kg)</label>
                <input class="form-control" type="number" step="0.1" id="weight" name="weight" value="1">
            </div>
            <div class="col-md-9">
                <label class="form-label" for="dimensions">Dimensioni</label>
                <input class="form-control" type="text" id="dimensions" name="dimensions" placeholder="es. 30x40x20 cm">
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-outline-light" type="submit">Registra spedizione</button>
            </div>
        </form>

        <h2 class="h4 mb-3">Le tue spedizioni</h2>
        <?php if (empty($shipments)): ?>
            <p class="mb-0">Non ci sono spedizioni registrate.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Tracking</th>
                            <th scope="col">Stato</th>
                            <th scope="col">Mittente</th>
                            <th scope="col">Destinatario</th>
                            <th scope="col">Peso</th>
                            <th scope="col">Aggiornato</th>
                            <th scope="col" class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $shipment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($shipment['tracking_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo htmlspecialchars(statusBadge($shipment['status']), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($shipment['status'])), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($shipment['sender'], ENT_QUOTES, 'UTF-8')); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($shipment['recipient'], ENT_QUOTES, 'UTF-8')); ?></td>
                                <td><?php echo htmlspecialchars(number_format((float) $shipment['weight'], 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?> kg</td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($shipment['updated_at'] ?? $shipment['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end">
                                    <?php if (in_array($shipment['status'], ['created', 'in_transit'], true)): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="shipment_id" value="<?php echo (int) $shipment['id']; ?>">
                                            <button class="btn btn-outline-light btn-sm" type="submit" onclick="return confirm('Vuoi annullare questa spedizione?');">Annulla</button>
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
