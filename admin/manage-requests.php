<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login(ROLE_ADMIN);

$pageTitle = 'Gestione Richieste';
$success = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['_csrf_token'] ?? null)) {
        $errors[] = 'Token CSRF non valido.';
    } else {
        $requestId = (int) ($_POST['id'] ?? 0);
        $status = sanitize_text($_POST['status'] ?? '');
        $note = sanitize_text($_POST['note'] ?? '');
        if (!$requestId || !in_array($status, ['pending', 'processing', 'completed', 'rejected'], true)) {
            $errors[] = 'Parametri non validi.';
        } else {
            $stmt = db()->prepare('UPDATE requests SET status = :status, updated_at = NOW(), data = JSON_SET(COALESCE(data, JSON_OBJECT()), "$.admin_note", :note) WHERE id = :id');
            $stmt->execute([
                'status' => $status,
                'note' => $note,
                'id' => $requestId,
            ]);
            $success = 'Richiesta aggiornata.';
            log_event('Request updated from admin', ['request_id' => $requestId, 'status' => $status]);
        }
    }
}

$filters = [
    'status' => sanitize_text($_GET['status'] ?? ''),
    'service' => sanitize_text($_GET['service'] ?? ''),
];

$query = 'SELECT r.*, s.title AS service_title, s.slug AS service_slug, u.email AS user_email FROM requests r JOIN services s ON s.id = r.service_id JOIN users u ON u.id = r.user_id WHERE 1=1';
$params = [];
if ($filters['status']) {
    $query .= ' AND r.status = :status';
    $params['status'] = $filters['status'];
}
if ($filters['service']) {
    $query .= ' AND s.slug = :slug';
    $params['slug'] = $filters['service'];
}
$query .= ' ORDER BY r.created_at DESC LIMIT 100';
$stmt = db()->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

$services = db()->query('SELECT slug, title FROM services ORDER BY title')->fetchAll();
$selectedRequest = null;
if (isset($_GET['id'])) {
    $stmtDetail = db()->prepare('SELECT r.*, s.title AS service_title, s.slug AS service_slug, u.email AS user_email FROM requests r JOIN services s ON s.id = r.service_id JOIN users u ON u.id = r.user_id WHERE r.id = :id LIMIT 1');
    $stmtDetail->execute(['id' => (int) $_GET['id']]);
    $selectedRequest = $stmtDetail->fetch();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav-admin.php';
?>
<h1 class="h3 mb-4">Gestione richieste</h1>
<?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= escape($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="table-controls mb-4" method="get" data-autosubmit="true">
    <select class="form-select" name="status">
        <option value="">Stato: tutti</option>
        <?php foreach (['pending' => 'In attesa', 'processing' => 'In lavorazione', 'completed' => 'Completate', 'rejected' => 'Rifiutate'] as $value => $label): ?>
            <option value="<?= $value ?>"<?= $filters['status'] === $value ? ' selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
    <select class="form-select" name="service">
        <option value="">Servizio: tutti</option>
        <?php foreach ($services as $serviceOption): ?>
            <option value="<?= escape($serviceOption['slug']) ?>"<?= $filters['service'] === $serviceOption['slug'] ? ' selected' : '' ?>>
                <?= escape($serviceOption['title']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php $isAdmin = true; include __DIR__ . '/../templates/partials/request-table.php'; ?>

<?php if ($selectedRequest): ?>
    <div class="card-service p-4 mt-5">
        <h2 class="h5 mb-3">Dettaglio richiesta #<?= escape((string) $selectedRequest['id']) ?></h2>
        <p class="text-muted mb-1">Utente: <?= escape($selectedRequest['user_email']) ?></p>
        <p class="text-muted mb-1">Servizio: <?= escape($selectedRequest['service_title']) ?></p>
        <p class="text-muted">Stato attuale: <span class="badge-status <?= escape($selectedRequest['status']) ?>"><?= escape($selectedRequest['status']) ?></span></p>
        <h3 class="h6 mt-4">Dati inviati</h3>
        <pre class="bg-dark p-3 rounded text-light small"><?= escape($selectedRequest['data']) ?></pre>
        <?php if ($selectedRequest['attachments']): ?>
            <h3 class="h6 mt-3">Allegati</h3>
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
        <form method="post" class="mt-4">
            <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) $selectedRequest['id'] ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="status">Stato</label>
                    <select class="form-select" id="status" name="status" required>
                        <?php foreach (['pending', 'processing', 'completed', 'rejected'] as $statusOption): ?>
                            <option value="<?= $statusOption ?>"<?= $selectedRequest['status'] === $statusOption ? ' selected' : '' ?>><?= ucfirst($statusOption) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="note">Nota interna</label>
                    <input class="form-control" type="text" id="note" name="note" value="<?= escape($selectedRequest['admin_note'] ?? '') ?>">
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-accent" type="submit">Aggiorna richiesta</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/nav-admin-end.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
