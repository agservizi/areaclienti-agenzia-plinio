<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h4 mb-4 text-center">Crea il tuo account</h1>
                <form method="post" action="/auth/register" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="name">Nome e cognome</label>
                            <input class="form-control" type="text" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" type="email" id="email" name="email" required autocomplete="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Telefono</label>
                            <input class="form-control" type="tel" id="phone" name="phone" placeholder="Opzionale">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control" type="password" id="password" name="password" required autocomplete="new-password" minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password_confirm">Conferma password</label>
                            <input class="form-control" type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">
                        </div>
                    </div>
                    <button class="btn btn-primary w-100 mt-4" type="submit">Registrati</button>
                </form>
                <p class="text-center mt-3 mb-0">Hai già un account? <a href="/auth/login">Accedi</a></p>
            </div>
        </div>
    </div>
</div>
