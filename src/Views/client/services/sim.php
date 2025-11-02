<section class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">Richiesta attivazione SIM</h1>
                <form method="post" action="/client/sim/request" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="operator">Operatore</label>
                        <select class="form-select" id="operator" name="operator" required>
                            <option value="">Seleziona operatore</option>
                            <option value="WindTre">WindTre</option>
                            <option value="Fastweb">Fastweb</option>
                            <option value="Iliad">Iliad</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="plan">Piano</label>
                        <input class="form-control" type="text" id="plan" name="plan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notes">Note aggiuntive</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Indica eventuali esigenze specifiche"></textarea>
                    </div>
                    <button class="btn btn-primary" type="submit">Invia richiesta</button>
                </form>
            </div>
        </div>
    </div>
</section>
