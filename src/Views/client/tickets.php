<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h1 class="h5 mb-3">Ticket di assistenza</h1>
                <div class="accordion" id="tickets-accordion">
                    <?php if (empty($tickets)): ?>
                        <p class="text-muted">Nessun ticket aperto.</p>
                    <?php else: ?>
                        <?php foreach ($tickets as $index => $ticket): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-<?= $index ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $index ?>" aria-expanded="false" aria-controls="collapse-<?= $index ?>">
                                        <?= htmlspecialchars($ticket['subject']) ?>
                                        <span class="badge bg-primary-subtle text-primary ms-3 text-uppercase"><?= htmlspecialchars($ticket['status']) ?></span>
                                    </button>
                                </h2>
                                <div id="collapse-<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?= $index ?>" data-bs-parent="#tickets-accordion">
                                    <div class="accordion-body">
                                        <?php foreach ($ticket['messages'] ?? [] as $message): ?>
                                            <div class="ticket-message mb-3">
                                                <div class="small text-muted"><?= htmlspecialchars($message['from'] ?? 'client') ?> • <?= htmlspecialchars($message['at'] ?? '') ?></div>
                                                <div><?= htmlspecialchars($message['content'] ?? '') ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Apri un ticket</h2>
                <form method="post" action="/client/tickets" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="subject">Oggetto</label>
                        <input class="form-control" type="text" id="subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="message">Messaggio</label>
                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                    </div>
                    <button class="btn btn-primary" type="submit">Invia richiesta</button>
                </form>
            </div>
        </div>
    </div>
</div>
