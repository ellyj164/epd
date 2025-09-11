<?php
/**
 * Database compatibility and base model
 * - Centralizes connection via includes/db.php
 * - Provides a legacy Database class wrapper so old code still works
 * - Does NOT redeclare db() to avoid fatal errors
 */

require_once __DIR__ . '/db.php'; // defines db(), db_transaction(), db_ping()

/**
 * Legacy compatibility: Database class wrapper
 * Allows existing code that calls Database::getInstance()->getConnection()
 * to continue working without redefining db().
 */
if (!class_exists('Database')) {
    final class Database {
        private static ?self $instance = null;
        private function __construct() {}
        public static function getInstance(): self {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        public function getConnection(): PDO {
            return db();
        }
    }
}

/**
 * Base Model Class
 * Full CRUD, backed by centralized db()
 */
if (!class_exists('BaseModel')) {
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
}