<?php
require_once __DIR__ . '/../config/database.php';

class CustomerAddress {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS customers_addresses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            created_by_user_id INT NULL,
            customer_id INT NOT NULL,
            title VARCHAR(120) NULL,
            line1 VARCHAR(255) NULL,
            line2 VARCHAR(255) NULL,
            city VARCHAR(120) NULL,
            country VARCHAR(100) NULL,
            state VARCHAR(100) NULL,
            pincode VARCHAR(20) NULL,
            gstin VARCHAR(20) NULL,
            extra_key VARCHAR(60) NULL,
            extra_value VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(customer_id), INDEX(city), INDEX(state), INDEX(gstin),
            CONSTRAINT fk_customers_addresses_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo = $this->db->getConnection();
        $pdo->exec($sql);
        // Ensure columns exist for legacy tables
        $cols = $pdo->query('DESCRIBE customers_addresses')->fetchAll(PDO::FETCH_COLUMN);
        $alter = [];
        if (!in_array('owner_id', $cols, true)) { $alter[] = 'ADD COLUMN owner_id INT NULL AFTER id'; }
        if (!in_array('created_by_user_id', $cols, true)) { $alter[] = 'ADD COLUMN created_by_user_id INT NULL AFTER owner_id'; }
        if ($alter) { $pdo->exec('ALTER TABLE customers_addresses ' . implode(', ', $alter)); }
    }

    public function listByCustomer(int $customerId): array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        // For customers, allow them to see their own addresses
        // For admins, filter by owner_id
        if ($ownerId > 0) {
            $stmt = $pdo->prepare('SELECT * FROM customers_addresses WHERE customer_id = ? AND (owner_id = ? OR owner_id IS NULL) ORDER BY id DESC');
            $stmt->execute([$customerId, $ownerId]);
        } else {
            // Customer viewing their own addresses (no owner_id filter)
            $stmt = $pdo->prepare('SELECT * FROM customers_addresses WHERE customer_id = ? ORDER BY id DESC');
            $stmt->execute([$customerId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(int $customerId, array $data): int {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        $stmt = $pdo->prepare("INSERT INTO customers_addresses (owner_id, created_by_user_id, customer_id, title, line1, line2, city, country, state, pincode, gstin, extra_key, extra_value) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $ownerId,
            $userId,
            $customerId,
            $data['title'] ?? null,
            $data['line1'] ?? null,
            $data['line2'] ?? null,
            $data['city'] ?? null,
            $data['country'] ?? null,
            $data['state'] ?? null,
            $data['pincode'] ?? null,
            $data['gstin'] ?? null,
            $data['extra_key'] ?? null,
            $data['extra_value'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("UPDATE customers_addresses SET title=?, line1=?, line2=?, city=?, country=?, state=?, pincode=?, gstin=?, extra_key=?, extra_value=? WHERE id=?");
        return $stmt->execute([
            $data['title'] ?? null,
            $data['line1'] ?? null,
            $data['line2'] ?? null,
            $data['city'] ?? null,
            $data['country'] ?? null,
            $data['state'] ?? null,
            $data['pincode'] ?? null,
            $data['gstin'] ?? null,
            $data['extra_key'] ?? null,
            $data['extra_value'] ?? null,
            $id
        ]);
    }

    public function delete(int $id): bool {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare('DELETE FROM customers_addresses WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
