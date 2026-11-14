<?php
declare(strict_types=1);

/**
 * Novalyx Cloud Webmail - Unified Master Migration & Seeder
 * Idempotent: Can be run multiple times safely on fresh or existing databases.
 */

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register();

if (file_exists(__DIR__ . '/../.env')) {
    \App\Core\Env::load(__DIR__ . '/../.env');
}

echo "=========================================================\n";
echo " Novalyx Cloud Webmail - Automated Database Migration\n";
echo "=========================================================\n";

try {
    // 1. Establish database connection
    try {
        $pdo = \App\Core\Database::getConnection();
        echo "[1/6] Connected to target database successfully.\n";
    } catch (\PDOException $e) {
        echo "[1/6] Target database not found or cannot connect directly. Attempting to create...\n";
        try {
            $rootPdo = \App\Core\Database::getRootConnection();
            $dbName = \App\Core\Env::get('DB_DATABASE', 'nkinfyx_email');
            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            echo "      Database `{$dbName}` verified/created.\n";
            $pdo = \App\Core\Database::getConnection();
        } catch (\Exception $ex) {
            throw new \Exception("Could not connect or create database: " . $ex->getMessage());
        }
    }

    // 2. Base tables (users, messages, message_recipients, mailbox_items, labels, message_labels)
    echo "[2/6] Verifying core tables...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `display_name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(191) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `avatar_path` VARCHAR(255) NULL,
            `status` ENUM('active', 'suspended', 'pending') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_users_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `messages` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `owner_user_id` INT UNSIGNED NOT NULL,
            `sender_name` VARCHAR(120) NOT NULL,
            `sender_email` VARCHAR(191) NOT NULL,
            `subject` VARCHAR(255) NOT NULL DEFAULT '(no subject)',
            `body_text` MEDIUMTEXT,
            `body_html` MEDIUMTEXT,
            `snippet` VARCHAR(255) DEFAULT '',
            `sent_at` DATETIME NULL,
            `received_at` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            INDEX `idx_messages_owner` (`owner_user_id`),
            INDEX `idx_messages_sender` (`sender_email`),
            INDEX `idx_messages_subject` (`subject`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `message_recipients` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `message_id` INT UNSIGNED NOT NULL,
            `recipient_type` ENUM('to', 'cc', 'bcc') NOT NULL DEFAULT 'to',
            `email` VARCHAR(191) NOT NULL,
            `display_name` VARCHAR(120) DEFAULT NULL,
            FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
            INDEX `idx_recipients_message` (`message_id`),
            INDEX `idx_recipients_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `mailbox_items` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `message_id` INT UNSIGNED NOT NULL,
            `folder` ENUM('inbox', 'sent', 'drafts', 'archive', 'spam', 'trash', 'snoozed', 'scheduled', 'purchases', 'important') NOT NULL DEFAULT 'inbox',
            `category` ENUM('primary', 'updates', 'promotions', 'purchases', 'social') NOT NULL DEFAULT 'primary',
            `is_read` TINYINT(1) NOT NULL DEFAULT 0,
            `is_starred` TINYINT(1) NOT NULL DEFAULT 0,
            `is_important` TINYINT(1) NOT NULL DEFAULT 0,
            `is_draft` TINYINT(1) NOT NULL DEFAULT 0,
            `snooze_until` DATETIME NULL,
            `scheduled_at` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `uniq_user_message` (`user_id`, `message_id`),
            INDEX `idx_mailbox_user_folder` (`user_id`, `folder`, `category`),
            INDEX `idx_mailbox_snooze` (`snooze_until`),
            INDEX `idx_mailbox_scheduled` (`scheduled_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `labels` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `name` VARCHAR(50) NOT NULL,
            `color` VARCHAR(20) NOT NULL DEFAULT '#1a73e8',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `uniq_user_label` (`user_id`, `name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS `message_labels` (
            `message_id` INT UNSIGNED NOT NULL,
            `label_id` INT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`message_id`, `label_id`),
            FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`label_id`) REFERENCES `labels` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Attachments table
    echo "[3/6] Verifying attachments table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `attachments` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `message_id` INT UNSIGNED NULL,
            `user_id` INT UNSIGNED NOT NULL,
            `original_name` VARCHAR(255) NOT NULL,
            `stored_filename` VARCHAR(255) NOT NULL,
            `file_path` VARCHAR(500) NOT NULL,
            `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
            `mime_type` VARCHAR(100) NOT NULL DEFAULT 'application/octet-stream',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
            INDEX `idx_attachments_user` (`user_id`),
            INDEX `idx_attachments_message` (`message_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. Calendar events table
    echo "[4/6] Verifying calendar events table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `calendar_events` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `event_date` DATE NOT NULL,
            `start_time` TIME NULL,
            `end_time` TIME NULL,
            `color` VARCHAR(20) NOT NULL DEFAULT '#188038',
            `is_interview` TINYINT(1) NOT NULL DEFAULT 0,
            `source_message_id` INT UNSIGNED NULL,
            `description` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            INDEX `idx_cal_user_date` (`user_id`, `event_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 5. User settings & Blocked senders tables
    echo "[5/6] Verifying user settings and blocked senders tables...\n";
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

        CREATE TABLE IF NOT EXISTS `blocked_senders` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `email` VARCHAR(191) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            UNIQUE KEY `uniq_user_blocked_email` (`user_id`, `email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Safe column migrations in case of pre-existing older tables
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar_path'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL AFTER display_name");
    }

    // 6. Seed demo data if database is fresh
    echo "[6/6] Checking seed accounts...\n";
    $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount === 0) {
        echo "      Database is empty. Seeding initial accounts and sample emails...\n";
        $seedSql = file_get_contents(__DIR__ . '/seed.sql');
        if ($seedSql) {
            $pdo->exec($seedSql);
            echo "      Seeding complete! Default admin: adminnkinflyx@gmail.com / password123\n";
        }
    } else {
        echo "      Existing users found ({$userCount} users). Skipping seed data.\n";
    }

    echo "=========================================================\n";
    echo " SUCCESS: All migrations applied cleanly!\n";
    echo "=========================================================\n";
} catch (\Exception $e) {
    echo "=========================================================\n";
    echo " MIGRATION ERROR: " . $e->getMessage() . "\n";
    echo "=========================================================\n";
    exit(1);
}
