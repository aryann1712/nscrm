<?php
require_once __DIR__ . '/../config/database.php';

class Task {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            due_date DATE NULL,
            due_time TIME NULL,
            status ENUM('open','in_progress','done','cancelled') NOT NULL DEFAULT 'open',
            priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
            created_by_user_id INT NULL,
            assigned_to_user_id INT NULL,
            related_type VARCHAR(50) NULL,
            related_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            INDEX(owner_id),
            INDEX(assigned_to_user_id),
            INDEX(created_by_user_id),
            INDEX(status),
            INDEX(due_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo = $this->db->getConnection();
        $pdo->exec($sql);

        // Ensure new columns exist on legacy tables
        $cols = $pdo->query('DESCRIBE tasks')->fetchAll(PDO::FETCH_COLUMN);
        $alter = [];
        if (!in_array('related_type', $cols, true)) {
            $alter[] = "ADD COLUMN related_type VARCHAR(50) NULL AFTER assigned_to_user_id";
        }
        if (!in_array('related_id', $cols, true)) {
            $alter[] = "ADD COLUMN related_id INT NULL AFTER related_type";
        }
        if ($alter) {
            $pdo->exec('ALTER TABLE tasks ' . implode(', ', $alter));
        }
    }

    public function create(array $data): int {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("INSERT INTO tasks (owner_id, title, description, due_date, due_time, status, priority, created_by_user_id, assigned_to_user_id, related_type, related_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            (int)$data['owner_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['due_date'] ?? null,
            $data['due_time'] ?? null,
            $data['status'] ?? 'open',
            $data['priority'] ?? 'medium',
            $data['created_by_user_id'] ?? null,
            $data['assigned_to_user_id'] ?? null,
            $data['related_type'] ?? null,
            $data['related_id'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public function listInbox(int $ownerId, int $userId): array {
        $pdo = $this->db->getConnection();
        $sql = "SELECT t.*, u.name AS assignee_name
                FROM tasks t
                LEFT JOIN users u ON u.id = t.assigned_to_user_id AND u.owner_id = t.owner_id
                WHERE t.owner_id = ? AND t.assigned_to_user_id = ?
                ORDER BY COALESCE(t.due_date, CURRENT_DATE()) ASC, t.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ownerId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listOutbox(int $ownerId, int $userId): array {
        $pdo = $this->db->getConnection();
        $sql = "SELECT t.*, u.name AS assignee_name
                FROM tasks t
                LEFT JOIN users u ON u.id = t.assigned_to_user_id AND u.owner_id = t.owner_id
                WHERE t.owner_id = ? AND t.created_by_user_id = ?
                ORDER BY COALESCE(t.due_date, CURRENT_DATE()) ASC, t.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ownerId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById(int $ownerId, int $id): ?array {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE owner_id = ? AND id = ? LIMIT 1');
        $stmt->execute([$ownerId, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function update(int $ownerId, int $id, array $data): bool {
        if (!$data) { return false; }
        $allowed = ['title','description','due_date','due_time','status','priority','assigned_to_user_id'];
        $sets = [];
        $params = [];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[] = "$col = ?";
                $params[] = $data[$col];
            }
        }
        if (!$sets) { return false; }
        $params[] = $ownerId;
        $params[] = $id;
        $sql = 'UPDATE tasks SET ' . implode(', ', $sets) . ', updated_at = NOW() WHERE owner_id = ? AND id = ?';
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }
}
