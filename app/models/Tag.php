<?php
require_once __DIR__ . '/../config/database.php';

class Tag {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
    }
    
    private function ensureTable(): void {
        $pdo = $this->db->getConnection();
        // Base table
        $sql = "CREATE TABLE IF NOT EXISTS tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            created_by_user_id INT NULL,
            name VARCHAR(150) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(name),
            KEY idx_tags_owner_id (owner_id),
            KEY idx_tags_created_by (created_by_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        // Try to add missing columns and unique key (ignore errors if exist)
        try { $pdo->exec("ALTER TABLE tags ADD COLUMN owner_id INT NOT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE tags ADD COLUMN created_by_user_id INT NULL"); } catch (\Throwable $e) {}
        // Drop old global uniques on name if present; then add per-tenant unique
        try { $pdo->exec("ALTER TABLE tags DROP INDEX name"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE tags DROP INDEX name_2"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE tags ADD UNIQUE KEY uniq_tags_owner_name (owner_id, name)"); } catch (\Throwable $e) {}
    }

    public function getAll(): array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT id, name, is_active FROM tags WHERE owner_id = ? ORDER BY name ASC");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new \Exception('User not authenticated'); }
        $name = trim($data['name'] ?? '');
        $isActive = !empty($data['is_active']) ? 1 : 0;
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("INSERT INTO tags (owner_id, created_by_user_id, name, is_active) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ownerId, $userId, $name, $isActive]);
        return (int)$pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $name = trim($data['name'] ?? '');
        $isActive = !empty($data['is_active']) ? 1 : 0;
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("UPDATE tags SET name = ?, is_active = ? WHERE id = ? AND owner_id = ?");
        return $stmt->execute([$name, $isActive, $id, $ownerId]);
    }

    public function delete(int $id): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM tags WHERE id = ? AND owner_id = ?");
        return $stmt->execute([$id, $ownerId]);
    }
}
