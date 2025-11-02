<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use function db;

class SPIDRequest
{
    public static function create(array $data): array
    {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO spid_requests (user_id, status, data) VALUES (:user_id, :status, :data)');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'status' => $data['status'],
            'data' => json_encode($data['data'], JSON_UNESCAPED_UNICODE),
        ]);

        return self::find((int) $pdo->lastInsertId()) ?? [];
    }

    public static function find(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM spid_requests WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['data'] = $row['data'] ? json_decode((string) $row['data'], true) : [];
        return $row;
    }

    public static function countForUser(int $userId): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM spid_requests WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public static function countByStatus(string $status): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM spid_requests WHERE status = :status');
        $stmt->execute(['status' => $status]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public static function latest(int $limit = 5): array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT sr.*, u.name AS user_name FROM spid_requests sr JOIN users u ON sr.user_id = u.id ORDER BY sr.created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['data'] = $row['data'] ? json_decode((string) $row['data'], true) : [];
        }
        return $rows;
    }

    public static function all(): array
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT sr.*, u.name AS user_name, u.email FROM spid_requests sr JOIN users u ON sr.user_id = u.id ORDER BY sr.created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['data'] = $row['data'] ? json_decode((string) $row['data'], true) : [];
        }
        return $rows;
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare('UPDATE spid_requests SET status = :status, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'status' => $status,
            'id' => $id,
        ]);
    }
}
