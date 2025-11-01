<?php
declare(strict_types=1);

$pageTitle = 'Registrazione';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card-service p-4">
                <h1 class="h4 mb-3">Crea il tuo account AG Servizi</h1>
                <form method="post" action="/api/auth.php?action=register" data-async="true" class="needs-validation" novalidate>
                    <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nome e cognome</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="phone" name="phone" pattern="[0-9 +]{6,20}">
                        </div>
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-12">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="privacyCheck" required>
                        <label class="form-check-label" for="privacyCheck">
                            Accetto l'informativa privacy.
                        </label>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-accent">Completa registrazione</button>
                    </div>
                </form>
                <p class="text-muted mt-3 mb-0">Hai gi&agrave; un account? <a href="/index.php">Accedi</a></p>
            </div>
        </div>
    </div>
</div>
<script>
    const form = document.querySelector('form[data-async=true]');
    form.addEventListener('async:success', (event) => {
        window.location.href = event.detail.redirect || '/client/dashboard.php';
    });
    form.addEventListener('async:error', (event) => {
        alert(event.detail.errors ? event.detail.errors.join('\n') : 'Registrazione fallita');
    });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
