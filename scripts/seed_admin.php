<?php

declare(strict_types=1);

use App\Helpers\Env;

require __DIR__ . '/../vendor/autoload.php';

Env::load(dirname(__DIR__));

$pdo = db();

$username = 'admin';
$email = 'admin@example.com';
$passwordHash = password_hash('admin', PASSWORD_DEFAULT);

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if ($user) {
    $update = $pdo->prepare('UPDATE users SET name = :name, email = :email, password = :password, role = :role, updated_at = NOW() WHERE id = :id');
    $update->execute([
        'name' => 'Amministratore',
        'email' => $email,
        'password' => $passwordHash,
        'role' => 'admin',
        'id' => $user['id'],
    ]);
    echo "Utente admin aggiornato.\n";
    return;
}

$insert = $pdo->prepare('INSERT INTO users (username, name, email, password, role, created_at) VALUES (:username, :name, :email, :password, :role, NOW())');
$insert->execute([
    'username' => $username,
    'name' => 'Amministratore',
    'email' => $email,
    'password' => $passwordHash,
    'role' => 'admin',
]);

echo "Utente admin creato.\n";
