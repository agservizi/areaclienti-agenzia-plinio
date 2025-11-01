<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login(ROLE_ADMIN);

$pageTitle = 'Gestione Servizi';
$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['_csrf_token'] ?? null)) {
        $errors[] = 'Token CSRF non valido.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $title = sanitize_text($_POST['title'] ?? '');
            $slug = sanitize_text($_POST['slug'] ?? '');
            $category = sanitize_text($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if ($title === '' || $slug === '') {
                $errors[] = 'Titolo e slug sono obbligatori.';
            } else {
                $stmt = db()->prepare('INSERT INTO services (slug, title, description, category, enabled) VALUES (:slug, :title, :description, :category, 1)');
                $stmt->execute([
                    'slug' => $slug,
                    'title' => $title,
                    'description' => $description,
                    'category' => $category,
                ]);
                $success = 'Servizio creato con successo.';
                log_event('Service created', ['slug' => $slug]);
            }
        }
        if ($action === 'toggle') {
            $serviceId = (int) ($_POST['service_id'] ?? 0);
            $enabled = (int) ($_POST['enabled'] ?? 0);
            $stmt = db()->prepare('UPDATE services SET enabled = :enabled WHERE id = :id');
            $stmt->execute(['enabled' => $enabled ? 1 : 0, 'id' => $serviceId]);
            $success = 'Servizio aggiornato.';
            log_event('Service status updated', ['service_id' => $serviceId, 'enabled' => $enabled]);
        }
    }
}

$services = db()->query('SELECT * FROM services ORDER BY created_at DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav-admin.php';
?>
<h1 class="h3 mb-4">Gestione Servizi</h1>
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
<div class="row g-5">
    <div class="col-lg-7">
        <div class="table-responsive">
            <table class="table table-dark-modern align-middle mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Titolo</th>
                    <th>Categoria</th>
                    <th>Stato</th>
                    <th class="text-end">Azioni</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td>#<?= escape((string) $service['id']) ?></td>
                        <td><?= escape($service['title']) ?></td>
                        <td><?= escape($service['category']) ?></td>
                        <td>
                            <span class="badge-status <?= $service['enabled'] ? 'completed' : 'rejected' ?>">
                                <?= $service['enabled'] ? 'Attivo' : 'Disattivo' ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <form method="post" class="d-inline">
                                <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">
                                <input type="hidden" name="enabled" value="<?= $service['enabled'] ? 0 : 1 ?>">
                                <button class="btn btn-sm btn-outline-light" type="submit">
                                    <?= $service['enabled'] ? 'Disattiva' : 'Attiva' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-service p-4">
            <h2 class="h5 mb-3">Nuovo servizio</h2>
            <form method="post">
                <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
                <input type="hidden" name="action" value="create">
                <div class="mb-3">
                    <label class="form-label" for="title">Titolo</label>
                    <input class="form-control" type="text" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="slug">Slug</label>
                    <input class="form-control" type="text" id="slug" name="slug" pattern="[a-z0-9-]{3,}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="category">Categoria</label>
                    <input class="form-control" type="text" id="category" name="category">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Descrizione</label>
                    <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                </div>
                <div class="d-grid">
                    <button class="btn btn-accent" type="submit">Salva servizio</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/nav-admin-end.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
