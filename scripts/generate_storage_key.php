<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Questo script va eseguito da CLI.\n");
    exit(1);
}

require_once __DIR__ . '/../includes/helpers.php';

if (!extension_loaded('sodium')) {
    fwrite(STDERR, "Estensione sodium non disponibile: abilitarla prima di procedere.\n");
    exit(1);
}

$config = get_config();
$keyPath = $config['storage']['encryption_key_path'];
$keyDir = dirname($keyPath);
$length = (int) ($config['storage']['encryption_key_length'] ?? 32);

$options = getopt('', ['force::']);
$force = array_key_exists('force', $options);

if (is_file($keyPath) && !$force) {
    fwrite(STDERR, "Chiave gia esistente. Usa --force per rigenerarla.\n");
    exit(1);
}

if (!is_dir($keyDir) && !mkdir($keyDir, 0700, true) && !is_dir($keyDir)) {
    fwrite(STDERR, "Impossibile creare la cartella di storage: {$keyDir}\n");
    exit(1);
}

$key = random_bytes($length);
$written = file_put_contents($keyPath, sodium_bin2hex($key) . PHP_EOL, LOCK_EX);
if ($written === false) {
    fwrite(STDERR, "Scrittura della chiave fallita.\n");
    exit(1);
}

chmod($keyPath, 0600);

echo "Chiave generata in {$keyPath}\n";
exit(0);
