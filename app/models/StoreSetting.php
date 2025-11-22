<?php
require_once __DIR__ . '/../config/database.php';

class StoreSetting {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $pdo = $db->getConnection();
        if (!$pdo) { throw new RuntimeException('DB connection failed'); }
        $this->conn = $pdo;
        $this->ensureTable();
        $this->ensureTenantColumns();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS store_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            skey VARCHAR(191) NOT NULL,
            svalue LONGTEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->conn->exec($sql);
    }

    private function ensureTenantColumns(): void {
        try { $this->conn->exec("ALTER TABLE store_settings ADD COLUMN owner_id INT NULL AFTER id"); } catch (Throwable $e) {}
        try { $this->conn->exec("ALTER TABLE store_settings ADD COLUMN created_by_user_id INT NULL AFTER owner_id"); } catch (Throwable $e) {}
        try { $this->conn->exec("ALTER TABLE store_settings DROP INDEX skey"); } catch (Throwable $e) {}
        try { $this->conn->exec("ALTER TABLE store_settings ADD UNIQUE KEY uniq_store_settings_owner_key (owner_id, skey)"); } catch (Throwable $e) {}
    }

    public function getAll(): array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $this->conn->prepare("SELECT skey, svalue FROM store_settings WHERE owner_id = ?");
        $stmt->execute([$ownerId]);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[$row['skey']] = $row['svalue'];
        }
        return $out;
    }

    public function getByKeys(array $keys): array {
        if (empty($keys)) return [];
        $in = implode(',', array_fill(0, count($keys), '?'));
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $this->conn->prepare("SELECT skey, svalue FROM store_settings WHERE owner_id = ? AND skey IN ($in)");
        $stmt->execute(array_merge([$ownerId], array_values($keys)));
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[$row['skey']] = $row['svalue'];
        }
        return $out;
    }

    public function setMany(array $map): void {
        if (empty($map)) return;
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        $this->conn->beginTransaction();
        $stmt = $this->conn->prepare("INSERT INTO store_settings (owner_id, created_by_user_id, skey, svalue) VALUES (:owner_id, :created_by, :k, :v)
            ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)");
        foreach ($map as $k => $v) {
            $stmt->execute([':owner_id' => $ownerId, ':created_by' => $userId, ':k' => (string)$k, ':v' => (string)$v]);
        }
        $this->conn->commit();
    }
}

