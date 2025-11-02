<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

$profile = [];
$profileError = null;
$profileSuccess = null;

try {
    $profile = getUserById($user['id'], $pdo) ?? [];
} catch (PDOException $exception) {
    $profileError = 'Impossibile caricare il profilo: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'errore inatteso.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $username === '') {
        $profileError = 'Nome completo e username sono obbligatori.';
    } elseif ($newPassword !== '' && $newPassword !== $confirmPassword) {
        $profileError = 'Le password non coincidono.';
    } else {
        try {
            $duplicate = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1');
            $duplicate->execute([$username, $user['id']]);

            if ($duplicate->fetch()) {
                $profileError = 'Il nome utente è già utilizzato da un altro account.';
            } else {
                $pdo->beginTransaction();

                $params = [$name, $username, $phone !== '' ? $phone : null, $user['id']];
                $sql = 'UPDATE users SET name = ?, username = ?, phone = ?, updated_at = NOW()';

                if ($newPassword !== '') {
                    $sql .= ', password = ?';
                    $params = [$name, $username, $phone !== '' ? $phone : null, password_hash($newPassword, PASSWORD_DEFAULT), $user['id']];
                }

                $sql .= ' WHERE id = ?';

                $update = $pdo->prepare($sql);
                $update->execute($params);

                recordAuditLog($pdo, $user['id'], 'profile.update', [
                    'changed_password' => $newPassword !== '',
                ]);

                $pdo->commit();

                $profile = getUserById($user['id'], $pdo) ?? $profile;
                $_SESSION['user'] = array_merge($_SESSION['user'], [
                    'name' => $profile['name'] ?? $name,
                    'username' => $profile['username'] ?? $username,
                    'phone' => $profile['phone'] ?? $phone,
                ]);

                $profileSuccess = 'Profilo aggiornato con successo.';
            }
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $profileError = 'Errore durante l\'aggiornamento del profilo: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1>Il tuo profilo</h1>
        <p>Aggiorna i tuoi dati anagrafici e di contatto.</p>

        <?php if ($profileSuccess): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($profileSuccess, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($profileError): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($profileError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">Nome e Cognome</label>
                <input class="form-control" type="text" id="name" name="name" value="<?php echo htmlspecialchars($profile['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="username">Nome utente</label>
                <input class="form-control" type="text" id="username" name="username" value="<?php echo htmlspecialchars($profile['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" value="<?php echo htmlspecialchars($profile['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="phone">Telefono</label>
                <input class="form-control" type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="new_password">Nuova password</label>
                <input class="form-control" type="password" id="new_password" name="new_password">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="confirm_password">Conferma password</label>
                <input class="form-control" type="password" id="confirm_password" name="confirm_password">
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-outline-light" type="submit">Salva modifiche</button>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/client.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
