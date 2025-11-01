<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login(ROLE_CLIENT);

$user = current_user();
$pageTitle = 'Profilo';
$breadcrumbs = ['Profilo' => null];
$successMessage = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['_csrf_token'] ?? null)) {
        $errors[] = 'Token CSRF non valido.';
    } else {
        $name = sanitize_text($_POST['name'] ?? '');
        $phone = sanitize_text($_POST['phone'] ?? '');

        $stmt = db()->prepare('UPDATE users SET name = :name, phone = :phone WHERE id = :id');
        $stmt->execute([
            'name' => $name,
            'phone' => $phone,
            'id' => $user['id'],
        ]);
        $successMessage = 'Profilo aggiornato con successo.';
        log_event('Profile updated', ['user_id' => $user['id']]);
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav-client.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card-service p-4">
                <h2 class="h4 mb-3">Dettagli account</h2>
                <?php if ($successMessage): ?>
                    <div class="alert alert-success"><?= escape($successMessage) ?></div>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= escape($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="name">Nome e cognome</label>
                        <input class="form-control" type="text" id="name" name="name" value="<?= escape($user['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" value="<?= escape($user['email'] ?? '') ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="phone">Telefono</label>
                        <input class="form-control" type="text" id="phone" name="phone" value="<?= escape($user['phone'] ?? '') ?>">
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-accent" type="submit">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
