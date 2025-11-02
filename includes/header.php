<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config = include __DIR__ . '/../config/env.php';
$appUrl = rtrim((string) ($config['APP_URL'] ?? ''), '/');
$basePath = $appUrl !== '' ? $appUrl : '';
$assetBase = ($basePath !== '' ? $basePath : '') . '/assets';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portale servizi digitali Agenzia Plinio">
    <title>Portale Servizi Agenzia Plinio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase . '/css/bootstrap.min.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase . '/css/ui-glass.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase . '/css/glass.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase . '/css/admin.css', ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="ui-glass-app">
<nav class="navbar navbar-expand-lg navbar-dark navbar-glass ui-glass-nav">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo htmlspecialchars($basePath . '/index.php', ENT_QUOTES, 'UTF-8'); ?>">
            <img src="<?php echo htmlspecialchars($assetBase . '/img/logo.svg', ENT_QUOTES, 'UTF-8'); ?>" alt="Agenzia Plinio" width="36" height="36" class="me-2">
            Agenzia Plinio
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalNavbar" aria-controls="portalNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="portalNavbar">
            <ul class="navbar-nav ms-auto">
                <?php if (!empty($_SESSION['user'])): ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($basePath . '/client/dashboard.php', ENT_QUOTES, 'UTF-8'); ?>">Area Clienti</a></li>
                    <?php if (!empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($basePath . '/admin/dashboard.php', ENT_QUOTES, 'UTF-8'); ?>">Area Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($basePath . '/auth/logout.php', ENT_QUOTES, 'UTF-8'); ?>">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($basePath . '/auth/login.php', ENT_QUOTES, 'UTF-8'); ?>">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($basePath . '/auth/register.php', ENT_QUOTES, 'UTF-8'); ?>">Registrati</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="content-wrapper">
