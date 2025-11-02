<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$pageTitle = 'Tentativi di accesso';
$adminActive = 'login_attempts';

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if ($email === '') {
        $errorMessage = 'Email non valida.';
    } else {
        try {
            $delete = $pdo->prepare('DELETE FROM login_attempts WHERE email = ?');
            $delete->execute([$email]);
            recordAuditLog($pdo, $user['id'], 'login_attempts.clear', ['email' => $email]);
            $successMessage = 'Tentativi azzerati per ' . $email;
        } catch (PDOException $exception) {
            $errorMessage = 'Errore durante l\'operazione: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
        }
    }
}

try {
    $attempts = getLoginAttempts($pdo);
} catch (PDOException $exception) {
    $attempts = [];
    $errorMessage = 'Impossibile recuperare i tentativi di accesso: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
}

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page">
    <div class="glass-container">
        <div class="admin-page-header">
            <h2 class="admin-page-title">Tentativi di accesso</h2>
            <p class="admin-page-subtitle">Monitora i login falliti e ripristina gli account bloccati.</p>
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

        <?php if (empty($attempts)): ?>
            <p class="mb-0">Non risultano tentativi falliti registrati.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-glass align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Email</th>
                            <th scope="col">Tentativi</th>
                            <th scope="col">Ultimo tentativo</th>
                            <th scope="col" class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attempts as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($entry['attempts'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(isset($entry['last_attempt_at']) ? date('d/m/Y H:i', strtotime($entry['last_attempt_at'])) : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end">
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($entry['email'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <button class="btn btn-outline-light btn-sm" type="submit">Azzera tentativi</button>
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
