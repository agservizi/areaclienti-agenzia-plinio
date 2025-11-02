<?php

declare(strict_types=1);

use RuntimeException;

function crypto_get_key(): string
{
    $key = env('APP_KEY');
    if (!$key) {
        throw new RuntimeException('Chiave di cifratura non configurata. Imposta APP_KEY nel file .env');
    }

    if (strlen($key) !== 32) {
        $key = hash('sha256', $key, true);
    }

    return $key;
}

function crypto_encrypt_file(string $tmpPath, string $storagePath): array
{
    $contents = file_get_contents($tmpPath);
    if ($contents === false) {
        throw new RuntimeException('Impossibile leggere il file da cifrare');
    }

    $key = crypto_get_key();
    if (function_exists('sodium_crypto_secretbox')) {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($contents, $nonce, $key);
    } else {
        $nonce = random_bytes(16);
        $cipher = openssl_encrypt($contents, 'aes-256-ctr', $key, OPENSSL_RAW_DATA, $nonce);
        if ($cipher === false) {
            throw new RuntimeException('Cifratura OpenSSL fallita');
        }
    }

    $storedName = bin2hex(random_bytes(16));
    $destination = rtrim($storagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $storedName;
    if (!is_dir($storagePath)) {
        mkdir($storagePath, 0770, true);
    }
    file_put_contents($destination, $cipher);

    return [
        'stored_name' => $storedName,
        'nonce' => $nonce,
        'size' => strlen($cipher),
    ];
}

function crypto_decrypt_file(string $storedName, string $storagePath, string $nonce): string
{
    $path = rtrim($storagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $storedName;
    if (!is_file($path)) {
        throw new RuntimeException('File cifrato non trovato');
    }

    $cipher = file_get_contents($path);
    if ($cipher === false) {
        throw new RuntimeException('Impossibile leggere il file cifrato');
    }

    $key = crypto_get_key();
    if (function_exists('sodium_crypto_secretbox_open')) {
        $plain = sodium_crypto_secretbox_open($cipher, $nonce, $key);
        if ($plain === false) {
            throw new RuntimeException('Decifratura Sodium fallita');
        }
    } else {
        $plain = openssl_decrypt($cipher, 'aes-256-ctr', $key, OPENSSL_RAW_DATA, $nonce);
        if ($plain === false) {
            throw new RuntimeException('Decifratura OpenSSL fallita');
        }
    }

    return $plain;
}
