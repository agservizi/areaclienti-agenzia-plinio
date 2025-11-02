<section class="row g-4">
    <div class="col-lg-4">
        <div class="card service-card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Identità digitale</h2>
                <p class="text-muted">Richiedi SPID, PEC e firma digitale con supporto dell'agenzia.</p>
                <a class="btn btn-primary" href="/client/spid/request">Richiedi SPID</a>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card service-card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Telefonia</h2>
                <p class="text-muted">Attiva SIM e servizi con i principali operatori nazionali.</p>
                <a class="btn btn-primary" href="/client/sim/request">Richiedi SIM</a>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card service-card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Verifica copertura rete</h2>
                <p class="text-muted">Controlla rapidamente la copertura e la tecnologia disponibile al tuo indirizzo.</p>
                <form class="mt-3" method="post" action="/client/coverage/check" id="coverage-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-2">
                        <label class="form-label" for="coverage-address">Indirizzo</label>
                        <input class="form-control" type="text" id="coverage-address" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="coverage-operator">Operatore</label>
                        <select class="form-select" id="coverage-operator" name="operator" required>
                            <option value="">Seleziona</option>
                            <option value="WindTre">WindTre</option>
                            <option value="Fastweb">Fastweb</option>
                            <option value="Iliad">Iliad</option>
                        </select>
                    </div>
                    <button class="btn btn-outline-primary w-100" type="submit">Verifica copertura</button>
                </form>
            </div>
        </div>
    </div>
</section>
