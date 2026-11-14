<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class UserSettings
{
    /**
     * Allowed setting values for strict validation
     */
    public const ALLOWED_THEMES = ['light', 'dark', 'system'];
    public const ALLOWED_DENSITIES = ['comfortable', 'compact'];
    public const ALLOWED_FOLDERS = ['inbox', 'starred', 'snoozed', 'sent', 'drafts', 'purchases'];
    public const ALLOWED_CATEGORIES = ['primary', 'updates', 'promotions', 'purchases'];
    public const ALLOWED_BADGE_MODES = ['unread', 'total'];
    public const ALLOWED_REPLY_MODES = ['reply', 'reply_all'];

    /**
     * Get settings for a user (creates default row if not exists)
     */
    public static function get(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $insert = $pdo->prepare("
                INSERT IGNORE INTO user_settings 
                (user_id, theme, density, default_folder, default_category, show_category_tabs, unread_badge_mode, in_app_notifications, browser_notifications, append_signature, default_reply_behavior, confirm_discard, auto_save_drafts)
                VALUES (:uid, 'light', 'comfortable', 'inbox', 'primary', 1, 'unread', 1, 0, 1, 'reply', 1, 1)
            ");
            $insert->execute(['uid' => $userId]);

            $stmt->execute(['uid' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        return $row;
    }

    /**
     * Update user settings with server-side validation
     */
    public static function update(int $userId, array $data): array
    {
        $pdo = Database::getConnection();
        $current = self::get($userId);

        $theme = in_array($data['theme'] ?? '', self::ALLOWED_THEMES, true) ? $data['theme'] : $current['theme'];
        $density = in_array($data['density'] ?? '', self::ALLOWED_DENSITIES, true) ? $data['density'] : $current['density'];
        $defaultFolder = in_array($data['default_folder'] ?? '', self::ALLOWED_FOLDERS, true) ? $data['default_folder'] : $current['default_folder'];
        $defaultCategory = in_array($data['default_category'] ?? '', self::ALLOWED_CATEGORIES, true) ? $data['default_category'] : $current['default_category'];
        $showCategoryTabs = isset($data['show_category_tabs']) ? (int)!empty($data['show_category_tabs']) : (int)$current['show_category_tabs'];
        $unreadBadgeMode = in_array($data['unread_badge_mode'] ?? '', self::ALLOWED_BADGE_MODES, true) ? $data['unread_badge_mode'] : $current['unread_badge_mode'];
        $inAppNotifications = isset($data['in_app_notifications']) ? (int)!empty($data['in_app_notifications']) : (int)$current['in_app_notifications'];
        $browserNotifications = isset($data['browser_notifications']) ? (int)!empty($data['browser_notifications']) : (int)$current['browser_notifications'];
        
        $signature = isset($data['signature']) ? mb_substr(trim((string)$data['signature']), 0, 1000) : $current['signature'];
        $appendSignature = isset($data['append_signature']) ? (int)!empty($data['append_signature']) : (int)$current['append_signature'];
        $defaultReply = in_array($data['default_reply_behavior'] ?? '', self::ALLOWED_REPLY_MODES, true) ? $data['default_reply_behavior'] : $current['default_reply_behavior'];
        $confirmDiscard = isset($data['confirm_discard']) ? (int)!empty($data['confirm_discard']) : (int)$current['confirm_discard'];
        $autoSaveDrafts = isset($data['auto_save_drafts']) ? (int)!empty($data['auto_save_drafts']) : (int)$current['auto_save_drafts'];

        $stmt = $pdo->prepare("
            UPDATE user_settings 
            SET theme = :theme,
                density = :density,
                default_folder = :default_folder,
                default_category = :default_category,
                show_category_tabs = :show_category_tabs,
                unread_badge_mode = :unread_badge_mode,
                in_app_notifications = :in_app_notifications,
                browser_notifications = :browser_notifications,
                signature = :signature,
                append_signature = :append_signature,
                default_reply_behavior = :default_reply_behavior,
                confirm_discard = :confirm_discard,
                auto_save_drafts = :auto_save_drafts,
                updated_at = NOW()
            WHERE user_id = :uid
        ");

        $stmt->execute([
            'theme' => $theme,
            'density' => $density,
            'default_folder' => $defaultFolder,
            'default_category' => $defaultCategory,
            'show_category_tabs' => $showCategoryTabs,
            'unread_badge_mode' => $unreadBadgeMode,
            'in_app_notifications' => $inAppNotifications,
            'browser_notifications' => $browserNotifications,
            'signature' => $signature,
            'append_signature' => $appendSignature,
            'default_reply_behavior' => $defaultReply,
            'confirm_discard' => $confirmDiscard,
            'auto_save_drafts' => $autoSaveDrafts,
            'uid' => $userId
        ]);

        return self::get($userId);
    }

    /**
     * Get list of blocked sender email addresses
     */
    public static function getBlockedSenders(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, email, created_at FROM blocked_senders WHERE user_id = :uid ORDER BY created_at DESC");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add an email address to the user's blocked senders list
     */
    public static function addBlockedSender(int $userId, string $email): bool
    {
        $cleanEmail = strtolower(trim($email));
        if (!filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }

        $user = User::findById($userId);
        if ($user && strtolower($user['email']) === $cleanEmail) {
            throw new Exception("You cannot block your own email address.");
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT IGNORE INTO blocked_senders (user_id, email, created_at) VALUES (:uid, :email, NOW())");
        return $stmt->execute(['uid' => $userId, 'email' => $cleanEmail]);
    }

    /**
     * Remove an email from the user's blocked list (by ID or email address)
     */
    public static function removeBlockedSender(int $userId, string|int $identifier): bool
    {
        $pdo = Database::getConnection();
        if (is_numeric($identifier) && (int)$identifier > 0 && !filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $stmt = $pdo->prepare("DELETE FROM blocked_senders WHERE user_id = :uid AND id = :id");
            return $stmt->execute(['uid' => $userId, 'id' => (int)$identifier]);
        }
        $cleanEmail = strtolower(trim((string)$identifier));
        $stmt = $pdo->prepare("DELETE FROM blocked_senders WHERE user_id = :uid AND email = :email");
        return $stmt->execute(['uid' => $userId, 'email' => $cleanEmail]);
    }

    /**
     * Check if a sender is blocked by a recipient
     */
    public static function isSenderBlocked(int $userId, string $email): bool
    {
        $cleanEmail = strtolower(trim($email));
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT 1 FROM blocked_senders WHERE user_id = :uid AND email = :email LIMIT 1");
        $stmt->execute(['uid' => $userId, 'email' => $cleanEmail]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Update user profile display name and optional avatar
     */
    public static function updateProfile(int $userId, string $displayName, ?string $avatarPath = null): bool
    {
        $cleanName = trim($displayName);
        if (mb_strlen($cleanName) < 2 || mb_strlen($cleanName) > 100) {
            throw new Exception("Display name must be between 2 and 100 characters.");
        }

        $pdo = Database::getConnection();
        if ($avatarPath !== null) {
            $stmt = $pdo->prepare("UPDATE users SET display_name = :name, avatar_path = :avatar, updated_at = NOW() WHERE id = :uid");
            return $stmt->execute(['name' => $cleanName, 'avatar' => $avatarPath, 'uid' => $userId]);
        }

        $stmt = $pdo->prepare("UPDATE users SET display_name = :name, updated_at = NOW() WHERE id = :uid");
        return $stmt->execute(['name' => $cleanName, 'uid' => $userId]);
    }

    /**
     * Secure password change with current password verification
     */
    public static function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'New password must be at least 6 characters long.'];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($currentPassword, $hash)) {
            return ['success' => false, 'message' => 'Current password verification failed.'];
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        $updateStmt = $pdo->prepare("UPDATE users SET password_hash = :pwd, updated_at = NOW() WHERE id = :uid");
        $updateStmt->execute(['pwd' => $newHash, 'uid' => $userId]);

        return ['success' => true, 'message' => 'Password updated successfully.'];
    }

    /**
     * Delete user account with password verification and cascade cleanup
     */
    public static function deleteAccount(int $userId, string $password): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($password, $hash)) {
            return ['success' => false, 'message' => 'Password confirmation incorrect. Cannot delete account.'];
        }

        $delStmt = $pdo->prepare("DELETE FROM users WHERE id = :uid");
        $delStmt->execute(['uid' => $userId]);

        return ['success' => true, 'message' => 'Account and all associated messages permanently deleted.'];
    }
}
