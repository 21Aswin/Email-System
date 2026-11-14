<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Env::load(__DIR__ . '/../.env');

echo "=== Running Sidebar Extensions Migration ===\n";

try {
    $pdo = \App\Core\Database::getConnection();

    // 1. Extend folder enum to support 'scheduled'
    $pdo->exec("
        ALTER TABLE mailbox_items 
        MODIFY COLUMN folder ENUM('inbox', 'sent', 'drafts', 'archive', 'spam', 'trash', 'snoozed', 'scheduled') NOT NULL DEFAULT 'inbox'
    ");
    echo "[1/5] Extended folder ENUM to include 'scheduled'.\n";

    // 2. Extend category enum to support 'purchases'
    $pdo->exec("
        ALTER TABLE mailbox_items 
        MODIFY COLUMN category ENUM('primary', 'updates', 'promotions', 'purchases') NOT NULL DEFAULT 'primary'
    ");
    echo "[2/5] Extended category ENUM to include 'purchases'.\n";

    // 3. Add scheduled_at column if not exists
    $cols = $pdo->query("SHOW COLUMNS FROM mailbox_items LIKE 'scheduled_at'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("
            ALTER TABLE mailbox_items 
            ADD COLUMN scheduled_at DATETIME NULL DEFAULT NULL AFTER snoozed_until,
            ADD INDEX idx_mailbox_scheduled (user_id, scheduled_at)
        ");
        echo "[3/5] Added scheduled_at column and index.\n";
    } else {
        echo "[3/5] scheduled_at column already exists.\n";
    }

    // 4. Add index for is_important if not exists
    $indexes = $pdo->query("SHOW INDEX FROM mailbox_items WHERE Key_name = 'idx_mailbox_user_important'")->fetchAll();
    if (empty($indexes)) {
        $pdo->exec("ALTER TABLE mailbox_items ADD INDEX idx_mailbox_user_important (user_id, is_important)");
        echo "[4/5] Added index for is_important.\n";
    } else {
        echo "[4/5] is_important index already exists.\n";
    }

    // 5. Check if a purchase sample email exists for User 1, if not insert one
    $purchaseCheck = $pdo->prepare("SELECT id FROM messages WHERE owner_user_id = 1 AND subject LIKE '%Invoice #NX-8924%' LIMIT 1");
    $purchaseCheck->execute();
    if (!$purchaseCheck->fetchColumn()) {
        $pdo->beginTransaction();
        $stmtMsg = $pdo->prepare("
            INSERT INTO messages (owner_user_id, sender_name, sender_email, subject, body_text, body_html, snippet, received_at, created_at)
            VALUES (
                1, 'Novalyx Billing Service', 'billing@novalyx.cloud',
                'Invoice #NX-8924: Dedicated Cloud Node Pro Subscription',
                'Thank you for your order. Your subscription for Dedicated Cloud Node Pro has been renewed for the current billing cycle. Total Amount: $49.00.',
                '<p>Dear Customer,</p><p>Thank you for your order with <strong>Novalyx Cloud & Tech</strong>.</p><p><strong>Order Summary:</strong><br>Item: Dedicated Cloud Node Pro (Annual)<br>Order ID: #NX-8924<br>Amount Billed: $49.00 USD<br>Status: Paid</p><p>You can manage your subscription preferences from your account dashboard.</p><p>Regards,<br>Novalyx Billing Department</p>',
                'Thank you for your order. Your subscription for Dedicated Cloud Node Pro has been renewed...',
                NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY
            )
        ");
        $stmtMsg->execute();
        $newMsgId = (int)$pdo->lastInsertId();

        $stmtRec = $pdo->prepare("
            INSERT INTO message_recipients (message_id, recipient_type, email, display_name)
            VALUES (:mid, 'to', 'novalyx.workspace@gmail.com', 'Novalyx Admin')
        ");
        $stmtRec->execute(['mid' => $newMsgId]);

        $stmtMb = $pdo->prepare("
            INSERT INTO mailbox_items (user_id, message_id, folder, category, is_read, is_starred, is_important, created_at)
            VALUES (1, :mid, 'inbox', 'purchases', 0, 0, 1, NOW() - INTERVAL 1 DAY)
        ");
        $stmtMb->execute(['mid' => $newMsgId]);

        $pdo->commit();
        echo "[5/5] Seeded sample Purchases item.\n";
    } else {
        echo "[5/5] Purchases sample email already present.\n";
    }

    echo "==> Migration Completed Successfully!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
