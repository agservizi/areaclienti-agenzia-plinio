<?php
/**
 * API endpoint per la registrazione
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Verifica che sia una richiesta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

try {
    // Leggi i dati JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Dati non validi');
    }

    // Validazione campi obbligatori
    $required_fields = ['username', 'email', 'password', 'first_name', 'last_name'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Il campo {$field} è obbligatorio");
        }
    }

    // Sanitizzazione dati
    $registrationData = [
        'username' => trim($data['username']),
        'email' => trim(strtolower($data['email'])),
        'password' => $data['password'],
        'first_name' => trim($data['first_name']),
        'last_name' => trim($data['last_name']),
        'phone' => isset($data['phone']) ? trim($data['phone']) : null,
        'address' => isset($data['address']) ? trim($data['address']) : null,
        'city' => isset($data['city']) ? trim($data['city']) : null,
        'postal_code' => isset($data['postal_code']) ? trim($data['postal_code']) : null,
        'fiscal_code' => isset($data['fiscal_code']) ? strtoupper(trim($data['fiscal_code'])) : null
    ];

    // Validazioni aggiuntive
    if (!filter_var($registrationData['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email non valida');
    }

    if (strlen($registrationData['password']) < 8) {
        throw new Exception('La password deve essere di almeno 8 caratteri');
    }

    if (strlen($registrationData['username']) < 3) {
        throw new Exception('Lo username deve essere di almeno 3 caratteri');
    }

    // Validazione codice fiscale se presente
    if ($registrationData['fiscal_code'] && !preg_match('/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/', $registrationData['fiscal_code'])) {
        throw new Exception('Codice fiscale non valido');
    }

    // Validazione numero di telefono se presente
    if ($registrationData['phone'] && !preg_match('/^[\+]?[0-9\s\-\(\)]{8,}$/', $registrationData['phone'])) {
        throw new Exception('Numero di telefono non valido');
    }

    // Istanza della classe Auth
    $auth = new Auth();

    // Tentativo di registrazione
    $result = $auth->registerClient($registrationData);

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Registrazione completata con successo',
            'user_id' => $result['user_id']
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $result['error']
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>