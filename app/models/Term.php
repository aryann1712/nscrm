<?php
require_once __DIR__ . '/../config/database.php';

class Term {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
    }

    public function ensureTable(): void {
        $pdo = $this->db->getConnection();
        $sql = "CREATE TABLE IF NOT EXISTS terms_master (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            created_by_user_id INT NULL,
            text VARCHAR(500) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            display_order INT NOT NULL DEFAULT 1000,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(is_active), INDEX(display_order),
            KEY idx_terms_owner_id (owner_id),
            KEY idx_terms_created_by (created_by_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        // Add missing columns if upgrading
        try { $pdo->exec("ALTER TABLE terms_master ADD COLUMN owner_id INT NOT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE terms_master ADD COLUMN created_by_user_id INT NULL"); } catch (\Throwable $e) {}
    }

    public function getAllActive(): array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT id, text, is_active, display_order FROM terms_master WHERE owner_id = ? AND is_active=1 ORDER BY display_order, id");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT id, text, is_active, display_order FROM terms_master WHERE owner_id = ? ORDER BY display_order, id");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $text, bool $isActive = true, int $order = 1000): int {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new \Exception('User not authenticated'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("INSERT INTO terms_master (owner_id, created_by_user_id, text, is_active, display_order) VALUES (?,?,?,?,?)");
        $stmt->execute([$ownerId, $userId, trim($text), $isActive ? 1 : 0, $order]);
        return (int)$pdo->lastInsertId();
    }

    public function update(int $id, string $text, bool $isActive): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("UPDATE terms_master SET text=?, is_active=? WHERE id=? AND owner_id = ?");
        return $stmt->execute([trim($text), $isActive ? 1 : 0, $id, $ownerId]);
    }

    public function toggle(int $id, bool $isActive): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("UPDATE terms_master SET is_active=? WHERE id=? AND owner_id = ?");
        return $stmt->execute([$isActive ? 1 : 0, $id, $ownerId]);
    }

    public function delete(int $id): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM terms_master WHERE id=? AND owner_id = ?");
        return $stmt->execute([$id, $ownerId]);
    }

    public function reorder(array $orders): void {
        // $orders is an array of [id => order]
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new \Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("UPDATE terms_master SET display_order=? WHERE id=? AND owner_id = ?");
        foreach ($orders as $id => $order) {
            $stmt->execute([ (int)$order, (int)$id, $ownerId ]);
        }
    }
}
