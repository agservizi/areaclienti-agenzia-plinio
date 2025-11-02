<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\PageController;
use App\Helpers\Env;

require __DIR__ . '/../vendor/autoload.php';

Env::load(dirname(__DIR__));

date_default_timezone_set('Europe/Rome');

if (env('APP_DEBUG', 'false') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

secure_session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_guard();
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/');
$path = $path === '' ? '/' : $path;

if ($path === '/index.php' || $path === '/public/index.php') {
    $path = '/';
}

$routes = [
    'GET' => [
        '/' => [AuthController::class, 'showLogin'],
    '/auth/login' => [AuthController::class, 'showLogin'],
        '/auth/register' => [AuthController::class, 'showRegister'],
        '/client/dashboard' => [ClientController::class, 'dashboard'],
        '/client/profile' => [ClientController::class, 'profile'],
        '/client/services' => [ClientController::class, 'services'],
        '/client/spid/request' => [ClientController::class, 'spidForm'],
        '/client/sim/request' => [ClientController::class, 'simForm'],
        '/client/shipments' => [ClientController::class, 'shipments'],
        '/client/tickets' => [ClientController::class, 'tickets'],
        '/client/documents' => [ClientController::class, 'documents'],
        '/admin/dashboard' => [AdminController::class, 'dashboard'],
        '/admin/users' => [AdminController::class, 'users'],
        '/admin/spid' => [AdminController::class, 'spid'],
        '/admin/sim-orders' => [AdminController::class, 'simOrders'],
        '/admin/shipments' => [AdminController::class, 'shipments'],
        '/admin/tickets' => [AdminController::class, 'tickets'],
    ],
    'POST' => [
        '/auth/login' => [AuthController::class, 'login'],
        '/auth/register' => [AuthController::class, 'register'],
        '/auth/logout' => [AuthController::class, 'logout'],
        '/client/profile' => [ClientController::class, 'updateProfile'],
        '/client/spid/request' => [ClientController::class, 'submitSpid'],
        '/client/sim/request' => [ClientController::class, 'submitSim'],
        '/client/coverage/check' => [ClientController::class, 'checkCoverage'],
        '/client/shipments' => [ClientController::class, 'createShipment'],
        '/client/tickets' => [ClientController::class, 'createTicket'],
        '/api/coverage' => [ApiController::class, 'coverage'],
        '/api/upload' => [ApiController::class, 'upload'],
        '/admin/users/create' => [AdminController::class, 'createUser'],
        '/admin/users/update-role' => [AdminController::class, 'updateUserRole'],
        '/admin/spid/update' => [AdminController::class, 'updateSpidStatus'],
        '/admin/sim-orders/update' => [AdminController::class, 'updateSimStatus'],
        '/admin/shipments/update' => [AdminController::class, 'updateShipmentStatus'],
        '/admin/tickets/reply' => [AdminController::class, 'replyTicket'],
        '/admin/notifications/broadcast' => [AdminController::class, 'sendBroadcast'],
    ],
];

if (isset($routes[$method][$path])) {
    [$controllerClass, $action] = $routes[$method][$path];
    $controller = new $controllerClass();
    $controller->$action();
    exit;
}

if ($method === 'GET' && preg_match('#^/api/tracking/([A-Za-z0-9\-]+)$#', $path, $matches)) {
    $controller = new ApiController();
    $controller->tracking($matches[1]);
    exit;
}

if ($method === 'GET' && $path === '/client/documents/download') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $controller = new ClientController();
    $controller->downloadDocument($id);
    exit;
}

http_response_code(404);
echo 'Pagina non trovata';
