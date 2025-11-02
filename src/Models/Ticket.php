<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use function db;

class Ticket
{
    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO tickets (user_id, subject, status, messages) VALUES (:user_id, :subject, :status, :messages)');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'subject' => $data['subject'],
            'status' => $data['status'],
            'messages' => json_encode($data['messages'], JSON_UNESCAPED_UNICODE),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function forUser(int $userId): array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM tickets WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['messages'] = $row['messages'] ? json_decode((string) $row['messages'], true) : [];
        }
        return $rows;
    }

    public static function latestForUser(int $userId, int $limit = 5): array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM tickets WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['messages'] = $row['messages'] ? json_decode((string) $row['messages'], true) : [];
        }
        return $rows;
    }

    public static function countOpenForUser(int $userId): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tickets WHERE user_id = :user_id AND status != 'closed'");
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public static function all(): array
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT t.*, u.name AS user_name FROM tickets t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['messages'] = $row['messages'] ? json_decode((string) $row['messages'], true) : [];
        }
        return $rows;
    }

    public static function latest(int $limit = 5): array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT t.*, u.name AS user_name FROM tickets t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['messages'] = $row['messages'] ? json_decode((string) $row['messages'], true) : [];
        }
        return $rows;
    }

    public static function countByStatus(string $status): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM tickets WHERE status = :status');
        $stmt->execute(['status' => $status]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public static function appendMessage(int $id, array $message): bool
    {
        $pdo = db();
        $ticket = self::find($id);
        if (!$ticket) {
            return false;
        }
        $messages = $ticket['messages'] ?: [];
        $messages[] = $message;
        $stmt = $pdo->prepare('UPDATE tickets SET messages = :messages, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'messages' => json_encode($messages, JSON_UNESCAPED_UNICODE),
            'id' => $id,
        ]);
    }

    public static function find(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['messages'] = $row['messages'] ? json_decode((string) $row['messages'], true) : [];
        return $row;
    }
}
