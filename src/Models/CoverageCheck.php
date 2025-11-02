<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use function db;

class CoverageCheck
{
    public static function create(array $data): array
    {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO coverage_checks (user_id, address, operator, result) VALUES (:user_id, :address, :operator, :result)');
        $resultJson = json_encode($data['result'], JSON_UNESCAPED_UNICODE);
        $stmt->execute([
            'user_id' => $data['user_id'],
            'address' => $data['address'],
            'operator' => $data['operator'],
            'result' => $resultJson,
        ]);
        $insertedId = (int) $pdo->lastInsertId();
        return self::find($insertedId) ?? [];
    }

    public static function find(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM coverage_checks WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['result'] = $row['result'] ? json_decode((string) $row['result'], true) : [];
        return $row;
    }

    public static function latestForUser(int $userId, int $limit = 5): array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM coverage_checks WHERE user_id = :user_id ORDER BY checked_at DESC LIMIT :limit');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['result'] = $row['result'] ? json_decode((string) $row['result'], true) : [];
        }
        return $rows;
    }
}
