<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/reporting.php';
require_login(ROLE_ADMIN);

$pageTitle = 'Reportistica';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    if (!validate_csrf_token($_POST['_csrf_token'] ?? null)) {
        $errors[] = 'Token CSRF non valido per l\'esportazione.';
    } else {
        $format = sanitize_text($_POST['format'] ?? 'csv');
        $filters = [
            'status' => sanitize_text($_POST['status'] ?? ''),
            'service' => sanitize_text($_POST['service'] ?? ''),
            'date_from' => sanitize_text($_POST['date_from'] ?? ''),
            'date_to' => sanitize_text($_POST['date_to'] ?? ''),
        ];
        $report = fetch_requests_report($filters);

        try {
            if ($format === 'csv') {
                $csv = generate_requests_csv($report['rows'], $filters);
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="report-richieste-' . date('Ymd-His') . '.csv"');
                echo $csv;
                exit;
            }
            if ($format === 'pdf') {
                $pdf = generate_requests_pdf($report['rows'], $filters);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="report-richieste-' . date('Ymd-His') . '.pdf"');
                echo $pdf;
                exit;
            }
            $errors[] = 'Formato export non riconosciuto.';
        } catch (Throwable $exception) {
            $errors[] = 'Esportazione fallita: ' . $exception->getMessage();
            log_event('Report export failed', ['error' => $exception->getMessage()], 'error');
        }
    }
}

$filters = [
    'status' => sanitize_text($_GET['status'] ?? ''),
    'service' => sanitize_text($_GET['service'] ?? ''),
    'date_from' => sanitize_text($_GET['date_from'] ?? ''),
    'date_to' => sanitize_text($_GET['date_to'] ?? ''),
];

$reportData = fetch_requests_report($filters);
$services = db()->query('SELECT slug, title FROM services ORDER BY title')->fetchAll();
$previewRows = array_slice($reportData['rows'], 0, 50);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav-admin.php';
?>
<h1 class="h3 mb-4">Reportistica richieste</h1>
<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= escape($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="table-controls mb-4" method="get">
    <input class="form-control" type="date" name="date_from" value="<?= escape($filters['date_from']) ?>" placeholder="Dal">
    <input class="form-control" type="date" name="date_to" value="<?= escape($filters['date_to']) ?>" placeholder="Al">
    <select class="form-select" name="status">
        <option value="">Stato: tutti</option>
        <?php foreach (['pending' => 'In attesa', 'processing' => 'In lavorazione', 'completed' => 'Completate', 'rejected' => 'Rifiutate'] as $value => $label): ?>
            <option value="<?= $value ?>"<?= $filters['status'] === $value ? ' selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
    <select class="form-select" name="service">
        <option value="">Servizio: tutti</option>
        <?php foreach ($services as $service): ?>
            <option value="<?= escape($service['slug']) ?>"<?= $filters['service'] === $service['slug'] ? ' selected' : '' ?>>
                <?= escape($service['title']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-outline-light" type="submit">Aggiorna</button>
</form>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="stat-card">
            <p class="text-muted mb-1">Totale richieste</p>
            <h2 class="display-6 mb-0"><?= $reportData['totals']['total'] ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <p class="text-muted mb-1">In attesa</p>
            <h2 class="display-6 mb-0"><?= $reportData['totals']['pending'] ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <p class="text-muted mb-1">In lavorazione</p>
            <h2 class="display-6 mb-0"><?= $reportData['totals']['processing'] ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <p class="text-muted mb-1">Completate</p>
            <h2 class="display-6 mb-0"><?= $reportData['totals']['completed'] ?></h2>
        </div>
    </div>
</div>

<div class="card-service p-4 mb-5">
    <h2 class="h5 mb-3">Servizi piu richiesti</h2>
    <?php if ($reportData['services']): ?>
        <ol class="ps-3 mb-0">
            <?php foreach (array_slice($reportData['services'], 0, 5) as $service): ?>
                <li><?= escape($service['title']) ?> &ndash; <?= (int) $service['count'] ?> richieste</li>
            <?php endforeach; ?>
        </ol>
    <?php else: ?>
        <p class="text-muted mb-0">Nessun dato disponibile per i filtri selezionati.</p>
    <?php endif; ?>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Anteprima richieste (max 50 righe)</h2>
    <form method="post" class="d-flex align-items-center gap-2">
        <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
        <input type="hidden" name="status" value="<?= escape($filters['status']) ?>">
        <input type="hidden" name="service" value="<?= escape($filters['service']) ?>">
        <input type="hidden" name="date_from" value="<?= escape($filters['date_from']) ?>">
        <input type="hidden" name="date_to" value="<?= escape($filters['date_to']) ?>">
        <input type="hidden" name="export" value="1">
        <button class="btn btn-sm btn-outline-light" type="submit" name="format" value="csv">Esporta CSV</button>
        <button class="btn btn-sm btn-outline-light" type="submit" name="format" value="pdf">Esporta PDF</button>
    </form>
</div>

<div class="table-responsive mb-5">
    <table class="table table-dark-modern align-middle">
        <thead>
        <tr>
            <th>ID</th>
            <th>Utente</th>
            <th>Servizio</th>
            <th>Stato</th>
            <th>Creata</th>
            <th>Aggiornata</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$previewRows): ?>
            <tr>
                <td colspan="6">
                    <div class="placeholder-card">Nessuna richiesta trovata.</div>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($previewRows as $row): ?>
                <tr>
                    <td>#<?= (int) $row['id'] ?></td>
                    <td><?= escape($row['user_email'] ?? '-') ?></td>
                    <td><?= escape($row['service_title'] ?? '-') ?></td>
                    <td><span class="badge-status <?= escape($row['status'] ?? '') ?>"><?= escape(ucfirst((string) $row['status'])) ?></span></td>
                    <td><?= escape(format_date($row['created_at'] ?? null)) ?></td>
                    <td><?= escape(format_date($row['updated_at'] ?? null)) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/nav-admin-end.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
