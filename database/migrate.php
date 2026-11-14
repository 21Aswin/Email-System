<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';
\App\Core\Autoloader::register();

\App\Core\Env::load(__DIR__ . '/../.env');

echo "=== nkinfyx groups Database Migration & Seeder ===\n";

try {
    $pdo = \App\Core\Database::getRootConnection();
    echo "[1/3] Connected to MySQL host successfully.\n";

    // Read schema.sql
    $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
    if (!$schemaSql) {
        throw new Exception("schema.sql not found or empty.");
    }
    
    // Execute schema statements
    $pdo->exec($schemaSql);
    echo "[2/3] Database 'nkinfyx_email' and all tables created successfully.\n";

    // Read seed.sql
    $seedSql = file_get_contents(__DIR__ . '/seed.sql');
    if ($seedSql) {
        $pdo->exec($seedSql);
        echo "[3/3] Seed data inserted successfully (Default Admin: adminnkinflyx@gmail.com / password123).\n";
    }

    echo "==> Migration Completed Successfully!\n";
} catch (Exception $e) {
    echo "ERROR during migration: " . $e->getMessage() . "\n";
    exit(1);
}
