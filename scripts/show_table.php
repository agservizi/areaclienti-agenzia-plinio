<?php

declare(strict_types=1);

use App\Helpers\Env;

require __DIR__ . '/../vendor/autoload.php';

Env::load(dirname(__DIR__));

$table = $argv[1] ?? null;
if (!$table) {
    fwrite(STDERR, "Usage: php scripts/show_table.php <table>\n");
    exit(1);
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    env('DB_HOST'),
    env('DB_PORT', '3306'),
    env('DB_NAME'),
    env('DB_CHARSET', 'utf8mb4')
);

try {
    $pdo = new \PDO($dsn, env('DB_USERNAME'), env('DB_PASSWORD'), [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ]);
    $result = $pdo->query('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`');
    $data = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
    if (!$data) {
        fwrite(STDERR, "Table not found: {$table}\n");
        exit(1);
    }
    echo ($data['Create Table'] ?? '') . PHP_EOL;
} catch (\Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
