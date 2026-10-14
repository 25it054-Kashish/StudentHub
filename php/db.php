<?php
/**
 * StudentHub - Database Connectivity Helper (Practical 8)
 * Implements Secure PDO and MySQLi Connections with Prepared Statements
 * Charotar University of Science and Technology (CHARUSAT) - FTE ITUE203
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $pdoInstance = null;
    private static ?mysqli $mysqliInstance = null;

    /**
     * Get PDO Connection instance (Practical 8 Intermediate)
     */
    public static function getPDO(): ?PDO {
        if (self::$pdoInstance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                self::$pdoInstance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Log and gracefully return null so file-based fallback functions work seamlessly
                error_log("PDO Connection Error: " . $e->getMessage());
                return null;
            }
        }
        return self::$pdoInstance;
    }

    /**
     * Get MySQLi Connection instance (Practical 8 & 9)
     */
    public static function getMySQLi(): ?mysqli {
        if (self::$mysqliInstance === null) {
            try {
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                self::$mysqliInstance = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
                self::$mysqliInstance->set_charset("utf8mb4");
            } catch (mysqli_sql_exception $e) {
                error_log("MySQLi Connection Error: " . $e->getMessage());
                return null;
            }
        }
        return self::$mysqliInstance;
    }

    /**
     * Test Database Connectivity (For Practical 8 Evaluation)
     */
    public static function testConnection(): array {
        $pdo = self::getPDO();
        $mysqli = self::getMySQLi();

        return [
            'pdo_connected'    => ($pdo !== null),
            'mysqli_connected' => ($mysqli !== null),
            'database_name'    => DB_NAME,
            'host'             => DB_HOST . ':' . DB_PORT,
            'timestamp'        => date('Y-m-d H:i:s')
        ];
    }
}

// Global accessor shortcuts for quick usage
function getDbConnection(): ?PDO {
    return Database::getPDO();
}

function getMySQLiConnection(): ?mysqli {
    return Database::getMySQLi();
}
