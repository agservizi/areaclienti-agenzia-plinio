<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notificationId = isset($_POST['notification_id']) ? (int) $_POST['notification_id'] : 0;

    if ($notificationId > 0) {
        try {
            $update = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
            $update->execute([$notificationId, $user['id']]);
            $successMessage = 'Notifica segnata come letta.';
        } catch (PDOException $exception) {
            $errorMessage = 'Errore nell\'aggiornamento: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
        }
    }
}

try {
    $notifications = getUserNotifications($user['id'], $pdo);
} catch (PDOException $exception) {
    $notifications = [];
    $errorMessage = 'Impossibile caricare le notifiche: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1>Notifiche</h1>
        <p>Messaggi e aggiornamenti inviati dall'amministrazione del portale.</p>

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

        <?php if (empty($notifications)): ?>
            <p class="mb-0">Non ci sono notifiche da mostrare.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item list-group-item-dark mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">
                                    <?php echo htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if ((int) $notification['is_read'] === 0): ?>
                                        <span class="badge bg-info ms-2">Nuova</span>
                                    <?php endif; ?>
                                </h5>
                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($notification['body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
                                <small>Inviata il <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($notification['created_at'])), ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <?php if ((int) $notification['is_read'] === 0): ?>
                                <form method="post">
                                    <input type="hidden" name="notification_id" value="<?php echo (int) $notification['id']; ?>">
                                    <button class="btn btn-outline-light btn-sm" type="submit">Segna come letta</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
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
