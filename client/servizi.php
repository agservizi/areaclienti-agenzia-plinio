<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

$categoryFilter = isset($_GET['categoria']) ? trim($_GET['categoria']) : null;
$services = [];
$servicesError = null;
$requestSuccess = null;

try {
    $services = getAllServices($pdo, true);

    if ($categoryFilter) {
        $services = array_filter($services, static function ($service) use ($categoryFilter) {
            return strtolower($service['category'] ?? '') === strtolower($categoryFilter);
        });
    }
} catch (PDOException $exception) {
    $servicesError = 'Impossibile caricare i servizi disponibili: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'errore inatteso.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;
    $details = isset($_POST['details']) ? trim($_POST['details']) : '';

    try {
        $service = $serviceId > 0 ? getServiceById($serviceId, $pdo) : null;

        if (!$service || (int) $service['enabled'] === 0) {
            $servicesError = 'Servizio non disponibile.';
        } else {
            $payload = $details !== '' ? ['note' => $details] : [];
            $insert = $pdo->prepare('INSERT INTO requests (user_id, service_id, status, data, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
            $insert->execute([
                $user['id'],
                $service['id'],
                'pending',
                $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ]);

            recordAuditLog($pdo, $user['id'], 'request.create', [
                'service' => $service['slug'],
            ]);

            $requestSuccess = 'Richiesta inviata correttamente. Riceverai aggiornamenti nella sezione Richieste.';
        }
    } catch (PDOException $exception) {
        $servicesError = 'Impossibile inviare la richiesta: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'errore inatteso.');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h1>Catalogo servizi</h1>
                <p>Consulta i servizi disponibili e invia nuove richieste.</p>
            </div>
            <form class="d-flex" method="get">
                <select class="form-select me-2" name="categoria">
                    <option value="">Tutte le categorie</option>
                    <?php
                    $categories = array_unique(array_map(static fn ($service) => $service['category'], $services));
                    sort($categories);
                    foreach ($categories as $category):
                    ?>
                        <option value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $categoryFilter === $category ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($category), ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline-light" type="submit">Filtra</button>
            </form>
        </div>

        <?php if ($requestSuccess): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($requestSuccess, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($servicesError)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($servicesError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($services)): ?>
            <p class="mb-0">Nessun servizio disponibile al momento.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($services as $service): ?>
                    <div class="col-md-4">
                        <div class="card glass-container h-100">
                            <h5><?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p class="mb-2">Categoria: <?php echo htmlspecialchars(ucfirst($service['category']), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="flex-grow-1"><?php echo htmlspecialchars($service['description'] ?? 'Descrizione non disponibile.', ENT_QUOTES, 'UTF-8'); ?></p>
                            <form method="post">
                                <input type="hidden" name="service_id" value="<?php echo (int) $service['id']; ?>">
                                <div class="mb-3">
                                    <label class="form-label" for="details-<?php echo (int) $service['id']; ?>">Dettagli (opzionali)</label>
                                    <textarea class="form-control" id="details-<?php echo (int) $service['id']; ?>" name="details" rows="2"></textarea>
                                </div>
                                <button class="btn btn-outline-light" type="submit">Richiedi</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/client.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
