<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h4 mb-4 text-center">Accedi al portale</h1>
                <form method="post" action="/auth/login" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="identifier">Email o username</label>
                        <input class="form-control" type="text" id="identifier" name="identifier" required autocomplete="username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Accedi</button>
                </form>
                <p class="text-center mt-3 mb-0">Non hai un account? <a href="/auth/register">Registrati</a></p>
            </div>
        </div>
    </div>
</div>
