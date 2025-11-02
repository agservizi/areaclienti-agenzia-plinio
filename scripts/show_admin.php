<?php

declare(strict_types=1);

use App\Helpers\Env;

require __DIR__ . '/../vendor/autoload.php';

Env::load(dirname(__DIR__));

$pdo = db();
$stmt = $pdo->prepare('SELECT id, username, email, role, created_at, updated_at FROM users WHERE username = :username');
$stmt->execute(['username' => 'admin']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Nessun utente admin trovato.\n";
    exit(0);
}

print_r($user);
