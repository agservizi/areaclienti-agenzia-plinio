<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/**
 * Authentication helper functions.
 */

const ROLE_ADMIN = 'admin';
const ROLE_CLIENT = 'client';

if (!function_exists('auth_login')) {
    function auth_login(string $identifier, string $password): bool
    {
        $identifier = trim($identifier);
        if ($identifier === '' || $password === '') {
            return false;
        }

        $pdo = db();
        $user = null;
        $lowerIdentifier = strtolower($identifier);

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $lowerIdentifier]);
            $user = $stmt->fetch();

            if ($user && $user['role'] === ROLE_ADMIN) {
                log_event('Admin email login attempt blocked', ['user_id' => $user['id'], 'email' => $lowerIdentifier], 'warning');
                return false;
            }
        } else {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
            $stmt->execute(['username' => $lowerIdentifier]);
            $user = $stmt->fetch();
        }

        if (!$user) {
            log_event('Login attempt failed - user not found', ['identifier' => $identifier], 'warning');
            return false;
        }

        if ($user['role'] === ROLE_ADMIN && ($user['username'] ?? '') === '') {
            log_event('Admin account missing username', ['user_id' => $user['id']], 'error');
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            log_event('Login attempt failed - wrong password', ['user_id' => $user['id']], 'warning');
            return false;
        }

        $_SESSION['auth_user_id'] = (int) $user['id'];
        $_SESSION['auth_user_role'] = $user['role'];
        $_SESSION['auth_last_seen'] = time();
        log_event('User logged in', ['user_id' => $user['id']]);

        return true;
    }
}

if (!function_exists('auth_register')) {
    function auth_register(array $payload): array
    {
        $config = get_config();
        $hash = password_hash($payload['password'], $config['security']['password_algo']);
        $stmt = db()->prepare('INSERT INTO users (role, name, email, password, phone) VALUES (:role, :name, :email, :password, :phone)');

        $data = [
            'role' => ROLE_CLIENT,
            'name' => sanitize_text($payload['name'] ?? ''),
            'email' => strtolower(trim($payload['email'] ?? '')),
            'password' => $hash,
            'phone' => sanitize_text($payload['phone'] ?? ''),
        ];

        try {
            $stmt->execute($data);
            $userId = (int) db()->lastInsertId();
            log_event('User registered', ['user_id' => $userId]);
            return ['success' => true, 'user_id' => $userId];
        } catch (PDOException $exception) {
            log_event('Registration failed', ['error' => $exception->getMessage()], 'error');
            if ((int) $exception->getCode() === 23000) {
                return ['success' => false, 'errors' => ['Email già registrata']];
            }
            return ['success' => false, 'errors' => ['Registrazione non riuscita']];
        }
    }
}

if (!function_exists('auth_logout')) {
    function auth_logout(): void
    {
        if (isset($_SESSION['auth_user_id'])) {
            log_event('User logged out', ['user_id' => $_SESSION['auth_user_id']]);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array
    {
        get_config();
        static $cachedUser;
        if ($cachedUser !== null) {
            return $cachedUser;
        }
        $userId = $_SESSION['auth_user_id'] ?? null;
        if (!$userId) {
            return null;
        }

    $stmt = db()->prepare('SELECT id, role, username, name, email, phone, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $cachedUser = $stmt->fetch();

        return $cachedUser ?: null;
    }
}

if (!function_exists('is_authenticated')) {
    function is_authenticated(): bool
    {
        return current_user() !== null;
    }
}

if (!function_exists('has_role')) {
    function has_role(string $role): bool
    {
        $user = current_user();
        return $user && $user['role'] === $role;
    }
}

if (!function_exists('require_login')) {
    function require_login(?string $role = null): void
    {
        if (!is_authenticated()) {
            redirect('/index.php');
        }
        if ($role !== null && !has_role($role)) {
            redirect('/unauthorized.php');
        }
    }
}
