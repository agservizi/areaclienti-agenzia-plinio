<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use function db;

class FileStore
{
    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO files (user_id, filename_original, filename_storage, iv, mime, size) VALUES (:user_id, :filename_original, :filename_storage, :iv, :mime, :size)');
        $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':filename_original', $data['filename_original']);
        $stmt->bindValue(':filename_storage', $data['filename_storage']);
        $stmt->bindValue(':iv', $data['iv'], PDO::PARAM_LOB);
        $stmt->bindValue(':mime', $data['mime']);
        $stmt->bindValue(':size', $data['size'], PDO::PARAM_INT);
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    public static function forUser(int $userId): array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, filename_original, mime, size, created_at FROM files WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM files WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function all(): array
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT f.*, u.name AS user_name FROM files f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
