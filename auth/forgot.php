<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/db_connect.php';

$resetSent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if ($email !== '') {
        // Integrare con sistema email / token reset appena disponibile.
        $resetSent = true;
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="glass-container">
                <h1 class="text-center mb-4">Recupera password</h1>
                <p class="text-center">Inserisci l'email con cui ti sei registrato. Ti invieremo le istruzioni per reimpostare la password.</p>

                <?php if ($resetSent): ?>
                    <div class="alert alert-success" role="alert">
                        Se l'indirizzo risulta registrato riceverai a breve una email con le istruzioni.
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" type="email" id="email" name="email" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <a class="link-light" href="<?php echo htmlspecialchars($basePath . '/auth/login.php', ENT_QUOTES, 'UTF-8'); ?>">Torna al login</a>
                        <button class="btn btn-outline-light" type="submit">Invia</button>
                    </div>
                </form>
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
