<?php

declare(strict_types=1);

function render(string $view, array $data = [], array $options = []): void
{
    secure_session_start();
    $layout = $options['layout'] ?? 'client';
    $viewPath = __DIR__ . '/../Views/' . $view . '.php';
    if (!is_file($viewPath)) {
        http_response_code(404);
        echo 'Pagina non trovata';
        return;
    }

    $pageTitle = $data['page_title'] ?? ($options['title'] ?? 'Agenzia Plinio');
    $currentUser = current_user();
    $flashes = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);

    extract($data, EXTR_SKIP);

    include __DIR__ . '/../Views/partials/header.php';
    if ($layout === 'admin') {
        include __DIR__ . '/../Views/partials/sidebar.php';
    } elseif (!empty($flashes)) {
        echo '<div class="container-xl mt-3">';
        include __DIR__ . '/../Views/partials/flash.php';
        echo '</div>';
    }
    include $viewPath;
    include __DIR__ . '/../Views/partials/footer.php';
}

function render_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void
{
    secure_session_start();
    $_SESSION['flashes'][$type][] = $message;
}
