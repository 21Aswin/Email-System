<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Env::load(__DIR__ . '/../.env');

echo "=== Running Attachments Foreign Key & Nullable Migration ===\n";

try {
    $pdo = \App\Core\Database::getConnection();

    // 1. Check if user_id column exists on attachments
    $cols = $pdo->query("SHOW COLUMNS FROM attachments LIKE 'user_id'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("
            ALTER TABLE attachments 
            ADD COLUMN user_id INT UNSIGNED NULL DEFAULT NULL AFTER id,
            ADD CONSTRAINT fk_attachments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ");
        echo "[1/3] Added user_id column with foreign key to users.\n";
    } else {
        echo "[1/3] user_id column already exists.\n";
    }

    // 2. Make message_id NULLable on attachments table
    // In MySQL, to alter a column with a foreign key constraint, we can modify column nullability directly:
    $pdo->exec("
        ALTER TABLE attachments 
        MODIFY COLUMN message_id INT UNSIGNED NULL DEFAULT NULL
    ");
    echo "[2/3] Modified message_id to be NULLABLE (NULL DEFAULT NULL).\n";

    // 3. Verify attachments table definition
    $columns = $pdo->query("DESCRIBE attachments")->fetchAll();
    echo "[3/3] Current attachments schema:\n";
    foreach ($columns as $col) {
        echo "   - {$col['Field']}: {$col['Type']}, Null={$col['Null']}, Default={$col['Default']}\n";
    }

    echo "\n==> Attachments migration completed successfully!\n";

} catch (Exception $e) {
    echo "\n[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
