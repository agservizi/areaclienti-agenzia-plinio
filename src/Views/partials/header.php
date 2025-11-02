<?php
/** @var string $pageTitle */
/** @var array $flashes */
/** @var array|null $currentUser */
/** @var string $layout */
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/icons/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<?php if ($layout === 'admin'): ?>
<body class="layout-admin">
<div class="admin-wrapper d-flex">
<?php elseif ($layout === 'public'): ?>
<body class="layout-public">
<header class="public-header shadow-sm">
    <div class="container-xl d-flex align-items-center justify-content-between py-3">
        <a class="logo" href="/">Agenzia Plinio</a>
        <nav class="public-nav">
            <a class="btn btn-outline-primary me-2" href="/auth/login">Accedi</a>
            <a class="btn btn-primary" href="/auth/register">Registrati</a>
        </nav>
    </div>
</header>
<main class="public-main container-xl py-5">
<?php else: ?>
<body class="layout-client">
<header class="client-header border-bottom">
    <div class="container-xl d-flex align-items-center justify-content-between py-3">
        <a class="logo" href="/client/dashboard">Agenzia Plinio</a>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary-subtle text-primary">Benvenuto, <?= htmlspecialchars($currentUser['name'] ?? '') ?></span>
            <form method="post" action="/auth/logout" class="m-0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <button class="btn btn-outline-primary" type="submit">Esci</button>
            </form>
        </div>
    </div>
</header>
<main class="client-main container-xl py-4">
<?php endif; ?>
