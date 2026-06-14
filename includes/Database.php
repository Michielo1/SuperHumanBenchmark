<?php
/**
 * Database Connection Class
 * Singleton pattern to ensure only one database connection exists, can be used in API.
 */

class Database {
    private static ?PDO $instance = null;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {}

    /**
     * Get the singleton PDO instance
     *
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // Load config if not already loaded
            if (!defined('DB_HOST')) {
                require_once INCLUDES_PATH . '/config.php';
            }

            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

                self::$instance = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_PERSISTENT => false
                    ]
                );
            } catch (PDOException $e) {
                // Log error in production, show in development
                if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                    throw $e;
                } else {
                    error_log("Database connection error: " . $e->getMessage());
                    throw new PDOException("Database connection failed");
                }
            }
        }

        return self::$instance;
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
