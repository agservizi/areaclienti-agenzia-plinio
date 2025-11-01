<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login();

$requestId = (int) ($_GET['request_id'] ?? 0);
$fileIndex = (int) ($_GET['file'] ?? -1);

if ($requestId < 1 || $fileIndex < 0) {
    http_response_code(400);
    exit('Parametri non validi.');
}

$stmt = db()->prepare('SELECT r.id, r.user_id, r.attachments, s.title AS service_title FROM requests r JOIN services s ON s.id = r.service_id WHERE r.id = :id LIMIT 1');
$stmt->execute(['id' => $requestId]);
$request = $stmt->fetch();

if (!$request) {
    http_response_code(404);
    exit('Richiesta non trovata.');
}

$user = current_user();
if (!$user) {
    http_response_code(403);
    exit('Sessione non valida.');
}

$isOwner = (int) $request['user_id'] === (int) $user['id'];
$isAdmin = has_role(ROLE_ADMIN);
if (!$isOwner && !$isAdmin) {
    http_response_code(403);
    exit('Non autorizzato.');
}

if (empty($request['attachments'])) {
    http_response_code(404);
    exit('Nessun allegato disponibile.');
}

try {
    $attachments = json_decode($request['attachments'], true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    log_event('Attachment decode failed', ['request_id' => $requestId, 'error' => $exception->getMessage()], 'error');
    http_response_code(500);
    exit('Allegati non disponibili.');
}

if (!isset($attachments[$fileIndex])) {
    http_response_code(404);
    exit('Allegato non trovato.');
}

$meta = $attachments[$fileIndex];
$downloadName = $meta['original'] ?? ('allegato-' . $requestId . '.bin');
$config = get_config();
$storageDriver = $config['storage']['driver'] ?? 'mysql';
$content = null;
$mimeType = $meta['mime'] ?? 'application/octet-stream';

try {
    if ($storageDriver === 'filesystem') {
        $filename = $meta['filename'] ?? null;
        $nonce = $meta['nonce'] ?? null;
        if (!$filename || !$nonce) {
            throw new RuntimeException('Metadati crittografati mancanti.');
        }
        $plaintext = read_encrypted_file((int) $request['user_id'], (string) $filename, (string) $nonce);
        $content = $plaintext;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detected = finfo_buffer($finfo, $plaintext);
            if ($detected) {
                $mimeType = $detected;
            }
            finfo_close($finfo);
        }
    } else {
        $filePath = rtrim(UPLOAD_PATH, '/\\') . '/' . ($meta['filename'] ?? '');
        if (!is_file($filePath)) {
            throw new RuntimeException('File non presente sul filesystem.');
        }
        $content = (string) file_get_contents($filePath);
    }
} catch (Throwable $exception) {
    log_event('Attachment download failed', ['request_id' => $requestId, 'error' => $exception->getMessage()], 'error');
    http_response_code(500);
    exit('Errore durante il download.');
}

if (!is_string($content)) {
    http_response_code(500);
    exit('Contenuto allegato non disponibile.');
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . strlen($content));
header('Content-Disposition: attachment; filename="' . addcslashes(basename($downloadName), '\\"') . '"');
header('X-Content-Type-Options: nosniff');

echo $content;

log_event('Attachment downloaded', [
    'request_id' => $requestId,
    'file_index' => $fileIndex,
    'by_admin' => $isAdmin,
]);

exit;