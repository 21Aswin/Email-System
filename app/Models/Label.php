<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Label
{
    public static function getByUser(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, name, color, created_at FROM labels WHERE user_id = :user_id ORDER BY name ASC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, string $name, string $color): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO labels (user_id, name, color) VALUES (:user_id, :name, :color)");
        $stmt->execute([
            'user_id' => $userId,
            'name' => trim($name),
            'color' => $color
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $userId, int $labelId, string $name, string $color): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE labels SET name = :name, color = :color WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            'id' => $labelId,
            'user_id' => $userId,
            'name' => trim($name),
            'color' => $color
        ]);
    }

    public static function delete(int $userId, int $labelId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM labels WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            'id' => $labelId,
            'user_id' => $userId
        ]);
    }
}
