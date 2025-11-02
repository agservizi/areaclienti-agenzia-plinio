<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

$registerError = null;
$registerSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($name === '' || $username === '' || $email === '' || $password === '') {
        $registerError = 'Compila tutti i campi richiesti.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = 'Inserisci un indirizzo email valido.';
    } elseif ($password !== $confirm) {
        $registerError = 'Le password non coincidono.';
    } else {
        $duplicateEmail = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $duplicateEmail->execute([$email]);

        $duplicateUsername = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $duplicateUsername->execute([$username]);

        if ($duplicateEmail->fetch()) {
            $registerError = 'Esiste già un account con questa email.';
        } elseif ($duplicateUsername->fetch()) {
            $registerError = 'Il nome utente scelto non è disponibile.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare(
                'INSERT INTO users (role, username, name, email, password, phone, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())'
            );
            $insert->execute(['client', $username, $name, $email, $hash, $phone !== '' ? $phone : null]);

            $registerSuccess = true;

            $newUserId = (int) $pdo->lastInsertId();
            recordAuditLog($pdo, $newUserId, 'auth.register', [
                'email' => $email,
                'username' => $username,
            ]);
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-container">
                <h1 class="text-center mb-4">Crea un account</h1>

                <?php if ($registerError): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($registerError, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php elseif ($registerSuccess): ?>
                    <div class="alert alert-success" role="alert">
                        Registrazione completata. Ora puoi <a class="alert-link" href="<?php echo htmlspecialchars($basePath . '/auth/login.php', ENT_QUOTES, 'UTF-8'); ?>">accedere</a>.
                    </div>
                <?php endif; ?>

                <form method="post" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Nome e Cognome</label>
                        <input class="form-control" type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="username">Nome utente</label>
                        <input class="form-control" type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Telefono</label>
                        <input class="form-control" type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" type="password" id="password" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="confirm_password">Conferma Password</label>
                        <input class="form-control" type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="col-12 text-end">
                        <button class="btn btn-outline-light" type="submit">Registrati</button>
                    </div>
                </form>

                <p class="mt-4 text-center">Hai già un account? <a class="link-light" href="<?php echo htmlspecialchars($basePath . '/auth/login.php', ENT_QUOTES, 'UTF-8'); ?>">Accedi</a></p>
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
