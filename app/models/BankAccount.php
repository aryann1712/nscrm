<?php
require_once __DIR__ . '/../config/database.php';

class BankAccount {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
        $this->ensureTenantColumns();
    }

    public function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS bank_accounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bank_name VARCHAR(100) NOT NULL,
            account_no VARCHAR(50) NOT NULL,
            branch VARCHAR(100) NULL,
            ifsc VARCHAR(20) NULL,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(bank_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->getConnection()->exec($sql);
    }

    private function ensureTenantColumns(): void {
        $pdo = $this->db->getConnection();
        try { $pdo->exec("ALTER TABLE bank_accounts ADD COLUMN owner_id INT NULL AFTER id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE bank_accounts ADD COLUMN created_by_user_id INT NULL AFTER owner_id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE bank_accounts ADD INDEX idx_bank_accounts_owner_id (owner_id)"); } catch (Throwable $e) {}
    }

    public function getAll(): array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $pdo->prepare("SELECT * FROM bank_accounts WHERE owner_id = ? ORDER BY is_default DESC, bank_name ASC");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        if (!empty($data['is_default'])) {
            $rst = $pdo->prepare("UPDATE bank_accounts SET is_default = 0 WHERE owner_id = ?");
            $rst->execute([$ownerId]);
        }
        $stmt = $pdo->prepare("INSERT INTO bank_accounts (owner_id, created_by_user_id, bank_name, account_no, branch, ifsc, is_default) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            $ownerId,
            $userId,
            trim($data['bank_name']),
            trim($data['account_no']),
            $data['branch'] ?? null,
            $data['ifsc'] ?? null,
            !empty($data['is_default']) ? 1 : 0,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        if (!empty($data['is_default'])) {
            $rst = $pdo->prepare("UPDATE bank_accounts SET is_default = 0 WHERE owner_id = ?");
            $rst->execute([$ownerId]);
        }
        $stmt = $pdo->prepare("UPDATE bank_accounts SET bank_name=?, account_no=?, branch=?, ifsc=?, is_default=? WHERE owner_id = ? AND id=?");
        return $stmt->execute([
            trim($data['bank_name']),
            trim($data['account_no']),
            $data['branch'] ?? null,
            $data['ifsc'] ?? null,
            !empty($data['is_default']) ? 1 : 0,
            $ownerId,
            $id
        ]);
    }

    public function delete(int $id): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $pdo->prepare("DELETE FROM bank_accounts WHERE owner_id = ? AND id=?");
        return $stmt->execute([$ownerId, $id]);
    }

    public function findById(int $id): ?array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $pdo->prepare("SELECT * FROM bank_accounts WHERE owner_id = ? AND id=? LIMIT 1");
        $stmt->execute([$ownerId, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

