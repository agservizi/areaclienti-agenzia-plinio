<?php

function getUserById(int $id, PDO $pdo): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

function findUserByEmail(string $email, PDO $pdo): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

function findUserByIdentifier(string $identifier, PDO $pdo): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM users WHERE email = :identifier OR username = :identifier LIMIT 1'
    );
    $stmt->execute(['identifier' => $identifier]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

function getAllUsers(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllServices(PDO $pdo, bool $onlyEnabled = false): array
{
    $sql = 'SELECT * FROM services';
    if ($onlyEnabled) {
        $sql .= ' WHERE enabled = 1';
    }
    $sql .= ' ORDER BY category, title';

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getServiceById(int $id, PDO $pdo): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);

    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    return $service ?: null;
}

function getServiceBySlug(string $slug, PDO $pdo): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM services WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);

    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    return $service ?: null;
}

function getUserRequests(int $userId, PDO $pdo): array
{
    $stmt = $pdo->prepare(
        'SELECT r.*, s.title AS service_title, s.slug AS service_slug
        FROM requests r
        LEFT JOIN services s ON s.id = r.service_id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC'
    );
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllRequests(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT r.*, u.name AS user_name, u.email AS user_email, s.title AS service_title
        FROM requests r
        LEFT JOIN users u ON u.id = r.user_id
        LEFT JOIN services s ON s.id = r.service_id
        ORDER BY r.created_at DESC'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserTickets(int $userId, PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllTickets(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT t.*, u.name AS user_name, u.email AS user_email
        FROM tickets t
        LEFT JOIN users u ON u.id = t.user_id
        ORDER BY t.created_at DESC'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserNotifications(int $userId, PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllNotifications(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT n.*, u.name AS user_name, u.email AS user_email
        FROM notifications n
        LEFT JOIN users u ON u.id = n.user_id
        ORDER BY n.created_at DESC'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserShipments(int $userId, PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT * FROM shipments WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllShipments(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT s.*, u.name AS user_name, u.email AS user_email
        FROM shipments s
        LEFT JOIN users u ON u.id = s.user_id
        ORDER BY s.created_at DESC'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserSimOrders(int $userId, PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT * FROM sim_orders WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllSimOrders(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT o.*, u.name AS user_name, u.email AS user_email
        FROM sim_orders o
        LEFT JOIN users u ON u.id = o.user_id
        ORDER BY o.created_at DESC'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserSpidRequests(int $userId, PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT * FROM spid_requests WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllSpidRequests(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT r.*, u.name AS user_name, u.email AS user_email
        FROM spid_requests r
        LEFT JOIN users u ON u.id = r.user_id
        ORDER BY r.created_at DESC'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserFiles(int $userId, PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT * FROM files WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllFiles(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT f.*, u.name AS user_name, u.email AS user_email
        FROM files f
        LEFT JOIN users u ON u.id = f.user_id
        ORDER BY f.created_at DESC'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAuditLogs(PDO $pdo, int $limit = 100): array
{
    $stmt = $pdo->prepare(
        'SELECT a.*, u.name AS user_name, u.email AS user_email
        FROM audit_logs a
        LEFT JOIN users u ON u.id = a.user_id
        ORDER BY a.created_at DESC
        LIMIT ?'
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCoverageChecks(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT c.*, u.name AS user_name, u.email AS user_email
        FROM coverage_checks c
        LEFT JOIN users u ON u.id = c.user_id
        ORDER BY c.checked_at DESC'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLoginAttempts(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM login_attempts ORDER BY last_attempt_at DESC');

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function recordAuditLog(PDO $pdo, ?int $userId, string $action, ?array $payload = null): void
{
    $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, payload) VALUES (?, ?, ?)');
    $stmt->execute([
        $userId,
        $action,
        $payload ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
    ]);
}

function recordLoginAttempt(PDO $pdo, string $email, bool $success): void
{
    if ($success) {
        $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE email = ?');
        $stmt->execute([$email]);

        return;
    }

    $stmt = $pdo->prepare('SELECT attempts FROM login_attempts WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $update = $pdo->prepare('UPDATE login_attempts SET attempts = attempts + 1, last_attempt_at = NOW() WHERE email = ?');
        $update->execute([$email]);
    } else {
        $insert = $pdo->prepare('INSERT INTO login_attempts (email, attempts, last_attempt_at) VALUES (?, 1, NOW())');
        $insert->execute([$email]);
    }
}

function statusBadge(string $status): string
{
    $map = [
        'pending' => 'warning',
        'processing' => 'info',
        'completed' => 'success',
        'rejected' => 'danger',
        'approved' => 'success',
        'active' => 'success',
        'cancelled' => 'secondary',
        'in_progress' => 'info',
        'closed' => 'dark',
        'open' => 'primary',
        'delivered' => 'success',
        'in_transit' => 'info',
        'created' => 'secondary',
        'in_review' => 'info',
        'shipped' => 'primary',
    ];

    return $map[strtolower($status)] ?? 'light';
}

function isAdmin(array $user): bool
{
    return isset($user['role']) && $user['role'] === 'admin';
}
