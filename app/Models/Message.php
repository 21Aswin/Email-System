<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Message
{
    /**
     * Check and lazily restore any snoozed messages whose snooze timestamp has passed
     */
    public static function checkSnoozeExpiries(?int $userId = null): int
    {
        $pdo = Database::getConnection();
        $sql = "
            UPDATE mailbox_items 
            SET folder = 'inbox', 
                snoozed_until = NULL,
                is_read = 0,
                created_at = NOW() 
            WHERE folder = 'snoozed' 
              AND snoozed_until IS NOT NULL 
              AND snoozed_until <= NOW()
        ";
        if ($userId !== null) {
            $sql .= " AND user_id = :uid";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['uid' => $userId]);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        return (int)$stmt->rowCount();
    }

    /**
     * Get counts of unread and total items for mailbox folders
     */
    public static function getFolderCounts(int $userId): array
    {
        self::checkSnoozeExpiries($userId);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT folder, 
                   COUNT(*) as total_count,
                   SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_count
            FROM mailbox_items
            WHERE user_id = :user_id
            GROUP BY folder
        ");
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();

        $counts = [
            'inbox' => ['total' => 0, 'unread' => 0],
            'starred' => ['total' => 0, 'unread' => 0],
            'snoozed' => ['total' => 0, 'unread' => 0],
            'sent' => ['total' => 0, 'unread' => 0],
            'drafts' => ['total' => 0, 'unread' => 0],
            'purchases' => ['total' => 0, 'unread' => 0],
            'important' => ['total' => 0, 'unread' => 0],
            'scheduled' => ['total' => 0, 'unread' => 0],
            'archive' => ['total' => 0, 'unread' => 0],
            'spam' => ['total' => 0, 'unread' => 0],
            'trash' => ['total' => 0, 'unread' => 0]
        ];

        foreach ($rows as $r) {
            $f = $r['folder'];
            if (isset($counts[$f])) {
                $counts[$f]['total'] = (int)$r['total_count'];
                $counts[$f]['unread'] = (int)($r['unread_count'] ?? 0);
            }
        }

        // Starred count across active folders
        $starredStmt = $pdo->prepare("
            SELECT COUNT(*) FROM mailbox_items 
            WHERE user_id = :user_id AND is_starred = 1 AND folder NOT IN ('trash', 'spam')
        ");
        $starredStmt->execute(['user_id' => $userId]);
        $counts['starred']['total'] = (int)$starredStmt->fetchColumn();

        // Important count across active folders
        $importantStmt = $pdo->prepare("
            SELECT COUNT(*), SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) FROM mailbox_items 
            WHERE user_id = :user_id AND is_important = 1 AND folder NOT IN ('trash', 'spam')
        ");
        $importantStmt->execute(['user_id' => $userId]);
        $impRow = $importantStmt->fetch(PDO::FETCH_NUM);
        $counts['important']['total'] = (int)($impRow[0] ?? 0);
        $counts['important']['unread'] = (int)($impRow[1] ?? 0);

        // Purchases smart category count
        $purchasesStmt = $pdo->prepare("
            SELECT COUNT(*), SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) FROM mailbox_items 
            WHERE user_id = :user_id AND category = 'purchases' AND folder NOT IN ('trash', 'spam')
        ");
        $purchasesStmt->execute(['user_id' => $userId]);
        $purchRow = $purchasesStmt->fetch(PDO::FETCH_NUM);
        $counts['purchases']['total'] = (int)($purchRow[0] ?? 0);
        $counts['purchases']['unread'] = (int)($purchRow[1] ?? 0);

        return $counts;
    }

    /**
     * Get messages list with filtering, category, label, and search
     */
    public static function getList(
        int $userId,
        string $folder = 'inbox',
        string $category = '',
        ?int $labelId = null,
        string $search = '',
        array $advancedFilters = [],
        int $page = 1,
        int $limit = 25
    ): array {
        self::checkSnoozeExpiries($userId);

        $pdo = Database::getConnection();
        $offset = ($page - 1) * $limit;

        $params = ['user_id' => $userId];
        $whereClauses = ["mb.user_id = :user_id"];

        if ($folder === 'starred') {
            $whereClauses[] = "mb.is_starred = 1 AND mb.folder NOT IN ('trash', 'spam')";
        } elseif ($folder === 'snoozed') {
            $whereClauses[] = "mb.folder = 'snoozed'";
        } elseif ($folder === 'all') {
            $whereClauses[] = "mb.folder NOT IN ('trash', 'spam')";
        } elseif ($folder === 'important') {
            $whereClauses[] = "mb.is_important = 1 AND mb.folder NOT IN ('trash', 'spam')";
        } elseif ($folder === 'purchases') {
            $whereClauses[] = "mb.category = 'purchases' AND mb.folder NOT IN ('trash', 'spam')";
        } elseif ($folder === 'scheduled') {
            $whereClauses[] = "mb.folder = 'scheduled'";
        } elseif ($labelId !== null) {
            $whereClauses[] = "mb.folder NOT IN ('trash', 'spam')";
            $whereClauses[] = "EXISTS (SELECT 1 FROM message_labels ml WHERE ml.message_id = m.id AND ml.label_id = :label_id)";
            $params['label_id'] = $labelId;
        } else {
            $whereClauses[] = "mb.folder = :folder";
            $params['folder'] = $folder;

            if ($folder === 'inbox' && !empty($category) && in_array($category, ['primary', 'updates', 'promotions', 'purchases'], true)) {
                $whereClauses[] = "mb.category = :category";
                $params['category'] = $category;
            }
        }

        // Basic Search
        if (!empty($search)) {
            $searchTerm = '%' . trim($search) . '%';
            $whereClauses[] = "(m.subject LIKE :search1 OR m.sender_name LIKE :search2 OR m.sender_email LIKE :search3 OR m.snippet LIKE :search4)";
            $params['search1'] = $searchTerm;
            $params['search2'] = $searchTerm;
            $params['search3'] = $searchTerm;
            $params['search4'] = $searchTerm;
        }

        // Advanced Filters
        if (!empty($advancedFilters['from'])) {
            $whereClauses[] = "(m.sender_name LIKE :adv_from1 OR m.sender_email LIKE :adv_from2)";
            $params['adv_from1'] = '%' . $advancedFilters['from'] . '%';
            $params['adv_from2'] = '%' . $advancedFilters['from'] . '%';
        }
        if (!empty($advancedFilters['to'])) {
            $whereClauses[] = "EXISTS (SELECT 1 FROM message_recipients mr WHERE mr.message_id = m.id AND mr.email LIKE :adv_to)";
            $params['adv_to'] = '%' . $advancedFilters['to'] . '%';
        }
        if (!empty($advancedFilters['subject'])) {
            $whereClauses[] = "m.subject LIKE :adv_subj";
            $params['adv_subj'] = '%' . $advancedFilters['subject'] . '%';
        }
        if (!empty($advancedFilters['has_attachment'])) {
            $whereClauses[] = "EXISTS (SELECT 1 FROM attachments att WHERE att.message_id = m.id)";
        }
        if (!empty($advancedFilters['date_from'])) {
            $whereClauses[] = "m.created_at >= :adv_date_from";
            $params['adv_date_from'] = $advancedFilters['date_from'] . ' 00:00:00';
        }
        if (!empty($advancedFilters['date_to'])) {
            $whereClauses[] = "m.created_at <= :adv_date_to";
            $params['adv_date_to'] = $advancedFilters['date_to'] . ' 23:59:59';
        }

        $whereSql = implode(' AND ', $whereClauses);

        // Count total
        $countSql = "
            SELECT COUNT(*) 
            FROM mailbox_items mb
            JOIN messages m ON mb.message_id = m.id
            WHERE {$whereSql}
        ";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();

        // Fetch paginated messages
        $sql = "
            SELECT 
                mb.id as mailbox_item_id,
                mb.folder,
                mb.category,
                mb.is_read,
                mb.is_starred,
                mb.is_important,
                mb.snoozed_until,
                mb.scheduled_at,
                mb.created_at as mailbox_date,
                m.id as message_id,
                m.sender_name,
                m.sender_email,
                m.subject,
                m.snippet,
                m.sent_at,
                m.received_at,
                m.created_at as message_date,
                (SELECT COUNT(*) FROM attachments a WHERE a.message_id = m.id) as attachment_count,
                (SELECT GROUP_CONCAT(CONCAT(IFNULL(mr.display_name, ''), ' <', mr.email, '>') SEPARATOR ', ') FROM message_recipients mr WHERE mr.message_id = m.id AND mr.recipient_type = 'to') as recipient_display
            FROM mailbox_items mb
            JOIN messages m ON mb.message_id = m.id
            WHERE {$whereSql}
            ORDER BY mb.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $messages = $stmt->fetchAll();

        // Attach labels to each message
        if (!empty($messages)) {
            $msgIds = array_column($messages, 'message_id');
            $placeholders = implode(',', array_fill(0, count($msgIds), '?'));
            
            $lblStmt = $pdo->prepare("
                SELECT ml.message_id, l.id, l.name, l.color
                FROM message_labels ml
                JOIN labels l ON ml.label_id = l.id
                WHERE ml.message_id IN ({$placeholders}) AND l.user_id = ?
            ");
            $lblStmt->execute(array_merge($msgIds, [$userId]));
            $labelsGrouped = [];
            while ($row = $lblStmt->fetch()) {
                $labelsGrouped[$row['message_id']][] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'color' => $row['color']
                ];
            }

            foreach ($messages as &$msg) {
                $msg['labels'] = $labelsGrouped[$msg['message_id']] ?? [];
            }
        }

        return [
            'messages' => $messages,
            'total' => $totalItems,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($totalItems / max($limit, 1))
        ];
    }

    /**
     * Get full details of a specific message for reading
     */
    public static function getDetails(int $userId, int $messageId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                mb.id as mailbox_item_id,
                mb.folder,
                mb.category,
                mb.is_read,
                mb.is_starred,
                mb.is_important,
                mb.created_at as mailbox_date,
                m.id as message_id,
                m.owner_user_id,
                m.sender_name,
                m.sender_email,
                m.subject,
                m.body_text,
                m.body_html,
                m.snippet,
                m.sent_at,
                m.received_at,
                m.created_at as message_date
            FROM mailbox_items mb
            JOIN messages m ON mb.message_id = m.id
            WHERE mb.user_id = :user_id AND m.id = :message_id
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId, 'message_id' => $messageId]);
        $message = $stmt->fetch();

        if (!$message) {
            return null;
        }

        // Automatically mark as read if not read
        if ($message['is_read'] == 0) {
            self::markAsRead($userId, [$messageId], true);
            $message['is_read'] = 1;
        }

        // Fetch recipients
        $recStmt = $pdo->prepare("SELECT recipient_type, email, display_name FROM message_recipients WHERE message_id = :message_id");
        $recStmt->execute(['message_id' => $messageId]);
        $message['recipients'] = $recStmt->fetchAll();

        // Fetch attachments
        $attStmt = $pdo->prepare("SELECT id, original_name, storage_name, mime_type, size_bytes FROM attachments WHERE message_id = :message_id");
        $attStmt->execute(['message_id' => $messageId]);
        $message['attachments'] = $attStmt->fetchAll();

        // Fetch labels
        $lblStmt = $pdo->prepare("
            SELECT l.id, l.name, l.color
            FROM message_labels ml
            JOIN labels l ON ml.label_id = l.id
            WHERE ml.message_id = :message_id AND l.user_id = :user_id
        ");
        $lblStmt->execute(['message_id' => $messageId, 'user_id' => $userId]);
        $message['labels'] = $lblStmt->fetchAll();

        return $message;
    }

    /**
     * Mark messages as read / unread
     */
    public static function markAsRead(int $userId, array $messageIds, bool $isRead = true): void
    {
        if (empty($messageIds)) return;
        $pdo = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
        $stmt = $pdo->prepare("
            UPDATE mailbox_items 
            SET is_read = ? 
            WHERE user_id = ? AND message_id IN ({$placeholders})
        ");
        $stmt->execute(array_merge([$isRead ? 1 : 0, $userId], $messageIds));
    }

    /**
     * Toggle star on a message
     */
    public static function toggleStar(int $userId, int $messageId, ?bool $starred = null): bool
    {
        $pdo = Database::getConnection();
        if ($starred === null) {
            $stmt = $pdo->prepare("
                UPDATE mailbox_items 
                SET is_starred = CASE WHEN is_starred = 1 THEN 0 ELSE 1 END
                WHERE user_id = :user_id AND message_id = :message_id
            ");
            $stmt->execute(['user_id' => $userId, 'message_id' => $messageId]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE mailbox_items 
                SET is_starred = :starred
                WHERE user_id = :user_id AND message_id = :message_id
            ");
            $stmt->execute([
                'starred' => $starred ? 1 : 0,
                'user_id' => $userId,
                'message_id' => $messageId
            ]);
        }

        // Return current status
        $statusStmt = $pdo->prepare("SELECT is_starred FROM mailbox_items WHERE user_id = :user_id AND message_id = :message_id LIMIT 1");
        $statusStmt->execute(['user_id' => $userId, 'message_id' => $messageId]);
        return (bool)$statusStmt->fetchColumn();
    }

    /**
     * Move messages to another folder (trash, archive, spam, inbox)
     */
    public static function moveToFolder(int $userId, array $messageIds, string $folder): void
    {
        if (empty($messageIds)) return;
        $pdo = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
        $stmt = $pdo->prepare("
            UPDATE mailbox_items 
            SET folder = ? 
            WHERE user_id = ? AND message_id IN ({$placeholders})
        ");
        $stmt->execute(array_merge([$folder, $userId], $messageIds));
    }

    /**
     * Toggle importance on a message
     */
    public static function toggleImportant(int $userId, int $messageId, ?bool $important = null): bool
    {
        $pdo = Database::getConnection();
        if ($important === null) {
            $stmt = $pdo->prepare("
                UPDATE mailbox_items 
                SET is_important = CASE WHEN is_important = 1 THEN 0 ELSE 1 END
                WHERE user_id = :user_id AND message_id = :message_id
            ");
            $stmt->execute(['user_id' => $userId, 'message_id' => $messageId]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE mailbox_items 
                SET is_important = :important
                WHERE user_id = :user_id AND message_id = :message_id
            ");
            $stmt->execute([
                'important' => $important ? 1 : 0,
                'user_id' => $userId,
                'message_id' => $messageId
            ]);
        }

        $statusStmt = $pdo->prepare("SELECT is_important FROM mailbox_items WHERE user_id = :user_id AND message_id = :message_id LIMIT 1");
        $statusStmt->execute(['user_id' => $userId, 'message_id' => $messageId]);
        return (bool)$statusStmt->fetchColumn();
    }

    /**
     * Snooze message until a specified timestamp
     */
    public static function snooze(int $userId, array $messageIds, string $untilDatetime): void
    {
        if (empty($messageIds)) return;
        $pdo = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
        $stmt = $pdo->prepare("
            UPDATE mailbox_items 
            SET folder = 'snoozed', snoozed_until = ? 
            WHERE user_id = ? AND message_id IN ({$placeholders})
        ");
        $stmt->execute(array_merge([$untilDatetime, $userId], $messageIds));
    }

    /**
     * Unsnooze and restore message back to inbox immediately
     */
    public static function unsnooze(int $userId, array $messageIds): void
    {
        if (empty($messageIds)) return;
        $pdo = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
        $stmt = $pdo->prepare("
            UPDATE mailbox_items 
            SET folder = 'inbox', snoozed_until = NULL, is_read = 0, created_at = NOW() 
            WHERE user_id = ? AND message_id IN ({$placeholders})
        ");
        $stmt->execute(array_merge([$userId], $messageIds));
    }

    /**
     * Set category (primary, updates, promotions, purchases)
     */
    public static function setCategory(int $userId, array $messageIds, string $category): void
    {
        if (empty($messageIds)) return;
        $valid = ['primary', 'updates', 'promotions', 'purchases'];
        if (!in_array($category, $valid, true)) return;

        $pdo = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
        $stmt = $pdo->prepare("
            UPDATE mailbox_items 
            SET category = ? 
            WHERE user_id = ? AND message_id IN ({$placeholders})
        ");
        $stmt->execute(array_merge([$category, $userId], $messageIds));
    }

    /**
     * Empty all messages in a specific folder (trash or spam) with safe transaction
     */
    public static function emptyFolder(int $userId, string $folder): int
    {
        if (!in_array($folder, ['trash', 'spam'], true)) {
            return 0;
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            // Find message IDs in this folder
            $stmtFind = $pdo->prepare("SELECT message_id FROM mailbox_items WHERE user_id = :uid AND folder = :folder");
            $stmtFind->execute(['uid' => $userId, 'folder' => $folder]);
            $msgIds = $stmtFind->fetchAll(PDO::FETCH_COLUMN);

            if (empty($msgIds)) {
                $pdo->rollBack();
                return 0;
            }

            $count = count($msgIds);
            $placeholders = implode(',', array_fill(0, $count, '?'));

            // Delete mailbox items
            $delMb = $pdo->prepare("DELETE FROM mailbox_items WHERE user_id = ? AND folder = ?");
            $delMb->execute([$userId, $folder]);

            // If owned messages have no other mailbox items, delete message
            $delMsg = $pdo->prepare("
                DELETE FROM messages 
                WHERE owner_user_id = ? AND id IN ({$placeholders})
                  AND id NOT IN (SELECT message_id FROM mailbox_items)
            ");
            $delMsg->execute(array_merge([$userId], $msgIds));

            $pdo->commit();
            return $count;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    public static function deletePermanently(int $userId, array $messageIds): void
    {
        if (empty($messageIds)) return;
        $pdo = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
        
        $pdo->beginTransaction();
        try {
            // Delete mailbox item
            $stmt = $pdo->prepare("
                DELETE FROM mailbox_items 
                WHERE user_id = ? AND folder = 'trash' AND message_id IN ({$placeholders})
            ");
            $stmt->execute(array_merge([$userId], $messageIds));

            // If message was owned by this user and has no other mailbox_items, delete message
            $stmtMsg = $pdo->prepare("
                DELETE FROM messages 
                WHERE owner_user_id = ? AND id IN ({$placeholders}) 
                  AND id NOT IN (SELECT message_id FROM mailbox_items)
            ");
            $stmtMsg->execute(array_merge([$userId], $messageIds));

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Apply or remove label
     */
    public static function toggleLabel(int $userId, int $messageId, int $labelId, bool $apply): void
    {
        $pdo = Database::getConnection();

        // Verify label belongs to user
        $check = $pdo->prepare("SELECT 1 FROM labels WHERE id = :label_id AND user_id = :user_id");
        $check->execute(['label_id' => $labelId, 'user_id' => $userId]);
        if (!$check->fetchColumn()) {
            return;
        }

        if ($apply) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO message_labels (message_id, label_id) VALUES (:message_id, :label_id)");
            $stmt->execute(['message_id' => $messageId, 'label_id' => $labelId]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM message_labels WHERE message_id = :message_id AND label_id = :label_id");
            $stmt->execute(['message_id' => $messageId, 'label_id' => $labelId]);
        }
    }
}
