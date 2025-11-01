<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!has_role(ROLE_ADMIN)) {
    json_response(['success' => false, 'errors' => ['Non autorizzato']], 403);
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    $search = sanitize_text($_GET['q'] ?? '');
    $query = 'SELECT id, role, name, email, phone, created_at FROM users WHERE 1=1';
    $params = [];
    if ($search !== '') {
        $query .= ' AND (email LIKE :term OR name LIKE :term)';
        $params['term'] = "%$search%";
    }
    $query .= ' ORDER BY created_at DESC LIMIT 100';
    $stmt = db()->prepare($query);
    $stmt->execute($params);
    json_response(['success' => true, 'data' => $stmt->fetchAll()]);
}

if ($method !== 'POST') {
    json_response(['success' => false, 'errors' => ['Metodo non permesso']], 405);
}

if (!validate_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? null)) {
    json_response(['success' => false, 'errors' => ['Token CSRF non valido']], 419);
}

$action = $_POST['action'] ?? '';
$userId = (int) ($_POST['id'] ?? 0);
if (!$userId) {
    json_response(['success' => false, 'errors' => ['ID utente mancante']], 422);
}

switch ($action) {
    case 'promote':
        $stmt = db()->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute(['role' => ROLE_ADMIN, 'id' => $userId]);
        log_event('User promoted to admin', ['user_id' => $userId]);
        json_response(['success' => true]);

    case 'demote':
        $stmt = db()->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute(['role' => ROLE_CLIENT, 'id' => $userId]);
        log_event('User demoted to client', ['user_id' => $userId]);
        json_response(['success' => true]);

    case 'reset_password':
        $config = get_config();
        $tempPassword = bin2hex(random_bytes(4));
        $hash = password_hash($tempPassword, $config['security']['password_algo']);
        $stmt = db()->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute(['password' => $hash, 'id' => $userId]);
        log_event('Password reset for user', ['user_id' => $userId]);
        json_response(['success' => true, 'data' => ['temp_password' => $tempPassword]]);

    default:
        json_response(['success' => false, 'errors' => ['Azione non supportata']], 400);
}
