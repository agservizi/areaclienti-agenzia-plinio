<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login(ROLE_CLIENT);

$pageTitle = 'Richieste';
$breadcrumbs = ['Richieste' => null];

$stmt = db()->prepare('SELECT r.*, s.title AS service_title FROM requests r JOIN services s ON r.service_id = s.id WHERE r.user_id = :user ORDER BY r.created_at DESC');
$stmt->execute(['user' => $_SESSION['auth_user_id']]);
$requests = $stmt->fetchAll();

$services = db()->query('SELECT id, slug, title FROM services WHERE enabled = 1 ORDER BY title ASC')->fetchAll();

$selectedRequest = null;
if (isset($_GET['id'])) {
    $stmtDetail = db()->prepare('SELECT r.*, s.title AS service_title, s.slug AS service_slug FROM requests r JOIN services s ON s.id = r.service_id WHERE r.user_id = :user AND r.id = :id LIMIT 1');
    $stmtDetail->execute([
        'user' => $_SESSION['auth_user_id'],
        'id' => (int) $_GET['id'],
    ]);
    $selectedRequest = $stmtDetail->fetch();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav-client.php';
?>
<div class="container">
    <div class="row g-5">
        <div class="col-lg-7">
            <h2 class="h4 mb-3">Storico richieste</h2>
            <?php $isAdmin = false; include __DIR__ . '/../templates/partials/request-table.php'; ?>
            <?php if ($selectedRequest): ?>
                <div class="card-service p-4 mt-4">
                    <h3 class="h5 mb-3">Dettaglio richiesta #<?= escape((string) $selectedRequest['id']) ?></h3>
                    <p class="text-muted mb-1">Servizio: <?= escape($selectedRequest['service_title'] ?? '-') ?></p>
                    <p class="text-muted mb-1">Stato: <span class="badge-status <?= escape($selectedRequest['status'] ?? '') ?>"><?= escape(ucfirst((string) $selectedRequest['status'])) ?></span></p>
                    <p class="text-muted mb-3">Creata il: <?= escape(format_date($selectedRequest['created_at'] ?? null)) ?></p>
                    <h4 class="h6">Dati inviati</h4>
                    <pre class="bg-dark p-3 rounded text-light small"><?= escape($selectedRequest['data']) ?></pre>
                    <?php if (!empty($selectedRequest['attachments'])): ?>
                        <h4 class="h6 mt-3">Allegati</h4>
                        <ul class="text-muted small">
                            <?php
                            try {
                                $files = json_decode($selectedRequest['attachments'], true, 512, JSON_THROW_ON_ERROR);
                            } catch (Throwable $exception) {
                                $files = [];
                            }
                            foreach ($files as $index => $file):
                                $label = $file['original'] ?? $file['filename'] ?? 'Allegato';
                                $url = sprintf('/request-download.php?request_id=%d&file=%d', (int) $selectedRequest['id'], (int) $index);
                                ?>
                                <li><a class="link-light" href="<?= $url ?>" target="_blank" rel="noopener"><?= escape($label) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-5">
            <div class="card-service p-4 mb-4">
                <h3 class="h5 mb-3">Nuova richiesta</h3>
                <form method="post" action="/api/services.php?action=request" enctype="multipart/form-data" data-async="true">
                    <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="service_slug">Servizio</label>
                        <select class="form-select" id="service_slug" name="service_slug" required>
                            <option value="">Scegli un servizio</option>
                            <?php foreach ($services as $serviceOption): ?>
                                <option value="<?= escape($serviceOption['slug']) ?>"<?= (isset($_GET['service']) && $_GET['service'] === $serviceOption['slug']) ? ' selected' : '' ?>>
                                    <?= escape($serviceOption['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notes">Note aggiuntive</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="attachments">Allegati</label>
                        <input class="form-control" type="file" id="attachments" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp">
                        <small class="text-muted">Max 5MB per file.</small>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-accent">Invia richiesta</button>
                    </div>
                </form>
            </div>

            <div class="card-service p-4">
                <h3 class="h5 mb-3">Verifica copertura operatori</h3>
                <form id="coverage-check-form" method="post" action="/api/coverage_check.php" data-async="true">
                    <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="coverage_address">Indirizzo</label>
                        <input class="form-control" type="text" id="coverage_address" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="coverage_operator">Operatore</label>
                        <select class="form-select" id="coverage_operator" name="operator" required>
                            <option value="fastweb">Fastweb</option>
                            <option value="windtre">WindTre</option>
                            <option value="iliad">Iliad</option>
                            <option value="tim">TIM</option>
                            <option value="vodafone">Vodafone</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-outline-light" type="submit">Verifica</button>
                    </div>
                </form>
                <div id="coverage-result"></div>
            </div>
        </div>
    </div>
</div>
<script>
    const requestForm = document.querySelector('form[action*="services.php"]');
    requestForm.addEventListener('async:success', (event) => {
        alert('Richiesta inviata con successo');
        window.location.reload();
    });
    requestForm.addEventListener('async:error', (event) => {
        alert(event.detail.errors ? event.detail.errors.join('\n') : 'Errore durante l\'invio');
    });
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
