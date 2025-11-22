<?php
require_once __DIR__ . '/../config/database.php';

class InventoryHsn {
    private Database $db;
    public function __construct(){
        $this->db = new Database();
        $this->ensureTable();
    }

    public function ensureTable(): void {
        $pdo = $this->db->getConnection();
        $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_hsn (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            created_by_user_id INT NULL,
            code VARCHAR(64) NOT NULL,
            rate DECIMAL(5,2) NULL,
            note VARCHAR(255) NULL,
            active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_hsn_owner_code (owner_id, code),
            KEY idx_hsn_owner (owner_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Backfill columns if table exists without new fields
        try { $pdo->exec("ALTER TABLE inventory_hsn ADD COLUMN owner_id INT NOT NULL"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_hsn ADD COLUMN created_by_user_id INT NULL"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_hsn ADD COLUMN rate DECIMAL(5,2) NULL"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_hsn ADD COLUMN note VARCHAR(255) NULL"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_hsn ADD COLUMN active TINYINT(1) DEFAULT 1"); } catch (Throwable $e) {}
        // Replace global unique on code with per-tenant unique
        try { $pdo->exec("ALTER TABLE inventory_hsn DROP INDEX code"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_hsn DROP INDEX uniq_hsn_owner_code"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_hsn ADD UNIQUE KEY uniq_hsn_owner_code (owner_id, code)"); } catch (Throwable $e) {}
    }

    public function getByCode(string $code): ?array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $code = trim($code);
        if ($code === '') { return null; }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT id, code, rate, note, active FROM inventory_hsn WHERE owner_id = ? AND code = ? LIMIT 1");
        $stmt->execute([$ownerId, $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getAll(): array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT id, code, COALESCE(rate,0) AS rate, COALESCE(note,'') AS note, COALESCE(active,1) AS active FROM inventory_hsn WHERE owner_id = ? ORDER BY code");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function create(string $code, ?float $rate = null, ?string $note = null): int {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        $pdo = $this->db->getConnection();
        $code = trim($code);
        if ($code === '') throw new Exception('HSN/SAC code required');
        $stmt = $pdo->prepare("SELECT id FROM inventory_hsn WHERE owner_id = ? AND code = ? LIMIT 1");
        $stmt->execute([$ownerId, $code]);
        $id = (int)($stmt->fetchColumn() ?: 0);
        if ($id > 0) return $id;
        $ins = $pdo->prepare("INSERT INTO inventory_hsn (owner_id, created_by_user_id, code, rate, note) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$ownerId, $userId, $code, $rate, $note]);
        return (int)$pdo->lastInsertId();
    }

    public function update(int $id, string $code, ?float $rate = null, ?string $note = null, int $active = 1): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("UPDATE inventory_hsn SET code = ?, rate = ?, note = ?, active = ? WHERE id = ? AND owner_id = ?");
        return (bool)$stmt->execute([$code, $rate, $note, $active, $id, $ownerId]);
    }

    public function delete(int $id): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM inventory_hsn WHERE id = ? AND owner_id = ?");
        return (bool)$stmt->execute([$id, $ownerId]);
    }
}
