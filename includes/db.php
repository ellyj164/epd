<?php
// /includes/db.php
declare(strict_types=1);

if (!function_exists('db')) {
    function db(): PDO {
        static $pdo = null;
        if ($pdo instanceof PDO) return $pdo;

        // Prefer .env if available
        $host     = getenv('DB_HOST') ?: 'localhost';
        $port     = getenv('DB_PORT') ?: '3306';
        $dbname   = getenv('DB_NAME') ?: 'duns';
        $user     = getenv('DB_USER') ?: 'duns';
        $pass     = getenv('DB_PASS') ?: 'QRJ5M0VuI1nkMQW';
        $charset  = 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Optionally log to file/syslog without secrets
            error_log('DB connection failed: '.$e->getMessage());
            throw $e; // Let caller handle (e.g., show friendly error page)
        }
        return $pdo;
    }
}

if (!function_exists('db_transaction')) {
    function db_transaction(callable $fn) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $result = $fn($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $t) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $t;
        }
    }
}

if (!function_exists('db_ping')) {
    function db_ping(): bool {
        try {
            db()->query('SELECT 1')->fetchColumn();
            return true;
        } catch (Throwable $t) {
            return false;
        }
    }
}