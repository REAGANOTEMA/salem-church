<?php
/**
 * Salem Dominion Ministries - PDO Database Connection
 * Multi-database support: website, admin, members
 */

require_once dirname(__DIR__) . '/config.php';

class Database {
    private static ?Database $instance = null;
    private static array $namedInstances = [];
    private ?PDO $pdo = null;
    private string $dbName;

    private function __construct(string $host, string $user, string $pass, string $name, string $port = '3306', string $charset = 'utf8mb4') {
        $this->dbName = $name;
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
                PDO::MYSQL_ATTR_FOUND_ROWS   => true,
            ]);
        } catch (PDOException $e) {
            error_log("Database connection failed ({$name}): " . $e->getMessage());
            $this->pdo = null;
            throw $e;
        }
    }

    // Default instance (website database) for backward compatibility
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, DB_CHARSET);
        }
        return self::$instance;
    }

    // Named instance: 'website', 'admin', 'members'
    public static function getNamed(string $name): self {
        if (!isset(self::$namedInstances[$name])) {
            switch ($name) {
                case 'admin':
                    self::$namedInstances[$name] = new self(
                        ADMIN_DB_HOST, ADMIN_DB_USER, ADMIN_DB_PASS, ADMIN_DB_NAME, DB_PORT, DB_CHARSET
                    );
                    break;
                case 'members':
                    self::$namedInstances[$name] = new self(
                        MEMBERS_DB_HOST, MEMBERS_DB_USER, MEMBERS_DB_PASS, MEMBERS_DB_NAME, DB_PORT, DB_CHARSET
                    );
                    break;
                case 'website':
                default:
                    self::$namedInstances[$name] = self::getInstance();
                    break;
            }
        }
        return self::$namedInstances[$name];
    }

    public static function reset(): void {
        self::$instance = null;
        self::$namedInstances = [];
    }

    public function getPdo(): PDO {
        if ($this->pdo === null) {
            throw new \PDOException("No database connection for {$this->dbName}");
        }
        return $this->pdo;
    }

    public function getDbName(): string {
        return $this->dbName;
    }

    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert(string $table, array $data): int {
        $columns = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));
        $sql = "UPDATE `{$table}` SET {$set} WHERE {$where}";
        $stmt = $this->query($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function count(string $table, string $where = '1=1', array $params = []): int {
        $sql = "SELECT COUNT(*) as cnt FROM `{$table}` WHERE {$where}";
        $row = $this->fetch($sql, $params);
        return $row ? (int)$row['cnt'] : 0;
    }

    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool {
        return $this->pdo->commit();
    }

    public function rollback(): bool {
        return $this->pdo->rollBack();
    }

    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }

    public function isConnected(): bool {
        return $this->pdo !== null;
    }

    public function tableExists(string $table): bool {
        try {
            $this->query("SELECT 1 FROM `{$table}` LIMIT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

// ============================================================
// Global helper functions
// ============================================================

// Default = website database (backward compatible)
function db(): Database {
    return Database::getInstance();
}

function dbConn(): ?PDO {
    return Database::getInstance()->getPdo();
}

// Named database shortcuts
function dbWebsite(): Database {
    return Database::getNamed('website');
}

function dbAdmin(): Database {
    return Database::getNamed('admin');
}

function dbMembers(): Database {
    return Database::getNamed('members');
}
