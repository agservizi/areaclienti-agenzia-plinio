<?php
declare(strict_types=1);

/**
 * Shared helper functions for the AG Servizi portal.
 */

if (!function_exists('get_config')) {
    function get_config(): array
    {
        static $config;
        if ($config === null) {
            /** @noinspection PhpIncludeInspection */
            $config = require __DIR__ . '/config.php';
        }

        return $config;
    }
}

if (!function_exists('db')) {
    function db(): PDO
    {
        static $pdo;
        if ($pdo !== null) {
            return $pdo;
        }

        $config = get_config();
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['db']['host'],
            $config['db']['port'],
            $config['db']['database'],
            $config['db']['charset']
        );

        $pdo = new PDO(
            $dsn,
            $config['db']['username'],
            $config['db']['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return $pdo;
    }
}

if (!function_exists('escape')) {
    function escape(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('sanitize_text')) {
    function sanitize_text(string $value): string
    {
        return trim(filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
    }
}

if (!function_exists('json_response')) {
    function json_response(array $data = [], int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }
}

if (!function_exists('is_post')) {
    function is_post(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

if (!function_exists('is_ajax_request')) {
    function is_ajax_request(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

if (!function_exists('ensure_csrf_token')) {
    function ensure_csrf_token(): void
    {
        $config = get_config();
        $tokenName = $config['security']['csrf_token_name'];
        $now = time();
        if (!isset($_SESSION[$tokenName]) || !isset($_SESSION[$tokenName . '_expires']) || $_SESSION[$tokenName . '_expires'] < $now) {
            $_SESSION[$tokenName] = bin2hex(random_bytes(32));
            $_SESSION[$tokenName . '_expires'] = $now + (int) $config['security']['csrf_token_ttl'];
        }
    }
}

if (!function_exists('get_csrf_token')) {
    function get_csrf_token(): string
    {
        ensure_csrf_token();
        $config = get_config();
        return $_SESSION[$config['security']['csrf_token_name']];
    }
}

if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token(?string $token): bool
    {
        $config = get_config();
        $tokenName = $config['security']['csrf_token_name'];
        if (!isset($_SESSION[$tokenName])) {
            return false;
        }

        return hash_equals($_SESSION[$tokenName], $token ?? '');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        if (!headers_sent()) {
            header('Location: ' . $path);
        }
        exit;
    }
}

if (!function_exists('enforce_log_rotation')) {
    function enforce_log_rotation(string $directory, int $maxFiles): void
    {
        static $rotationHandled = false;
        if ($rotationHandled || $maxFiles < 1) {
            return;
        }
        $files = glob(rtrim($directory, '/\\') . '/*.log');
        if ($files === false || count($files) <= $maxFiles) {
            $rotationHandled = true;
            return;
        }

        usort($files, static function (string $a, string $b): int {
            return (filemtime($a) ?: 0) <=> (filemtime($b) ?: 0);
        });

        $excess = array_slice($files, 0, count($files) - $maxFiles);
        foreach ($excess as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        $rotationHandled = true;
    }
}

if (!function_exists('log_event')) {
    function log_event(string $message, array $context = [], string $level = 'info'): void
    {
        $config = get_config();
        $logDir = LOG_PATH;
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $filename = $config['logs']['daily_rotation'] ? sprintf('%s/%s.log', $logDir, date('Y-m-d')) : $logDir . '/application.log';

        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $context['ua'] = $_SERVER['HTTP_USER_AGENT'] ?? 'cli';

        $line = sprintf(
            "%s [%s] %s %s\n",
            date('c'),
            strtoupper($level),
            $message,
            $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );

        file_put_contents($filename, $line, FILE_APPEND | LOCK_EX);

        if (!empty($config['logs']['daily_rotation'])) {
            $maxFiles = (int) ($config['logs']['max_files'] ?? 0);
            enforce_log_rotation($logDir, $maxFiles);
        }
    }
}

if (!function_exists('get_encryption_key')) {
    function get_encryption_key(): string
    {
        $config = get_config();
        $keyPath = $config['storage']['encryption_key_path'];
        if (!is_file($keyPath)) {
            throw new RuntimeException('Encryption key file missing. Generate it before enabling filesystem storage.');
        }
        $key = trim((string) file_get_contents($keyPath));
        if ($key === '') {
            throw new RuntimeException('Encryption key file is empty.');
        }

        return sodium_hex2bin($key);
    }
}

if (!function_exists('encrypt_payload')) {
    function encrypt_payload(string $plaintext, string $aad = ''): array
    {
        $key = get_encryption_key();
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $cipher = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($plaintext, $aad, $nonce, $key);

        return [
            'nonce' => sodium_bin2hex($nonce),
            'ciphertext' => sodium_bin2hex($cipher),
        ];
    }
}

if (!function_exists('decrypt_payload')) {
    function decrypt_payload(string $cipherHex, string $nonceHex, string $aad = ''): string
    {
        $key = get_encryption_key();
        $nonce = sodium_hex2bin($nonceHex);
        $cipher = sodium_hex2bin($cipherHex);
        $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($cipher, $aad, $nonce, $key);
        if ($plaintext === false) {
            throw new RuntimeException('Decryption failed');
        }

        return $plaintext;
    }
}

if (!function_exists('store_encrypted_file')) {
    function store_encrypted_file(int $userId, string $originalName, string $tmpPath): array
    {
        $config = get_config();
        $storagePath = rtrim($config['storage']['filesystem_path'], '/\\');
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0700, true);
        }
        $userDir = sprintf('%s/%d', $storagePath, $userId);
        if (!is_dir($userDir)) {
            mkdir($userDir, 0700, true);
        }

        $randomName = bin2hex(random_bytes(24));
        $targetPath = sprintf('%s/%s.dat', $userDir, $randomName);
        $contents = (string) file_get_contents($tmpPath);
        $encrypted = encrypt_payload($contents, (string) $userId);

        $payload = json_encode([
            'filename' => $randomName,
            'original' => $originalName,
            'nonce' => $encrypted['nonce'],
            'stored_at' => time(),
        ], JSON_THROW_ON_ERROR);

        file_put_contents($targetPath, $encrypted['ciphertext']);

        return [
            'storage_path' => $targetPath,
            'meta' => $payload,
        ];
    }
}

if (!function_exists('read_encrypted_file')) {
    function read_encrypted_file(int $userId, string $filename, string $nonceHex): string
    {
        $config = get_config();
        $filePath = sprintf('%s/%d/%s.dat', rtrim($config['storage']['filesystem_path'], '/\\'), $userId, $filename);
        if (!is_file($filePath)) {
            throw new RuntimeException('Encrypted file not found.');
        }
        $cipherHex = trim((string) file_get_contents($filePath));
        return decrypt_payload($cipherHex, $nonceHex, (string) $userId);
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd/m/Y H:i'): string
    {
        if (!$date) {
            return '-';
        }
        return (new DateTimeImmutable($date))->format($format);
    }
}
