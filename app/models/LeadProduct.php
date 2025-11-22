<?php
require_once __DIR__ . '/../config/database.php';

class LeadProduct {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
        $this->ensureTenantColumns();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS lead_products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NULL,
            created_by_user_id INT NULL,
            name VARCHAR(150) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(name),
            KEY idx_lead_products_owner_id (owner_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->getConnection()->exec($sql);
    }

    private function ensureTenantColumns(): void {
        $pdo = $this->db->getConnection();
        try { $pdo->exec("ALTER TABLE lead_products ADD COLUMN owner_id INT NULL AFTER id"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE lead_products ADD COLUMN created_by_user_id INT NULL AFTER owner_id"); } catch (\Throwable $e) {}
        // Drop global unique index and create per-tenant unique
        try { $pdo->exec("ALTER TABLE lead_products DROP INDEX name"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE lead_products DROP INDEX name_2"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE lead_products ADD UNIQUE KEY uniq_lead_products_owner_name (owner_id, name)"); } catch (\Throwable $e) {}
    }

    public function getAll(): array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $stmt = $pdo->prepare("SELECT id, name FROM lead_products WHERE owner_id = ? ORDER BY name ASC");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $name): int {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new \Exception('User not authenticated'); }
        $stmt = $pdo->prepare("INSERT INTO lead_products (owner_id, created_by_user_id, name) VALUES (?,?,?)");
        $stmt->execute([$ownerId, $userId, trim($name)]);
        return (int)$pdo->lastInsertId();
    }

    public function delete(int $id): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $stmt = $pdo->prepare("DELETE FROM lead_products WHERE id = ? AND owner_id = ?");
        return $stmt->execute([$id, $ownerId]);
    }
}
