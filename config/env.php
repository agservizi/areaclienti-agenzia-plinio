<?php
/**
 * Semplice loader per file .env
 */

declare(strict_types=1);

if (!function_exists('app_load_env')) {
    /**
     * Carica le variabili definite in un file .env.
     */
    function app_load_env(?string $path = null): void
    {
        static $loadedPath = null;

        $path = $path ?? dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

        if ($loadedPath === $path) {
            return;
        }

        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $separatorPosition = strpos($line, '=');
            if ($separatorPosition === false) {
                continue;
            }

            $name = trim(substr($line, 0, $separatorPosition));
            $value = trim(substr($line, $separatorPosition + 1));

            if ($value !== '' && ($value[0] === "'" || $value[0] === '"')) {
                $value = trim($value, "'\"");
            }

            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        $loadedPath = $path;
    }
}

if (!function_exists('env')) {
    /**
     * Restituisce il valore di una variabile d'ambiente, con fallback opzionale.
     */
    function env(string $key, $default = null)
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        return $value;
    }
}
