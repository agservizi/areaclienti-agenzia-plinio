<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'errors' => ['Metodo non permesso']], 405);
}

if (!validate_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? null)) {
    json_response(['success' => false, 'errors' => ['Token CSRF non valido']], 419);
}

$address = sanitize_text($_POST['address'] ?? '');
$operator = sanitize_text($_POST['operator'] ?? '');
if ($address === '' || $operator === '') {
    json_response(['success' => false, 'errors' => ['Indirizzo e operatore sono obbligatori']], 422);
}

$availableOperators = ['fastweb', 'windtre', 'iliad', 'tim', 'vodafone'];
if (!in_array(strtolower($operator), $availableOperators, true)) {
    json_response(['success' => false, 'errors' => ['Operatore non supportato']], 422);
}

$speeds = [
    'fastweb' => '1 Gbps FTTH',
    'windtre' => '200 Mbps FTTC',
    'iliad' => '300 Mbps FTTH',
    'tim' => '1 Gbps FTTH',
    'vodafone' => '500 Mbps FTTH',
];

$result = [
    'address' => $address,
    'operator' => strtoupper($operator),
    'speed' => $speeds[strtolower($operator)] ?? '100 Mbps',
    'latency_ms' => random_int(12, 32),
    'availability' => true,
];

$stmt = db()->prepare('INSERT INTO coverage_checks (user_id, address, operator, result) VALUES (:user_id, :address, :operator, :result)');
$stmt->execute([
    'user_id' => $_SESSION['auth_user_id'] ?? null,
    'address' => $address,
    'operator' => strtoupper($operator),
    'result' => json_encode($result, JSON_THROW_ON_ERROR),
]);

json_response(['success' => true, 'data' => $result]);
