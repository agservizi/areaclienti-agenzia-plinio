<?php

declare(strict_types=1);

namespace App\Helpers {

    class Env
    {
        public static function load(string $directory): void
        {
            $path = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';
            if (!is_file($path)) {
                return;
            }

            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                return;
            }

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                    continue;
                }

                [$key, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
                $key = trim($key);
                $value = trim($value);
                $value = self::stripQuotes($value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }

        public static function get(string $key, ?string $default = null): ?string
        {
            return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }

        private static function stripQuotes(string $value): string
        {
            $length = strlen($value);
            if ($length >= 2 && $value[0] === '"' && $value[$length - 1] === '"') {
                return substr($value, 1, -1);
            }
            if ($length >= 2 && $value[0] === "'" && $value[$length - 1] === "'") {
                return substr($value, 1, -1);
            }
            return $value;
        }
    }
}

namespace {
    function env(string $key, ?string $default = null): ?string
    {
        return \App\Helpers\Env::get($key, $default);
    }
}
