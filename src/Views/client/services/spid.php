<section class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">Richiesta SPID</h1>
                <form method="post" action="/client/spid/request" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="service_level">Livello di servizio</label>
                        <select class="form-select" id="service_level" name="service_level" required>
                            <option value="">Seleziona livello</option>
                            <option value="L1">Livello 1</option>
                            <option value="L2">Livello 2</option>
                            <option value="L3">Livello 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="document_number">Numero documento</label>
                        <input class="form-control" type="text" id="document_number" name="document_number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notes">Note (opzionale)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Indica eventuali richieste aggiuntive"></textarea>
                    </div>
                    <button class="btn btn-primary" type="submit">Invia richiesta</button>
                </form>
            </div>
        </div>
    </div>
</section>
