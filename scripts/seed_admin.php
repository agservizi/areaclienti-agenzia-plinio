<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Questo script va eseguito da CLI.\n");
    exit(1);
}

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$options = getopt('', ['email:', 'password:', 'name::', 'force::']);
$email = isset($options['email']) ? strtolower(trim((string) $options['email'])) : '';
$password = $options['password'] ?? '';
$name = trim((string) ($options['name'] ?? 'Admin AG Servizi'));
$force = array_key_exists('force', $options);

if ($email === '' || $password === '') {
    fwrite(STDERR, "Uso: php scripts/seed_admin.php --email=admin@agservizi.test --password=Admin123! [--name=Nome Cognome] [--force]\n");
    exit(1);
}

$config = get_config();
$hash = password_hash($password, $config['security']['password_algo']);

$stmt = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$existing = $stmt->fetch();

if ($existing && !$force) {
    fwrite(STDERR, "Utente gia presente. Usa --force per aggiornare password e ruolo.\n");
    exit(1);
}

if ($existing) {
    $update = db()->prepare('UPDATE users SET role = :role, name = :name, password = :password, updated_at = NOW() WHERE id = :id');
    $update->execute([
        'role' => ROLE_ADMIN,
        'name' => $name,
        'password' => $hash,
        'id' => $existing['id'],
    ]);
    log_event('Admin seeded via script', ['user_id' => $existing['id']]);
    echo "Admin aggiornato con successo.\n";
    exit(0);
}

$insert = db()->prepare('INSERT INTO users (role, name, email, password) VALUES (:role, :name, :email, :password)');
$insert->execute([
    'role' => ROLE_ADMIN,
    'name' => $name,
    'email' => $email,
    'password' => $hash,
]);

$userId = (int) db()->lastInsertId();
log_event('Admin seeded via script', ['user_id' => $userId]);

echo "Admin creato con successo (ID {$userId}).\n";
exit(0);
