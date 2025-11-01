<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!has_role(ROLE_ADMIN)) {
    json_response(['success' => false, 'errors' => ['Non autorizzato']], 403);
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    $status = sanitize_text($_GET['status'] ?? '');
    $service = sanitize_text($_GET['service'] ?? '');
    $query = 'SELECT r.*, s.title AS service_title, u.email AS user_email FROM requests r JOIN services s ON r.service_id = s.id JOIN users u ON r.user_id = u.id WHERE 1=1';
    $params = [];
    if ($status !== '') {
        $query .= ' AND r.status = :status';
        $params['status'] = $status;
    }
    if ($service !== '') {
        $query .= ' AND s.slug = :service';
        $params['service'] = $service;
    }
    $query .= ' ORDER BY r.created_at DESC LIMIT 100';

    $stmt = db()->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    json_response(['success' => true, 'data' => $rows]);
}

if ($method !== 'POST') {
    json_response(['success' => false, 'errors' => ['Metodo non permesso']], 405);
}

if (!validate_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? null)) {
    json_response(['success' => false, 'errors' => ['Token CSRF non valido']], 419);
}

$action = $_POST['action'] ?? null;
if ($action === 'update_status') {
    $requestId = (int) ($_POST['id'] ?? 0);
    $status = sanitize_text($_POST['status'] ?? '');
    $note = sanitize_text($_POST['note'] ?? '');

    if (!$requestId || !in_array($status, ['pending', 'processing', 'completed', 'rejected'], true)) {
        json_response(['success' => false, 'errors' => ['Parametri non validi']], 422);
    }

    $stmt = db()->prepare('UPDATE requests SET status = :status, updated_at = NOW(), data = JSON_SET(COALESCE(data, JSON_OBJECT()), "$.admin_note", :note) WHERE id = :id');
    $stmt->execute([
        'status' => $status,
        'note' => $note,
        'id' => $requestId,
    ]);

    log_event('Request status updated', ['request_id' => $requestId, 'status' => $status]);

    json_response(['success' => true]);
}

json_response(['success' => false, 'errors' => ['Azione non supportata']], 400);
