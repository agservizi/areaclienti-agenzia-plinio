<div class="card shadow-sm border-0">
    <div class="card-body">
        <h1 class="h5 mb-3">Ticket di assistenza</h1>
        <div class="accordion" id="admin-tickets">
            <?php if (empty($tickets)): ?>
                <p class="text-muted">Nessun ticket disponibile.</p>
            <?php else: ?>
                <?php foreach ($tickets as $index => $ticket): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="admin-heading-<?= $index ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admin-collapse-<?= $index ?>" aria-expanded="false" aria-controls="admin-collapse-<?= $index ?>">
                                <?= htmlspecialchars($ticket['subject'] ?? '') ?> • <?= htmlspecialchars($ticket['user_name'] ?? '') ?>
                                <span class="badge bg-primary-subtle text-primary ms-3 text-uppercase"><?= htmlspecialchars($ticket['status']) ?></span>
                            </button>
                        </h2>
                        <div id="admin-collapse-<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="admin-heading-<?= $index ?>" data-bs-parent="#admin-tickets">
                            <div class="accordion-body">
                                <?php foreach ($ticket['messages'] ?? [] as $message): ?>
                                    <div class="mb-3">
                                        <div class="small text-muted"><?= htmlspecialchars($message['from'] ?? '-') ?> • <?= htmlspecialchars($message['at'] ?? '') ?></div>
                                        <div><?= htmlspecialchars($message['content'] ?? '') ?></div>
                                    </div>
                                <?php endforeach; ?>
                                <form method="post" action="/admin/tickets/reply" class="mt-3">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $ticket['id'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label" for="reply-<?= $index ?>">Risposta</label>
                                        <textarea class="form-control" id="reply-<?= $index ?>" name="message" rows="3" required></textarea>
                                    </div>
                                    <button class="btn btn-primary" type="submit">Invia risposta</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
