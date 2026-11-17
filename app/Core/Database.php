<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dbUrl = Env::get('DATABASE_URL');
            if (!empty($dbUrl) && (str_starts_with($dbUrl, 'mysql://') || str_starts_with($dbUrl, 'mariadb://'))) {
                $parsed = parse_url($dbUrl);
                $host = $parsed['host'] ?? 'localhost';
                $port = (string)($parsed['port'] ?? '3306');
                $dbName = ltrim($parsed['path'] ?? 'nkinfyx_email', '/');
                $user = $parsed['user'] ?? 'root';
                $pass = $parsed['pass'] ?? '';
            } else {
                $host = Env::get('DB_HOST', '127.0.0.1');
                $port = Env::get('DB_PORT', '3306');
                $dbName = Env::get('DB_DATABASE', 'nkinfyx_email');
                $user = Env::get('DB_USERNAME', 'root');
                $pass = Env::get('DB_PASSWORD', 'Aswin@21');
            }

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
                self::$instance->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                self::$instance->exec("SET time_zone = '+05:30'");
            } catch (PDOException $e) {
                // Return descriptive error or rethrow
                throw new PDOException("Database connection error: " . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$instance;
    }

    /**
     * For migration/installation when the database might not yet exist
     */
    public static function getRootConnection(): PDO
    {
        $dbUrl = Env::get('DATABASE_URL');
        if (!empty($dbUrl) && (str_starts_with($dbUrl, 'mysql://') || str_starts_with($dbUrl, 'mariadb://'))) {
            $parsed = parse_url($dbUrl);
            $host = $parsed['host'] ?? 'localhost';
            $port = (string)($parsed['port'] ?? '3306');
            $user = $parsed['user'] ?? 'root';
            $pass = $parsed['pass'] ?? '';
        } else {
            $host = Env::get('DB_HOST', '127.0.0.1');
            $port = Env::get('DB_PORT', '3306');
            $user = Env::get('DB_USERNAME', 'root');
            $pass = Env::get('DB_PASSWORD', 'Aswin@21');
        }

        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, $user, $pass, $options);
    }
}
