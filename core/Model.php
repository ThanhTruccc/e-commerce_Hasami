<?php
// ============================================================
//  CORE/MODEL.PHP - Base Model với PDO wrapper
// ============================================================

class Model {

    protected PDO    $db;
    protected string $table = '';

    public function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->db = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }

    // ── Generic CRUD ─────────────────────────────────────────

    public function findAll(string $orderBy = 'id DESC', int $limit = 0): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        if ($limit > 0) $sql .= " LIMIT {$limit}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function findWhere(array $conditions, string $orderBy = 'id DESC', int $limit = 0): array {
        $clauses = [];
        $binds   = [];
        foreach ($conditions as $col => $val) {
            $clauses[] = "{$col} = :{$col}";
            $binds[":{$col}"] = $val;
        }
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $clauses) . " ORDER BY {$orderBy}";
        if ($limit > 0) $sql .= " LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($binds);
        return $stmt->fetchAll();
    }

    public function insert(array $data): int {
        $cols = implode(', ', array_keys($data));
        $vals = implode(', ', array_map(fn($k) => ":{$k}", array_keys($data)));
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$cols}) VALUES ({$vals})");
        $stmt->execute($this->prefix($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sets = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($data)));
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$sets} WHERE id = :id");
        $data['id'] = $id;
        return $stmt->execute($this->prefix($data));
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function count(array $conditions = []): int {
        if (empty($conditions)) {
            return (int) $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
        }
        $clauses = [];
        $binds   = [];
        foreach ($conditions as $col => $val) {
            $clauses[] = "{$col} = :{$col}";
            $binds[":{$col}"] = $val;
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE " . implode(' AND ', $clauses));
        $stmt->execute($binds);
        return (int) $stmt->fetchColumn();
    }

    // ── Helpers ──────────────────────────────────────────────

    protected function query(string $sql, array $binds = []): \PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($binds);
        return $stmt;
    }

    private function prefix(array $data): array {
        $result = [];
        foreach ($data as $k => $v) {
            $result[str_starts_with($k, ':') ? $k : ":{$k}"] = $v;
        }
        return $result;
    }

    public function getDb(): PDO {
        return $this->db;
    }
}
