<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

$loginError = null;
$identifier = '';

function sanitizeLoginRecord(array $user): array
{
    return [
        'id' => $user['id'],
        'role' => $user['role'],
        'name' => $user['name'],
        'email' => $user['email'],
        'username' => $user['username'] ?? null,
        'phone' => $user['phone'] ?? null,
        'last_login_at' => $user['last_login_at'] ?? null,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($identifier === '' || $password === '') {
        $loginError = 'Inserisci email o username e password.';
    } else {
        $account = findUserByIdentifier($identifier, $pdo);

        if (!$account || !password_verify($password, $account['password'])) {
            recordLoginAttempt($pdo, $identifier, false);
            $loginError = 'Credenziali non valide.';
        } else {
            $attemptKey = $account['email'] ?? $identifier;
            recordLoginAttempt($pdo, $attemptKey, true);

            $updateLogin = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
            $updateLogin->execute([$account['id']]);

            recordAuditLog($pdo, $account['id'], 'auth.login', [
                'email' => $account['email'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            $_SESSION['user'] = sanitizeLoginRecord($account);

            $redirect = $account['role'] === 'admin' ? '../admin/dashboard.php' : '../client/dashboard.php';
            header('Location: ' . $redirect);
            exit;
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="glass-container">
                <h1 class="text-center mb-4">Accedi</h1>

                <?php if ($loginError): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label" for="identifier">Email o Username</label>
                        <input class="form-control" type="text" id="identifier" name="identifier" value="<?php echo htmlspecialchars($identifier ?? '', ENT_QUOTES, 'UTF-8'); ?>" autocomplete="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" type="password" id="password" name="password" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <a class="link-light" href="<?php echo htmlspecialchars($basePath . '/auth/forgot.php', ENT_QUOTES, 'UTF-8'); ?>">Password dimenticata?</a>
                        <button class="btn btn-outline-light" type="submit">Login</button>
                    </div>
                </form>

                <p class="mt-4 text-center">Non hai un account? <a class="link-light" href="<?php echo htmlspecialchars($basePath . '/auth/register.php', ENT_QUOTES, 'UTF-8'); ?>">Registrati</a></p>
            </div>
        </div>
    </div>
</div>
<footer class="footer-glass mt-5">
    <div class="container text-center">
        <small>&copy; <span data-current-year></span> Agenzia Plinio</small>
    </div>
</footer>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
