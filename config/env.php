<?php

static $config;

if ($config !== null) {
	return $config;
}

$projectRoot = dirname(__DIR__);
$autoloadPath = $projectRoot . '/vendor/autoload.php';

if (is_file($autoloadPath)) {
	require_once $autoloadPath;

	if (class_exists('Dotenv\\Dotenv')) {
		$dotenvClass = 'Dotenv\\Dotenv';
		$dotenvClass::createImmutable($projectRoot)->safeLoad();
	}
}

$env = static function (string $key, mixed $default = null) {
	return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
};

$config = [
	'APP_ENV' => $env('APP_ENV'),
	'APP_DEBUG' => filter_var($env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
	'APP_URL' => $env('APP_URL'),
	'DB_CONNECTION' => $env('DB_CONNECTION'),
	'DB_HOST' => $env('DB_HOST'),
	'DB_PORT' => $env('DB_PORT'),
	'DB_NAME' => $env('DB_NAME'),
	'DB_USERNAME' => $env('DB_USERNAME'),
	'DB_PASSWORD' => $env('DB_PASSWORD'),
	'DB_CHARSET' => $env('DB_CHARSET'),
	'DB_COLLATION' => $env('DB_COLLATION'),
];

return $config;
