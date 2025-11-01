<?php
declare(strict_types=1);

$pageTitle = 'Accedi';
require_once __DIR__ . '/includes/header.php';
?>
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
                <h2 class="h4 mb-3">Accesso clienti</h2>
                <form method="post" action="/api/auth.php?action=login" data-async="true" class="needs-validation" novalidate>
                    <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
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
<?php require_once __DIR__ . '/includes/footer.php'; ?>
