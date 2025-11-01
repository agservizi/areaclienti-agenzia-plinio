<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_ajax_request()) {
    http_response_code(400);
    exit('Bad request');
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (!$action) {
    json_response(['success' => false, 'errors' => ['Azione non valida']], 400);
}

if (!validate_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? null)) {
    json_response(['success' => false, 'errors' => ['Token CSRF non valido']], 419);
}

try {
    switch ($action) {
        case 'login':
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';
            if (!$email || $password === '') {
                json_response(['success' => false, 'errors' => ['Credenziali non valide']], 422);
            }

            if (!auth_login($email, $password)) {
                json_response(['success' => false, 'errors' => ['Email o password errati']], 401);
            }

            json_response(['success' => true, 'redirect' => has_role(ROLE_ADMIN) ? '/admin/dashboard.php' : '/client/dashboard.php']);
            break;

        case 'register':
            $required = ['name', 'email', 'password'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    json_response(['success' => false, 'errors' => ['Tutti i campi sono obbligatori']], 422);
                }
            }
            $payload = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'phone' => $_POST['phone'] ?? '',
            ];

            $result = auth_register($payload);
            if (!$result['success']) {
                json_response(['success' => false, 'errors' => $result['errors'] ?? ['Registrazione fallita']], 422);
            }

            auth_login($payload['email'], $payload['password']);
            json_response(['success' => true, 'redirect' => '/client/dashboard.php']);
            break;

        case 'logout':
            auth_logout();
            json_response(['success' => true, 'redirect' => '/index.php']);
            break;

        default:
            json_response(['success' => false, 'errors' => ['Azione non supportata']], 400);
    }
} catch (Throwable $exception) {
    log_event('Auth API exception', [
        'action' => $action,
        'message' => $exception->getMessage(),
    ], 'error');

    json_response([
        'success' => false,
        'errors' => ['Si è verificato un errore inatteso, riprova più tardi.'],
    ], 500);
}
