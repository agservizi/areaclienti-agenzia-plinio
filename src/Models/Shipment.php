<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use function db;

class Shipment
{
    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO shipments (user_id, tracking_code, sender, recipient, weight, dimensions, status) VALUES (:user_id, :tracking_code, :sender, :recipient, :weight, :dimensions, :status)');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'tracking_code' => $data['tracking_code'],
            'sender' => json_encode($data['sender'], JSON_UNESCAPED_UNICODE),
            'recipient' => json_encode($data['recipient'], JSON_UNESCAPED_UNICODE),
            'weight' => $data['weight'],
            'dimensions' => $data['dimensions'],
            'status' => $data['status'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function countForUser(int $userId): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM shipments WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public static function countCreatedToday(): int
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT COUNT(*) as total FROM shipments WHERE DATE(created_at) = CURDATE()');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public static function forUser(int $userId): array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM shipments WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['sender'] = $row['sender'] ? json_decode((string) $row['sender'], true) : [];
            $row['recipient'] = $row['recipient'] ? json_decode((string) $row['recipient'], true) : [];
        }
        return $rows;
    }

    public static function latestForUser(int $userId, int $limit = 5): array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM shipments WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['sender'] = $row['sender'] ? json_decode((string) $row['sender'], true) : [];
            $row['recipient'] = $row['recipient'] ? json_decode((string) $row['recipient'], true) : [];
        }
        return $rows;
    }

    public static function all(): array
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT s.*, u.name AS user_name FROM shipments s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['sender'] = $row['sender'] ? json_decode((string) $row['sender'], true) : [];
            $row['recipient'] = $row['recipient'] ? json_decode((string) $row['recipient'], true) : [];
        }
        return $rows;
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare('UPDATE shipments SET status = :status, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);
    }

    public static function findByTracking(string $code): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM shipments WHERE tracking_code = :code');
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['sender'] = $row['sender'] ? json_decode((string) $row['sender'], true) : [];
        $row['recipient'] = $row['recipient'] ? json_decode((string) $row['recipient'], true) : [];
        return $row;
    }
}
