<?php
require_once __DIR__ . '/../config/database.php';

class LeadSource {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
        $this->ensureTenantColumns();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS lead_sources (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NULL,
            created_by_user_id INT NULL,
            name VARCHAR(100) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(is_active),
            INDEX(name),
            KEY idx_lead_sources_owner_id (owner_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->getConnection()->exec($sql);
    }

    private function ensureTenantColumns(): void {
        $pdo = $this->db->getConnection();
        try { $pdo->exec("ALTER TABLE lead_sources ADD COLUMN owner_id INT NULL AFTER id"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE lead_sources ADD COLUMN created_by_user_id INT NULL AFTER owner_id"); } catch (\Throwable $e) {}
        // Drop any global unique on name and create per-tenant unique
        try { $pdo->exec("ALTER TABLE lead_sources DROP INDEX name"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE lead_sources DROP INDEX name_2"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE lead_sources ADD UNIQUE KEY uniq_lead_sources_owner_name (owner_id, name)"); } catch (\Throwable $e) {}
    }

    public function getAll(): array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $stmt = $pdo->prepare("SELECT id, name, is_active FROM lead_sources WHERE owner_id = ? ORDER BY name ASC");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $name, bool $isActive = true): int {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new \Exception('User not authenticated'); }
        $stmt = $pdo->prepare("INSERT INTO lead_sources (owner_id, created_by_user_id, name, is_active) VALUES (?,?,?,?)");
        $stmt->execute([$ownerId, $userId, trim($name), $isActive ? 1 : 0]);
        return (int)$pdo->lastInsertId();
    }

    public function update(int $id, string $name, bool $isActive = true): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $stmt = $pdo->prepare("UPDATE lead_sources SET name = ?, is_active = ? WHERE id = ? AND owner_id = ?");
        return $stmt->execute([trim($name), $isActive ? 1 : 0, $id, $ownerId]);
    }

    public function delete(int $id): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $stmt = $pdo->prepare("DELETE FROM lead_sources WHERE id = ? AND owner_id = ?");
        return $stmt->execute([$id, $ownerId]);
    }
}
