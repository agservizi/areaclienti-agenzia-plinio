<?php

declare(strict_types=1);

use App\Helpers\Env;

require __DIR__ . '/../vendor/autoload.php';

Env::load(dirname(__DIR__));

$host = env('DB_HOST');
$port = env('DB_PORT', '3306');
$name = env('DB_NAME');
$user = env('DB_USERNAME');
$pass = env('DB_PASSWORD');
$charset = env('DB_CHARSET', 'utf8mb4');

if (!$host || !$name || !$user) {
    fwrite(STDERR, "Missing database configuration in .env\n");
    exit(1);
}

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);
$options = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
];

if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
    $options[\PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
}

try {
    $pdo = new \PDO($dsn, $user, $pass, $options);
} catch (\Throwable $exception) {
    fwrite(STDERR, 'Connection failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

$schemaPath = __DIR__ . '/../migrations/schema.sql';
if (!is_file($schemaPath)) {
    fwrite(STDERR, "Schema file not found at migrations/schema.sql\n");
    exit(1);
}

$sql = file_get_contents($schemaPath);
if ($sql === false) {
    fwrite(STDERR, "Unable to read schema file\n");
    exit(1);
}

try {
    $pdo->exec($sql);
    fwrite(STDOUT, "Schema import completed successfully.\n");
} catch (\Throwable $exception) {
    fwrite(STDERR, 'Schema import failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
