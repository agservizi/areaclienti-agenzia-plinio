<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use function db;

class User
{
    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, phone, role) VALUES (:name, :email, :password, :phone, :role)');
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'role' => $data['role'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function updateProfile(int $id, array $data): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare('UPDATE users SET name = :name, email = :email, phone = :phone, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'id' => $id,
        ]);
    }

    public static function updateRole(int $id, string $role): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare('UPDATE users SET role = :role, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            'role' => $role,
            'id' => $id,
        ]);
    }

    public static function all(): array
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allClients(): array
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT * FROM users WHERE role = 'client' ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function count(): int
    {
        $pdo = db();
        $stmt = $pdo->query('SELECT COUNT(*) as total FROM users');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public static function touchLogin(int $id): void
    {
        $pdo = db();
        $stmt = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
