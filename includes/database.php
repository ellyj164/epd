<?php
/**
 * Database Connection - Centralized PDO Singleton
 * E-Commerce Platform
 * 
 * As per requirements: Centralize DB access in database.php as a lazy PDO singleton: db(): PDO
 * Enforce utf8mb4, proper error modes, ATTR_EMULATE_PREPARES=false
 * Load DSN/host/user/pass/db from .env using vlucas/phpdotenv
 */

declare(strict_types=1);

// Load Composer autoloader for vlucas/phpdotenv
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

/**
 * Get database connection (lazy PDO singleton)
 */
function db(): PDO {
    static $pdo = null;
    
    if ($pdo !== null) {
        return $pdo;
    }
    
    // Load environment variables using vlucas/phpdotenv if available
    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        } catch (Exception $e) {
            // Fallback to manual parsing if Dotenv fails
            loadEnvFallback(__DIR__ . '/../.env');
        }
    } else {
        // Manual fallback if vlucas/phpdotenv not installed
        loadEnvFallback(__DIR__ . '/../.env');
    }
    
    // Get database credentials from environment
    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
    $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
    $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'ecommerce_platform';
    $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'duns1';
    $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: 'Tumukunde';
    $charset = $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4';
    
    // Build DSN
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
    
    // PDO options enforcing requirements
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, // Required: proper prepared statements
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        // Set additional connection settings
        $pdo->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
        $pdo->exec("SET time_zone = '+00:00'");
        
    } catch (PDOException $e) {
        // Log error without exposing credentials
        error_log('Database connection failed: ' . $e->getMessage());
        throw new Exception('Database connection failed. Please check your configuration.');
    }
    
    return $pdo;
}

/**
 * Manual environment file loader (fallback)
 */
function loadEnvFallback(string $path): void {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments
        if (strpos($line, '#') === 0) {
            continue;
        }
        
        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^["\'].*["\']$/', $value)) {
                $value = substr($value, 1, -1);
            }
            
            // Set in environment if not already set
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

/**
 * Execute a database transaction
 */
function db_transaction(callable $callback) {
    $pdo = db();
    
    $pdo->beginTransaction();
    try {
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        throw $e;
    }
}

/**
 * Test database connectivity
 */
function db_ping(): bool {
    try {
        db()->query('SELECT 1')->fetchColumn();
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Base Model Class
 * Updated to use centralized database.php
 */
abstract class BaseModel {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = db(); // Use centralized database connection
    }
    
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function count($where = '') {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn();
    }
}
?>