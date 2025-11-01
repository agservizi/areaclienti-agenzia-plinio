<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login(ROLE_ADMIN);

$pageTitle = 'Gestione Utenti';
$success = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['_csrf_token'] ?? null)) {
        $errors[] = 'Token CSRF non valido.';
    } else {
        $action = $_POST['action'] ?? '';
        $userId = (int) ($_POST['user_id'] ?? 0);
        if (!$userId) {
            $errors[] = 'Utente non valido.';
        } else {
            $config = get_config();
            switch ($action) {
                case 'promote':
                    $stmt = db()->prepare('UPDATE users SET role = :role WHERE id = :id');
                    $stmt->execute(['role' => ROLE_ADMIN, 'id' => $userId]);
                    $success = 'Utente promosso ad admin.';
                    log_event('User promoted', ['user_id' => $userId]);
                    break;
                case 'demote':
                    $stmt = db()->prepare('UPDATE users SET role = :role WHERE id = :id');
                    $stmt->execute(['role' => ROLE_CLIENT, 'id' => $userId]);
                    $success = 'Utente riportato a cliente.';
                    log_event('User demoted', ['user_id' => $userId]);
                    break;
                case 'reset_password':
                    $tempPassword = bin2hex(random_bytes(4));
                    $hash = password_hash($tempPassword, $config['security']['password_algo']);
                    $stmt = db()->prepare('UPDATE users SET password = :password WHERE id = :id');
                    $stmt->execute(['password' => $hash, 'id' => $userId]);
                    $success = 'Password temporanea: ' . $tempPassword;
                    log_event('Password reset', ['user_id' => $userId]);
                    break;
                default:
                    $errors[] = 'Azione non supportata.';
            }
        }
    }
}

$search = sanitize_text($_GET['q'] ?? '');
$query = 'SELECT id, role, name, email, phone, created_at FROM users WHERE 1=1';
$params = [];
if ($search) {
    $query .= ' AND (email LIKE :term OR name LIKE :term)';
    $params['term'] = "%$search%";
}
$query .= ' ORDER BY created_at DESC';
$stmt = db()->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav-admin.php';
?>
<h1 class="h3 mb-4">Gestione utenti</h1>
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

<form class="table-controls mb-4" method="get">
    <input class="form-control" type="search" name="q" placeholder="Cerca per nome o email" value="<?= escape($search) ?>">
    <button class="btn btn-outline-light" type="submit">Cerca</button>
</form>

<div class="table-responsive">
    <table class="table table-dark-modern align-middle">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Ruolo</th>
            <th>Telefono</th>
            <th>Registrato</th>
            <th class="text-end">Azioni</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td>#<?= (int) $user['id'] ?></td>
                <td><?= escape($user['name'] ?? '-') ?></td>
                <td><?= escape($user['email']) ?></td>
                <td>
                    <span class="badge-status <?= $user['role'] === ROLE_ADMIN ? 'processing' : 'completed' ?>">
                        <?= $user['role'] === ROLE_ADMIN ? 'Admin' : 'Cliente' ?>
                    </span>
                </td>
                <td><?= escape($user['phone'] ?? '-') ?></td>
                <td><?= escape(format_date($user['created_at'])) ?></td>
                <td class="text-end">
                    <form method="post" class="d-inline">
                        <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
                        <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                        <input type="hidden" name="action" value="<?= $user['role'] === ROLE_ADMIN ? 'demote' : 'promote' ?>">
                        <button class="btn btn-sm btn-outline-light" type="submit">
                            <?= $user['role'] === ROLE_ADMIN ? 'Rimuovi admin' : 'Promuovi admin' ?>
                        </button>
                    </form>
                    <form method="post" class="d-inline ms-1">
                        <input type="hidden" name="_csrf_token" value="<?= escape(get_csrf_token()) ?>">
                        <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                        <input type="hidden" name="action" value="reset_password">
                        <button class="btn btn-sm btn-outline-light" type="submit">Reset password</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/nav-admin-end.php'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
