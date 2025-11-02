<?php
$config = include __DIR__ . '/../config/env.php';

try {
    $dsn = "{$config['DB_CONNECTION']}:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset={$config['DB_CHARSET']}";
    $pdo = new PDO($dsn, $config['DB_USERNAME'], $config['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    if ($config['APP_DEBUG']) {
        die('Errore di connessione: ' . $e->getMessage());
    }

    die('Errore di connessione al database.');
}
