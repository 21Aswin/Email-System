<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        if ($user && !array_key_exists('avatar_path', $user)) {
            $user['avatar_path'] = null;
        }
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => strtolower(trim($email))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(string $displayName, string $email, string $password): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO users (display_name, email, password_hash, status, created_at)
            VALUES (:display_name, :email, :password_hash, 'active', NOW())
        ");
        $stmt->execute([
            'display_name' => trim($displayName),
            'email' => strtolower(trim($email)),
            'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 10])
        ]);

        $userId = (int)$pdo->lastInsertId();

        // Create default starter labels for new user
        $labelStmt = $pdo->prepare("INSERT INTO labels (user_id, name, color) VALUES (:user_id, :name, :color)");
        $defaultLabels = [
            ['Client Projects', '#1a73e8'],
            ['Invoices & Finance', '#188038'],
            ['Priority High', '#d93025'],
            ['Company Updates', '#f29900']
        ];
        foreach ($defaultLabels as $lbl) {
            $labelStmt->execute([
                'user_id' => $userId,
                'name' => $lbl[0],
                'color' => $lbl[1]
            ]);
        }

        return $userId;
    }
}
