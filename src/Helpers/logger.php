<?php

declare(strict_types=1);

use DateTimeImmutable;

function app_log(string $channel, string $message, array $context = []): void
{
    $dir = __DIR__ . '/../../logs';
    if (!is_dir($dir)) {
        mkdir($dir, 0770, true);
    }

    $time = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
    $payload = $context ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
    $line = sprintf('[%s] %s: %s %s%s', $time, strtoupper($channel), $message, $payload, PHP_EOL);
    file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX);
}
