<?php
/**
 * Hesabdar - Database Connection Manager (PDO Singleton)
 */

class Database {
    private static ?PDO $instance = null;

    /**
     * Get active PDO connection instance
     *
     * @return PDO
     * @throws Exception
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/config.php';
            $db = $config['db'];

            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $db['host'],
                $db['port'] ?? 3306,
                $db['database'],
                $db['charset'] ?? 'utf8mb4'
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $db['username'], $db['password'], $options);
            } catch (PDOException $e) {
                // In production, do not leak raw credentials
                error_log("Database connection error: " . $e->getMessage());
                throw new Exception("خطا در اتصال به پایگاه داده. لطفاً تنظیمات دیتابیس را در app/config/config.php بررسی نمایید.");
            }
        }

        return self::$instance;
    }

    /**
     * Prevent direct cloning or serialization
     */
    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
