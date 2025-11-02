<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$settingsSaved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Questa sezione andrà collegata alla tabella impostazioni quando disponibile.
    $settingsSaved = true;
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <h1 class="text-white mb-4">Impostazioni di Sistema</h1>
        <p>Configura i parametri principali del portale.</p>

        <?php if ($settingsSaved): ?>
            <div class="alert alert-success" role="alert">
                Impostazioni aggiornate con successo.
            </div>
        <?php endif; ?>

        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="portalName">Nome Portale</label>
                <input class="form-control" type="text" id="portalName" name="portalName" value="Portale Servizi Agenzia Plinio">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="supportEmail">Email Supporto</label>
                <input class="form-control" type="email" id="supportEmail" name="supportEmail" value="supporto@agenziaplinio.it">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="portalTheme">Tema</label>
                <select class="form-select" id="portalTheme" name="portalTheme">
                    <option value="glass" selected>Glassmorphism Blu</option>
                    <option value="dark">Dark</option>
                    <option value="light">Light</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="maintenance">Modalità Manutenzione</label>
                <select class="form-select" id="maintenance" name="maintenance">
                    <option value="off" selected>Off</option>
                    <option value="on">On</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="welcomeMessage">Messaggio di Benvenuto</label>
                <textarea class="form-control" id="welcomeMessage" name="welcomeMessage" rows="3">Benvenuto nel Portale Servizi Agenzia Plinio.</textarea>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-outline-light" type="submit">Salva Impostazioni</button>
            </div>
        </form>
    </div>
</div>
<footer class="footer-glass mt-5">
    <div class="container text-center">
        <small>&copy; <span data-current-year></span> Agenzia Plinio - Impostazioni</small>
    </div>
</footer>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/admin.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
