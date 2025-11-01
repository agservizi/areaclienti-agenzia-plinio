<?php
/**
 * API endpoint per il login
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
    if (empty($data['username']) || empty($data['password'])) {
        throw new Exception('Username e password sono obbligatori');
    }

    // Sanitizzazione input
    $username = trim($data['username']);
    $password = $data['password'];
    $remember = isset($data['remember']) ? (bool)$data['remember'] : false;

    // Istanza della classe Auth
    $auth = new Auth();

    // Tentativo di login
    $result = $auth->login($username, $password);

    if ($result['success']) {
        // Gestione "Ricordami" (opzionale - implementare cookie sicuri se necessario)
        if ($remember) {
            // Implementare cookie di remember se richiesto
            // setcookie('remember_token', $secure_token, time() + 30*24*60*60, '/', '', true, true);
        }

        // Risposta di successo
        echo json_encode([
            'success' => true,
            'message' => 'Login effettuato con successo',
            'user' => $result['user']
        ]);
    } else {
        http_response_code(401);
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