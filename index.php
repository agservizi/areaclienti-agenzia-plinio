<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

ensure_csrf_token();
$config = get_config();
$pageTitle = 'Accedi | ' . $config['app']['name'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escape($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body data-csrf="<?= escape(get_csrf_token()) ?>">
<div class="container py-5">
    <div class="row align-items-center gy-5">
        <div class="col-lg-6">
            <div class="hero-parallax mb-4">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <span class="badge-category">Area personale</span>
                    <h1 class="display-5 mt-3">Gestisci i servizi digitali AG Servizi</h1>
                    <p class="lead text-muted">Accedi alla tua area cliente per richiedere SPID, PEC, servizi di telefonia, firma digitale e molto altro.</p>
                </div>
            </div>
            <ul class="list-unstyled text-muted">
                <li class="mb-2">✔️ Gestione richieste e storico attività</li>
                <li class="mb-2">✔️ Supporto dedicato per telefonia e spedizioni</li>
                <li class="mb-2">✔️ Notifiche in tempo reale sugli aggiornamenti</li>
            </ul>
        </div>
        <div class="col-lg-5 ms-lg-auto">
            <div class="card-service p-4">
                <h2 class="h4 mb-3">Accesso area riservata</h2>
                <form method="post" action="/api/auth.php?action=login" data-async="true" class="needs-validation" novalidate>
                    <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
                    <div class="mb-3">
                        <label for="identifier" class="form-label">Email (clienti) o username (admin)</label>
                        <input type="text" class="form-control" id="identifier" name="identifier" autocomplete="username" required>
                        <small class="text-muted">Gli amministratori devono usare lo username assegnato.</small>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-accent">Accedi</button>
                    </div>
                </form>
                <p class="mt-3 mb-0 text-muted">Non hai un account? <a href="/register.php">Registrati qui</a>.</p>
            </div>
        </div>
    </div>
</div>
<script>
    document.querySelector('form[data-async=true]').addEventListener('async:success', function (event) {
        window.location.href = event.detail.redirect || '/client/dashboard.php';
    });
    document.querySelector('form[data-async=true]').addEventListener('async:error', function (event) {
        alert(event.detail.errors ? event.detail.errors.join('\n') : 'Login fallito');
    });
</script>
    <script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
