<?php
/**
 * RansomGuard - Database Connection Configuration
 * Target: MySQL / MariaDB (XAMPP Environment)
 */

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'final21_db');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a singleton PDO database connection instance.
 *
 * @return PDO
 * @throws PDOException
 */
function get_db_connection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error and display user-friendly message
            error_log('Database connection error: ' . $e->getMessage());
            die('
                <div style="font-family: sans-serif; padding: 24px; max-width: 600px; margin: 40px auto; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; color: #9f1239;">
                    <h3 style="margin-top:0;">Database Connection Error</h3>
                    <p>Could not connect to the database <strong>' . htmlspecialchars(DB_NAME) . '</strong> on localhost.</p>
                    <p>Please make sure <strong>MySQL</strong> is running in the XAMPP Control Panel and the <code>database/schema.sql</code> has been imported.</p>
                </div>
            ');
        }
    }

    return $pdo;
}
