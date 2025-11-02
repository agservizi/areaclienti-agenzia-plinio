<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db_connect.php';
require __DIR__ . '/../includes/functions.php';

if (!isAdmin($user)) {
    header('Location: ../client/dashboard.php');
    exit;
}

$pageTitle = 'Servizi';
$adminActive = 'services';

$services = [];
$successMessage = null;
$servicesError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    try {
        if ($action === 'toggle') {
            $serviceId = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;
            $stmt = $pdo->prepare('SELECT enabled FROM services WHERE id = ? LIMIT 1');
            $stmt->execute([$serviceId]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($service) {
                $newState = (int) $service['enabled'] === 1 ? 0 : 1;
                $update = $pdo->prepare('UPDATE services SET enabled = ?, updated_at = NOW() WHERE id = ?');
                $update->execute([$newState, $serviceId]);
                recordAuditLog($pdo, $user['id'], 'service.toggle', ['service_id' => $serviceId, 'enabled' => $newState]);
                $successMessage = 'Stato servizio aggiornato.';
            }
        } else {
            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
            $category = isset($_POST['category']) ? trim($_POST['category']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';

            if ($title === '' || $slug === '' || $category === '') {
                $servicesError = 'Compila titolo, slug e categoria per creare un servizio.';
            } else {
                $slug = strtolower(preg_replace('/[^a-z0-9-]+/i', '-', $slug));
                $exists = $pdo->prepare('SELECT id FROM services WHERE slug = ? LIMIT 1');
                $exists->execute([$slug]);

                if ($exists->fetch()) {
                    $servicesError = 'Esiste già un servizio con questo slug.';
                } else {
                    $insert = $pdo->prepare('INSERT INTO services (slug, title, description, category, enabled, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
                    $insert->execute([$slug, $title, $description !== '' ? $description : null, $category, 1]);
                    recordAuditLog($pdo, $user['id'], 'service.create', ['slug' => $slug]);
                    $successMessage = 'Servizio creato con successo.';
                }
            }
        }
    } catch (PDOException $exception) {
        $servicesError = 'Errore nella gestione dei servizi: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'riprovare più tardi.');
    }
}

try {
    $services = getAllServices($pdo);
} catch (PDOException $exception) {
    $servicesError = 'Impossibile caricare i servizi: ' . ($config['APP_DEBUG'] ? $exception->getMessage() : 'errore inatteso.');
}

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-page">
    <div class="glass-container">
        <div class="admin-page-header">
            <h2 class="admin-page-title">Gestione servizi</h2>
            <p class="admin-page-subtitle">Aggiungi o abilita le offerte presenti nel catalogo.</p>
        </div>

        <?php if ($successMessage): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($servicesError): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($servicesError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="row g-3 mb-5">
            <input type="hidden" name="action" value="create">
            <div class="col-md-4">
                <label class="form-label" for="title">Titolo</label>
                <input class="form-control" type="text" id="title" name="title" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="slug">Slug</label>
                <input class="form-control" type="text" id="slug" name="slug" placeholder="es. spid-attivazione" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="category">Categoria</label>
                <input class="form-control" type="text" id="category" name="category" placeholder="es. digital-identity" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Descrizione</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-outline-light" type="submit">Crea servizio</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-glass align-middle">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Titolo</th>
                        <th scope="col">Slug</th>
                        <th scope="col">Categoria</th>
                        <th scope="col">Stato</th>
                        <th scope="col">Aggiornato</th>
                        <th scope="col" class="text-end">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Nessun servizio registrato.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($service['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><code><?php echo htmlspecialchars($service['slug'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                <td><?php echo htmlspecialchars($service['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo (int) $service['enabled'] === 1 ? 'success' : 'secondary'; ?>">
                                        <?php echo (int) $service['enabled'] === 1 ? 'Attivo' : 'Disattivato'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($service['updated_at'] ?? $service['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-end">
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="service_id" value="<?php echo (int) $service['id']; ?>">
                                        <button class="btn btn-outline-light btn-sm" type="submit">
                                            <?php echo (int) $service['enabled'] === 1 ? 'Disattiva' : 'Attiva'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
