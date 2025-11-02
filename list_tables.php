<?php
require __DIR__ . '/includes/db_connect.php';

try {
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $name) {
        echo 'TABLE: ' . $name . PHP_EOL;
        $describe = $pdo->query('DESCRIBE `' . $name . '`');
        foreach ($describe->fetchAll(PDO::FETCH_ASSOC) as $column) {
            echo '  - ' . $column['Field'] . ' (' . $column['Type'] . ')';
            if ($column['Null'] === 'NO') {
                echo ' NOT NULL';
            }
            if ($column['Key'] !== '') {
                echo ' KEY ' . $column['Key'];
            }
            if ($column['Default'] !== null) {
                echo ' DEFAULT ' . $column['Default'];
            }
            if ($column['Extra'] !== '') {
                echo ' ' . $column['Extra'];
            }
            echo PHP_EOL;
        }
        echo PHP_EOL;
    }
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
