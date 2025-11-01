<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

ensure_csrf_token();
$config = get_config();
$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . $config['app']['name'] : $config['app']['name'];
$user = current_user();
?><!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= escape($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body data-csrf="<?= escape(get_csrf_token()) ?>">
<header class="header-minimal sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand navbar-brand-logo" href="/">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect width="24" height="24" rx="8" fill="#2a64d6"></rect>
                    <path d="M7 7h10v2H7zM7 11h6v2H7zM7 15h10v2H7z" fill="#ffffff"></path>
                </svg>
                <span>AG Servizi</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primaryNav" aria-controls="primaryNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="primaryNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!$user): ?>
                        <li class="nav-item"><a class="nav-link" href="/index.php">Accedi</a></li>
                        <li class="nav-item"><a class="nav-link" href="/register.php">Registrati</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/client/dashboard.php">Area Clienti</a></li>
                        <?php if (has_role(ROLE_ADMIN)): ?>
                            <li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Admin</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="/logout.php">Esci</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main>
