<?php
require_once __DIR__ . '/../config/database.php';

class InventoryStore {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTables();
    }

    private function ensureTables(): void {
        $pdo = $this->db->getConnection();
        // Stores table
        $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_stores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            created_by_user_id INT NULL,
            name VARCHAR(191) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_store_owner_name (owner_id, name),
            KEY idx_store_owner (owner_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        try { $pdo->exec("ALTER TABLE inventory_stores ADD COLUMN owner_id INT NOT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_stores ADD COLUMN created_by_user_id INT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_stores DROP INDEX name"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_stores DROP INDEX uniq_store_owner_name"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_stores ADD UNIQUE KEY uniq_store_owner_name (owner_id, name)"); } catch (\Throwable $e) {}
        // Mapping table
        $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_store_users (
            store_id INT NOT NULL,
            user_id INT NOT NULL,
            owner_id INT NOT NULL,
            PRIMARY KEY (store_id, user_id),
            KEY idx_store_users_owner (owner_id),
            CONSTRAINT fk_store_users_store FOREIGN KEY (store_id) REFERENCES inventory_stores(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        try { $pdo->exec("ALTER TABLE inventory_store_users ADD COLUMN owner_id INT NOT NULL"); } catch (\Throwable $e) {}
    }

    public function listStoresWithUsers(): array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stores = $pdo->prepare("SELECT id, name FROM inventory_stores WHERE owner_id = ? ORDER BY name");
        $stores->execute([$ownerId]);
        $rows = $stores->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (!$rows) return [];
        $ids = array_column($rows, 'id');
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT su.store_id, u.id, u.name FROM inventory_store_users su JOIN users u ON u.id = su.user_id WHERE su.owner_id = ? AND su.store_id IN ($in) AND (u.type IS NULL OR u.type != 'customer') ORDER BY u.name");
        $stmt->execute(array_merge([$ownerId], $ids));
        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $map[(int)$r['store_id']][] = [ 'id' => (int)$r['id'], 'name' => $r['name'] ];
        }
        $out = [];
        foreach ($rows as $r) {
            $sid = (int)$r['id'];
            $out[] = [ 'id' => $sid, 'name' => $r['name'], 'users' => $map[$sid] ?? [] ];
        }
        return $out;
    }

    public function getOwnerUsers(): array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE owner_id = ? AND (type IS NULL OR type != 'customer') ORDER BY is_owner DESC, name");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createStore(string $name, array $userIds, int $storeId = 0): int {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        $name = trim($name);
        if ($name === '') { throw new Exception('Store name required'); }
        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            $sid = (int)$storeId;
            if ($sid > 0) {
                // Update name of existing store (enforce per-tenant unique)
                $chk = $pdo->prepare("SELECT id FROM inventory_stores WHERE owner_id = ? AND name = ? AND id <> ? LIMIT 1");
                $chk->execute([$ownerId, $name, $sid]);
                if ($chk->fetchColumn()) { throw new Exception('Store name already exists.'); }
                $upd = $pdo->prepare("UPDATE inventory_stores SET name = ? WHERE id = ? AND owner_id = ?");
                $upd->execute([$name, $sid, $ownerId]);
            } else {
                // Insert new store, avoid dup by name
                $sel = $pdo->prepare("SELECT id FROM inventory_stores WHERE owner_id = ? AND name = ? LIMIT 1");
                $sel->execute([$ownerId, $name]);
                $sid = (int)($sel->fetchColumn() ?: 0);
                if ($sid === 0) {
                    $ins = $pdo->prepare("INSERT INTO inventory_stores (owner_id, created_by_user_id, name) VALUES (?, ?, ?)");
                    $ins->execute([$ownerId, $userId, $name]);
                    $sid = (int)$pdo->lastInsertId();
                }
            }
            // Replace assignments
            $del = $pdo->prepare("DELETE FROM inventory_store_users WHERE owner_id = ? AND store_id = ?");
            $del->execute([$ownerId, $sid]);
            if (!empty($userIds)) {
                $insU = $pdo->prepare("INSERT INTO inventory_store_users (store_id, user_id, owner_id) VALUES (?, ?, ?)");
                foreach ($userIds as $uid) {
                    $uid = (int)$uid; if ($uid <= 0) continue;
                    $insU->execute([$sid, $uid, $ownerId]);
                }
            }
            $pdo->commit();
            return $sid;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function deleteStore(int $storeId): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM inventory_stores WHERE id = ? AND owner_id = ?");
        return (bool)$stmt->execute([$storeId, $ownerId]);
    }
}
