<?php

static $config;

if ($config !== null) {
    return $config;
}

$projectRoot = dirname(__DIR__);
$autoloadPath = $projectRoot . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;

    if (class_exists('Dotenv\\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
        $dotenv->safeLoad();
    }
}

$env = function (string $key, mixed $default = null) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
};

$config = [
    'APP_ENV' => $env('APP_ENV', 'production'),
    'APP_DEBUG' => filter_var($env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
    'APP_URL' => $env('APP_URL', 'https://sienna-newt-368622.hostingersite.com'),
    'DB_CONNECTION' => $env('DB_CONNECTION', 'mysql'),
    'DB_HOST' => $env('DB_HOST', '193.203.168.205'),
    'DB_PORT' => (string) $env('DB_PORT', '3306'),
    'DB_NAME' => $env('DB_NAME', 'u427445037_portal'),
    'DB_USERNAME' => $env('DB_USERNAME', 'u427445037_portal'),
    'DB_PASSWORD' => $env('DB_PASSWORD', 'Giogiu2123@'),
    'DB_CHARSET' => $env('DB_CHARSET', 'utf8mb4'),
    'DB_COLLATION' => $env('DB_COLLATION', 'utf8mb4_unicode_ci'),
];

return $config;
