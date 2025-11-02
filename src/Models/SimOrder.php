<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use function db;

class SimOrder
{
    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO sim_orders (user_id, operator, plan, status, details) VALUES (:user_id, :operator, :plan, :status, :details)');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'operator' => $data['operator'],
            'plan' => $data['plan'],
            'status' => $data['status'],
            'details' => json_encode($data['details'], JSON_UNESCAPED_UNICODE),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function countForUser(int $userId): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM sim_orders WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public static function countByStatus(string $status): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM sim_orders WHERE status = :status');
        $stmt->execute(['status' => $status]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public static function latestForUser(int $userId, int $limit = 5): array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM sim_orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['details'] = $row['details'] ? json_decode((string) $row['details'], true) : [];
        }
        return $rows;
    }

    public static function all(): array
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT so.*, u.name AS user_name FROM sim_orders so LEFT JOIN users u ON so.user_id = u.id ORDER BY so.created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['details'] = $row['details'] ? json_decode((string) $row['details'], true) : [];
        }
        return $rows;
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare('UPDATE sim_orders SET status = :status WHERE id = :id');
        return $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);
    }
}
