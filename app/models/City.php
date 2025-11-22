<?php
require_once __DIR__ . '/../config/database.php';

class City {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
        $this->ensureTenantColumns();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS cities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NULL,
            created_by_user_id INT NULL,
            name VARCHAR(150) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(name),
            KEY idx_cities_owner_id (owner_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->getConnection()->exec($sql);
    }

    private function ensureTenantColumns(): void {
        $pdo = $this->db->getConnection();
        try { $pdo->exec("ALTER TABLE cities ADD COLUMN owner_id INT NULL AFTER id"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE cities ADD COLUMN created_by_user_id INT NULL AFTER owner_id"); } catch (\Throwable $e) {}
        // Remove global unique and replace with per-tenant unique
        try { $pdo->exec("ALTER TABLE cities DROP INDEX name"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE cities DROP INDEX name_2"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE cities ADD UNIQUE KEY uniq_cities_owner_name (owner_id, name)"); } catch (\Throwable $e) {}
    }

    public function getAll(): array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $stmt = $pdo->prepare("SELECT id, name, is_active FROM cities WHERE owner_id = ? ORDER BY name ASC");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int {
        $name = trim($data['name'] ?? '');
        $isActive = !empty($data['is_active']) ? 1 : 0;
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new \Exception('User not authenticated'); }
        $stmt = $pdo->prepare("INSERT INTO cities (owner_id, created_by_user_id, name, is_active) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ownerId, $userId, $name, $isActive]);
        return (int)$pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $name = trim($data['name'] ?? '');
        $isActive = !empty($data['is_active']) ? 1 : 0;
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $stmt = $pdo->prepare("UPDATE cities SET name = ?, is_active = ? WHERE id = ? AND owner_id = ?");
        return $stmt->execute([$name, $isActive, $id, $ownerId]);
    }

    public function delete(int $id): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $stmt = $pdo->prepare("DELETE FROM cities WHERE id = ? AND owner_id = ?");
        return $stmt->execute([$id, $ownerId]);
    }
}
