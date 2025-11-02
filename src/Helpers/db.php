<?php

declare(strict_types=1);

use PDO;
use PDOException;

function db_connect(): PDO
{
    $host = env('DB_HOST', '127.0.0.1');
    $db = env('DB_NAME', '');
    $user = env('DB_USERNAME', 'root');
    $pass = env('DB_PASSWORD', '');
    $charset = env('DB_CHARSET', 'utf8mb4');
    $port = env('DB_PORT', '3306');

    $dsn = "mysql:host={$host};dbname={$db};charset={$charset};port={$port}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $exception) {
        app_log('database', 'Connection failed', ['message' => $exception->getMessage()]);
        throw $exception;
    }
}

function db(): PDO
{
    static $connection;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $connection = db_connect();
    return $connection;
}
