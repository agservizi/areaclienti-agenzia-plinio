<?php
/**
 * Classe per la gestione dell'autenticazione
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Login utente
     */
    public function login($username, $password) {
        try {
            // Controllo se l'utente è bloccato
            if ($this->isUserLocked($username)) {
                throw new Exception('Account temporaneamente bloccato per troppi tentativi di accesso falliti.');
            }

            // Recupero dati utente
            $stmt = $this->db->query(
                "SELECT id, username, email, password_hash, first_name, last_name, user_type, is_active, login_attempts 
                 FROM users WHERE (username = ? OR email = ?) AND is_active = 1",
                [$username, $username]
            );

            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $this->incrementLoginAttempts($username);
                throw new Exception('Credenziali non valide.');
            }

            // Reset tentativi di login
            $this->resetLoginAttempts($user['id']);

            // Aggiornamento ultimo login
            $this->updateLastLogin($user['id']);

            // Creazione sessione
            $this->createSession($user);

            // Log attività
            $this->logActivity($user['id'], 'login', 'user', $user['id']);

            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'user_type' => $user['user_type']
                ]
            ];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Logout utente
     */
    public function logout() {
        try {
            if (isset($_SESSION['user_id'])) {
                // Invalidazione sessione nel database
                $this->invalidateSession($_SESSION['session_token']);
                
                // Log attività
                $this->logActivity($_SESSION['user_id'], 'logout', 'user', $_SESSION['user_id']);
            }

            // Distruzione sessione
            session_unset();
            session_destroy();

            return ['success' => true];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Registrazione nuovo cliente
     */
    public function registerClient($data) {
        try {
            // Validazione dati
            $this->validateRegistrationData($data);

            // Controllo se username/email già esistono
            if ($this->userExists($data['username'], $data['email'])) {
                throw new Exception('Username o email già in uso.');
            }

            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

            // Inserimento nuovo utente
            $stmt = $this->db->query(
                "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, address, city, postal_code, fiscal_code, user_type) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'client')",
                [
                    $data['username'],
                    $data['email'],
                    $passwordHash,
                    $data['first_name'],
                    $data['last_name'],
                    $data['phone'] ?? null,
                    $data['address'] ?? null,
                    $data['city'] ?? null,
                    $data['postal_code'] ?? null,
                    $data['fiscal_code'] ?? null
                ]
            );

            $userId = $this->db->lastInsertId();

            // Log attività
            $this->logActivity($userId, 'register', 'user', $userId);

            return ['success' => true, 'user_id' => $userId];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Controllo se utente è autenticato
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && $this->isSessionValid();
    }

    /**
     * Controllo se utente è admin
     */
    public function isAdmin() {
        return $this->isAuthenticated() && $_SESSION['user_type'] === 'admin';
    }

    /**
     * Controllo se utente è cliente
     */
    public function isClient() {
        return $this->isAuthenticated() && $_SESSION['user_type'] === 'client';
    }

    /**
     * Ottieni dati utente corrente
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $stmt = $this->db->query(
            "SELECT id, username, email, first_name, last_name, phone, address, city, postal_code, fiscal_code, user_type, created_at 
             FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );

        return $stmt->fetch();
    }

    /**
     * Cambio password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Verifica password corrente
            $stmt = $this->db->query("SELECT password_hash FROM users WHERE id = ?", [$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                throw new Exception('Password corrente non corretta.');
            }

            // Validazione nuova password
            if (strlen($newPassword) < 8) {
                throw new Exception('La nuova password deve essere di almeno 8 caratteri.');
            }

            // Aggiornamento password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->query(
                "UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$newPasswordHash, $userId]
            );

            // Log attività
            $this->logActivity($userId, 'change_password', 'user', $userId);

            return ['success' => true];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Reset password (invio email)
     */
    public function requestPasswordReset($email) {
        try {
            $stmt = $this->db->query("SELECT id, first_name, last_name FROM users WHERE email = ? AND is_active = 1", [$email]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception('Email non trovata.');
            }

            // Generazione token reset
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Salvataggio token (da implementare tabella password_resets)
            
            // Invio email (da implementare)
            
            return ['success' => true, 'message' => 'Email di reset inviata.'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Metodi privati

    private function createSession($user) {
        $sessionToken = bin2hex(random_bytes(32));
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['session_token'] = $sessionToken;
        $_SESSION['login_time'] = time();

        // Salvataggio sessione nel database
        $this->db->query(
            "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
             VALUES (?, ?, ?, ?, ?)",
            [
                $user['id'],
                $sessionToken,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                date('Y-m-d H:i:s', time() + SESSION_TIMEOUT)
            ]
        );
    }

    private function isSessionValid() {
        if (!isset($_SESSION['session_token']) || !isset($_SESSION['login_time'])) {
            return false;
        }

        // Controllo timeout
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            $this->logout();
            return false;
        }

        // Controllo validità sessione nel database
        $stmt = $this->db->query(
            "SELECT id FROM user_sessions WHERE session_token = ? AND is_active = 1 AND expires_at > NOW()",
            [$_SESSION['session_token']]
        );

        return $stmt->fetch() !== false;
    }

    private function invalidateSession($sessionToken) {
        $this->db->query(
            "UPDATE user_sessions SET is_active = 0 WHERE session_token = ?",
            [$sessionToken]
        );
    }

    private function isUserLocked($username) {
        $stmt = $this->db->query(
            "SELECT locked_until FROM users WHERE (username = ? OR email = ?) AND locked_until > NOW()",
            [$username, $username]
        );

        return $stmt->fetch() !== false;
    }

    private function incrementLoginAttempts($username) {
        $stmt = $this->db->query(
            "UPDATE users SET login_attempts = login_attempts + 1 WHERE username = ? OR email = ?",
            [$username, $username]
        );

        // Blocco account dopo MAX_LOGIN_ATTEMPTS
        $this->db->query(
            "UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) 
             WHERE (username = ? OR email = ?) AND login_attempts >= ?",
            [$username, $username, MAX_LOGIN_ATTEMPTS]
        );
    }

    private function resetLoginAttempts($userId) {
        $this->db->query(
            "UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?",
            [$userId]
        );
    }

    private function updateLastLogin($userId) {
        $this->db->query(
            "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?",
            [$userId]
        );
    }

    private function validateRegistrationData($data) {
        $required = ['username', 'email', 'password', 'first_name', 'last_name'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Il campo {$field} è obbligatorio.");
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email non valida.');
        }

        if (strlen($data['password']) < 8) {
            throw new Exception('La password deve essere di almeno 8 caratteri.');
        }

        if (strlen($data['username']) < 3) {
            throw new Exception('Lo username deve essere di almeno 3 caratteri.');
        }
    }

    private function userExists($username, $email) {
        $stmt = $this->db->query(
            "SELECT id FROM users WHERE username = ? OR email = ?",
            [$username, $email]
        );

        return $stmt->fetch() !== false;
    }

    private function logActivity($userId, $action, $resourceType = null, $resourceId = null, $oldValues = null, $newValues = null) {
        $this->db->query(
            "INSERT INTO activity_logs (user_id, action, resource_type, resource_id, old_values, new_values, ip_address, user_agent) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $action,
                $resourceType,
                $resourceId,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]
        );
    }
}