<?php
/**
 * Configurazione Database per Area Clienti Agenzia Plinio
 */

require_once __DIR__ . '/env.php';

class Database {
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;
    private $charset;
    private $pdo;

    public function __construct() {
        app_load_env(__DIR__ . '/../.env');

        $this->host = env('DB_HOST', 'localhost');
        $this->port = env('DB_PORT', '3306');
        $this->database = env('DB_NAME', '');
        $this->username = env('DB_USERNAME', '');
        $this->password = env('DB_PASSWORD', '');
        $this->charset = env('DB_CHARSET', 'utf8mb4');

        $this->connect();
    }

    private function connect() {
        try {
            if ($this->database === '' || $this->username === '') {
                throw new RuntimeException('Credenziali database mancanti. Verificare il file .env');
            }

            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
        return $this->pdo->rollback();
    }
}