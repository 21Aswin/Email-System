<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Env::load(__DIR__ . '/../.env');

echo "=== Running User Settings & Blocked Senders Migration ===\n";

try {
    $pdo = \App\Core\Database::getConnection();

    // 1. Add avatar_path to users table if not exists
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar_path'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL AFTER display_name");
        echo "[1/3] Added avatar_path column to users table.\n";
    } else {
        echo "[1/3] avatar_path column already exists.\n";
    }

    // 2. Create user_settings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_settings` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `theme` ENUM('light', 'dark', 'system') NOT NULL DEFAULT 'light',
            `density` ENUM('comfortable', 'compact') NOT NULL DEFAULT 'comfortable',
            `default_folder` VARCHAR(40) NOT NULL DEFAULT 'inbox',
            `default_category` VARCHAR(40) NOT NULL DEFAULT 'primary',
            `show_category_tabs` TINYINT(1) NOT NULL DEFAULT 1,
            `unread_badge_mode` ENUM('unread', 'total') NOT NULL DEFAULT 'unread',
            `in_app_notifications` TINYINT(1) NOT NULL DEFAULT 1,
            `browser_notifications` TINYINT(1) NOT NULL DEFAULT 0,
            `signature` TEXT NULL,
            `append_signature` TINYINT(1) NOT NULL DEFAULT 1,
            `default_reply_behavior` ENUM('reply', 'reply_all') NOT NULL DEFAULT 'reply',
            `confirm_discard` TINYINT(1) NOT NULL DEFAULT 1,
            `auto_save_drafts` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `uniq_user_settings_uid` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[2/3] Created user_settings table.\n";

    // 3. Create blocked_senders table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `blocked_senders` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `email` VARCHAR(191) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `uniq_user_blocked_email` (`user_id`, `email`),
            INDEX `idx_blocked_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[3/3] Created blocked_senders table.\n";

    // 4. Ensure default settings row exists for existing users
    $users = $pdo->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $insertSettingStmt = $pdo->prepare("
        INSERT IGNORE INTO user_settings (user_id, theme, density, default_folder, default_category, show_category_tabs, unread_badge_mode, in_app_notifications, browser_notifications, append_signature, default_reply_behavior, confirm_discard, auto_save_drafts)
        VALUES (:uid, 'light', 'comfortable', 'inbox', 'primary', 1, 'unread', 1, 0, 1, 'reply', 1, 1)
    ");
    foreach ($users as $uid) {
        $insertSettingStmt->execute(['uid' => $uid]);
    }
    echo "Seed: initialized settings for existing users.\n";

    echo "=== Settings Migration Finished Successfully! ===\n";
} catch (Exception $e) {
    echo "ERROR during migration: " . $e->getMessage() . "\n";
    exit(1);
}
