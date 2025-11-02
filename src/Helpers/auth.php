<?php

declare(strict_types=1);

use PDO;

function secure_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_name('agenziaplinio_session');
    session_start();
}

function login_user(array $user): void
{
    secure_session_start();
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
    $_SESSION['last_activity'] = time();
}

function logout_user(): void
{
    secure_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function is_logged_in(): bool
{
    secure_session_start();
    if (!isset($_SESSION['user'])) {
        return false;
    }

    $timeout = 60 * 60;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        logout_user();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return true;
}

function current_user(): ?array
{
    secure_session_start();
    return $_SESSION['user'] ?? null;
}

function require_login(?string $role = null): void
{
    if (!is_logged_in()) {
        header('Location: /auth/login');
        exit;
    }

    if ($role !== null) {
        $user = current_user();
        if (!$user || $user['role'] !== $role) {
            http_response_code(403);
            echo 'Accesso negato';
            exit;
        }
    }
}

function has_too_many_attempts(string $email): bool
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT attempts, last_attempt_at FROM login_attempts WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();
    if (!$row) {
        return false;
    }

    $attempts = (int) $row['attempts'];
    $lastAttempt = strtotime((string) $row['last_attempt_at']);
    if ($attempts >= 5 && $lastAttempt !== false && (time() - $lastAttempt) < 900) {
        return true;
    }

    if ($lastAttempt !== false && (time() - $lastAttempt) >= 900) {
        clear_login_attempts($email);
        return false;
    }

    return false;
}

function record_login_attempt(string $email): void
{
    $pdo = db();
    $stmt = $pdo->prepare('INSERT INTO login_attempts (email, attempts, last_attempt_at) VALUES (:email, 1, NOW())
        ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt_at = NOW()');
    $stmt->execute(['email' => $email]);
}

function clear_login_attempts(string $email): void
{
    $pdo = db();
    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE email = :email');
    $stmt->execute(['email' => $email]);
}
