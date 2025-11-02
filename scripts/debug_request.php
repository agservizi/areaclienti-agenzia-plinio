<?php

declare(strict_types=1);

if ($argc < 2) {
    fwrite(STDERR, "Usage: php scripts/debug_request.php <path> [method]\n");
    exit(1);
}

$path = $argv[1];
$method = $argv[2] ?? 'GET';

$_SERVER['REQUEST_METHOD'] = strtoupper($method);
$_SERVER['REQUEST_URI'] = $path;
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require __DIR__ . '/../public/index.php';
