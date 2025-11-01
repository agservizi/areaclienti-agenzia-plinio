<?php
declare(strict_types=1);

// Base configuration for AG Servizi Area Personale.
// IMPORTANT: duplicate this file as config.local.php if you need environment-specific overrides.

if (session_status() === PHP_SESSION_NONE) {
    session_name('agservizi_session');
    session_start();
}

date_default_timezone_set('Europe/Rome');

if (!function_exists('load_env_file')) {
    function load_env_file(string $filePath): void
    {
        static $loaded = [];
        if (isset($loaded[$filePath]) || !is_file($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));
            if ($name === '') {
                continue;
            }

            $value = trim($value, "\"' ");
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        $loaded[$filePath] = true;
    }
}

if (!function_exists('env_value')) {
    function env_value(string $key, mixed $default = null, bool $required = false): mixed
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            if ($required && $default === null) {
                throw new RuntimeException("Missing required environment variable: {$key}");
            }
            return $default;
        }

        return $value;
    }
}

const BASE_PATH = __DIR__ . '/..';
const STORAGE_PATH = BASE_PATH . '/storage';
const LOG_PATH = BASE_PATH . '/logs';
const UPLOAD_PATH = BASE_PATH . '/uploads';

load_env_file(BASE_PATH . '/.env');
load_env_file(BASE_PATH . '/.env.local');

$config = [
    'app' => [
        'name' => 'AG Servizi Area Personale',
        'env' => env_value('APP_ENV', 'local'),
        'base_url' => env_value('APP_URL', 'http://localhost'),
        'debug' => (bool) env_value('APP_DEBUG', true),
    ],
    'db' => [
    'driver' => env_value('DB_CONNECTION', 'mysql'),
    'host' => env_value('DB_HOST', null, true),
    'port' => (int) env_value('DB_PORT', 3306),
    'database' => env_value('DB_NAME', null, true),
    'username' => env_value('DB_USERNAME', env_value('DB_USER', null, true), true),
    'password' => env_value('DB_PASSWORD', env_value('DB_PASS', null, true), true),
        'charset' => env_value('DB_CHARSET', 'utf8mb4'),
        'collation' => env_value('DB_COLLATION', 'utf8mb4_unicode_ci'),
    ],
    'storage' => [
        // Available drivers: mysql, filesystem
        'driver' => getenv('STORAGE_DRIVER') ?: 'mysql',
        'filesystem_path' => STORAGE_PATH . '/encrypted',
        'encryption_key_path' => STORAGE_PATH . '/.master.key',
        'encryption_key_length' => 32,
    ],
    'security' => [
        'csrf_token_name' => '_csrf_token',
        'csrf_token_ttl' => 1800,
        'password_algo' => PASSWORD_ARGON2ID,
        'allowed_upload_mimes' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ],
        'max_upload_size' => 5 * 1024 * 1024, // 5 MB
    ],
    'mail' => [
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@agservizi.test',
        'from_name' => 'AG Servizi',
        'reply_to' => getenv('MAIL_REPLY_TO') ?: 'info@agservizi.test',
    ],
    'logs' => [
        'daily_rotation' => true,
        'max_files' => 14,
        'level' => 'info',
    ],
];

// Allow local overrides without committing sensitive data.
$localConfigPath = __DIR__ . '/config.local.php';
if (is_file($localConfigPath)) {
    /** @noinspection PhpIncludeInspection */
    $localOverrides = require $localConfigPath;
    if (is_array($localOverrides)) {
        $config = array_replace_recursive($config, $localOverrides);
    }
}

return $config;
