<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    $stmt = db()->query('SELECT id, slug, title, description, category FROM services WHERE enabled = 1 ORDER BY title ASC');
    $services = $stmt->fetchAll();
    json_response(['success' => true, 'data' => $services]);
}

if ($method !== 'POST') {
    json_response(['success' => false, 'errors' => ['Metodo non consentito']], 405);
}

if (!is_authenticated()) {
    json_response(['success' => false, 'errors' => ['Autenticazione richiesta']], 401);
}

if (!validate_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? null)) {
    json_response(['success' => false, 'errors' => ['Token CSRF non valido']], 419);
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
if ($action !== 'request') {
    json_response(['success' => false, 'errors' => ['Azione non supportata']], 400);
}

$serviceSlug = sanitize_text($_POST['service_slug'] ?? '');
if ($serviceSlug === '') {
    json_response(['success' => false, 'errors' => ['Servizio mancante']], 422);
}

$stmt = db()->prepare('SELECT id, title FROM services WHERE slug = :slug AND enabled = 1 LIMIT 1');
$stmt->execute(['slug' => $serviceSlug]);
$service = $stmt->fetch();
if (!$service) {
    json_response(['success' => false, 'errors' => ['Servizio non trovato']], 404);
}

$requestData = [
    'notes' => sanitize_text($_POST['notes'] ?? ''),
    'extra' => $_POST['extra'] ?? [],
];

$config = get_config();
$attachmentsMeta = [];
if (!empty($_FILES['attachments']['name'][0])) {
    $files = $_FILES['attachments'];
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        if ($files['size'][$i] > $config['security']['max_upload_size']) {
            continue;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $files['tmp_name'][$i]);
        finfo_close($finfo);
        if (!in_array($mime, $config['security']['allowed_upload_mimes'], true)) {
            continue;
        }

        if ($config['storage']['driver'] === 'filesystem') {
            $stored = store_encrypted_file((int) $_SESSION['auth_user_id'], $files['name'][$i], $files['tmp_name'][$i]);
            $attachmentsMeta[] = json_decode($stored['meta'], true, 512, JSON_THROW_ON_ERROR);
        } else {
            $randomName = bin2hex(random_bytes(16)) . '-' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $files['name'][$i]);
            $targetPath = rtrim(UPLOAD_PATH, '/\\') . '/' . $randomName;
            if (!move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                continue;
            }
            $attachmentsMeta[] = [
                'filename' => $randomName,
                'original' => $files['name'][$i],
                'mime' => $mime,
                'stored_at' => time(),
            ];
        }
    }
}

$stmt = db()->prepare('INSERT INTO requests (user_id, service_id, status, data, attachments) VALUES (:user_id, :service_id, :status, :data, :attachments)');
$stmt->execute([
    'user_id' => $_SESSION['auth_user_id'],
    'service_id' => $service['id'],
    'status' => 'pending',
    'data' => json_encode($requestData, JSON_THROW_ON_ERROR),
    'attachments' => $attachmentsMeta ? json_encode($attachmentsMeta, JSON_THROW_ON_ERROR) : null,
]);

log_event('New service request created', [
    'request_id' => db()->lastInsertId(),
    'user_id' => $_SESSION['auth_user_id'],
    'service_id' => $service['id'],
]);

json_response(['success' => true, 'message' => 'Richiesta inviata con successo']);
