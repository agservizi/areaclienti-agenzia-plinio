<?php
/**
 * API endpoint per il recupero password
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

    // Validazione input
    if (empty($data['email'])) {
        throw new Exception('Email è obbligatoria');
    }

    $email = trim(strtolower($data['email']));

    // Validazione email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email non valida');
    }

    // Istanza della classe Auth
    $auth = new Auth();

    // Richiesta reset password
    $result = $auth->requestPasswordReset($email);

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => $result['message']
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