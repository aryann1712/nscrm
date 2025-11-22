<?php
require_once __DIR__ . '/../config/database.php';

class InventoryUnit {
    private Database $db;

    public function __construct() { $this->db = new Database(); }

    public function ensureTable(): void {
        $pdo = $this->db->getConnection();
        $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_units (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            created_by_user_id INT NULL,
            code VARCHAR(50) NOT NULL,
            label VARCHAR(100) NULL,
            precision_format VARCHAR(20) NULL,
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_unit_owner_code (owner_id, code),
            KEY idx_unit_owner (owner_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        try { $pdo->exec("ALTER TABLE inventory_units ADD COLUMN owner_id INT NOT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_units ADD COLUMN created_by_user_id INT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_units DROP INDEX code"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_units DROP INDEX uniq_unit_owner_code"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_units ADD UNIQUE KEY uniq_unit_owner_code (owner_id, code)"); } catch (\Throwable $e) {}
    }

    public function getAll(): array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT id, code, COALESCE(label, code) AS label, COALESCE(precision_format, 'N') AS precision_format, COALESCE(active,1) AS active FROM inventory_units WHERE owner_id = ? ORDER BY code");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function create(string $code, ?string $label = null, ?string $precision = null): int {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        $pdo = $this->db->getConnection();
        $code = trim($code);
        if ($code === '') throw new Exception('Unit code required');
        $stmt = $pdo->prepare("SELECT id FROM inventory_units WHERE owner_id = ? AND code = ? LIMIT 1");
        $stmt->execute([$ownerId, $code]);
        $id = (int)($stmt->fetchColumn() ?: 0);
        if ($id > 0) return $id;
        $ins = $pdo->prepare("INSERT INTO inventory_units (owner_id, created_by_user_id, code, label, precision_format) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$ownerId, $userId, $code, $label, $precision]);
        return (int)$pdo->lastInsertId();
    }

    public function update(int $id, string $code, ?string $label = null, ?string $precision = null, int $active = 1): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("UPDATE inventory_units SET code = ?, label = ?, precision_format = ?, active = ? WHERE id = ? AND owner_id = ?");
        return (bool)$stmt->execute([$code, $label, $precision, $active, $id, $ownerId]);
    }

    public function delete(int $id): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM inventory_units WHERE id = ? AND owner_id = ?");
        return (bool)$stmt->execute([$id, $ownerId]);
    }
}
