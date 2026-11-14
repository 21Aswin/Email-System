<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Attachment
{
    public static function getByMessageId(int $messageId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, message_id, original_name, storage_name, mime_type, size_bytes, created_at FROM attachments WHERE message_id = :message_id");
        $stmt->execute(['message_id' => $messageId]);
        return $stmt->fetchAll();
    }

    public static function findByIdForUser(int $attachmentId, int $userId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.* 
            FROM attachments a
            LEFT JOIN messages m ON a.message_id = m.id
            LEFT JOIN mailbox_items mb ON mb.message_id = m.id
            WHERE a.id = :attachment_id 
              AND (mb.user_id = :user_id_mb OR a.user_id = :user_id_att)
            LIMIT 1
        ");
        $stmt->execute([
            'attachment_id' => $attachmentId,
            'user_id_mb' => $userId,
            'user_id_att' => $userId
        ]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(?int $messageId, string $originalName, string $storageName, string $mimeType, int $sizeBytes, ?int $userId = null): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO attachments (user_id, message_id, original_name, storage_name, mime_type, size_bytes, created_at)
            VALUES (:user_id, :message_id, :original_name, :storage_name, :mime_type, :size_bytes, NOW())
        ");
        $stmt->execute([
            'user_id' => $userId,
            'message_id' => ($messageId && $messageId > 0) ? $messageId : null,
            'original_name' => $originalName,
            'storage_name' => $storageName,
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes
        ]);
        return (int)$pdo->lastInsertId();
    }
}
