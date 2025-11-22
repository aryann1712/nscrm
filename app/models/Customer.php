<?php
require_once __DIR__ . '/../config/database.php';

class Customer {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company VARCHAR(200) NOT NULL,
            contact_name VARCHAR(150) NULL,
            contact_phone VARCHAR(30) NULL,
            contact_email VARCHAR(150) NULL,
            relation VARCHAR(50) NULL,
            type ENUM('customer','supplier','neighbour','friend') DEFAULT 'customer',
            executive VARCHAR(100) NULL,
            city VARCHAR(120) NULL,
            last_talk DATE NULL,
            next_action DATE NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(company), INDEX(contact_name), INDEX(type), INDEX(city)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->getConnection()->exec($sql);

        // Ensure optional columns exist for extended form fields
        $pdo = $this->db->getConnection();
        $stmt = $pdo->query('DESCRIBE customers');
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $toAdd = [];
        if (!in_array('website', $columns, true)) {
            $toAdd[] = 'ADD COLUMN website VARCHAR(200) NULL AFTER relation';
        }
        if (!in_array('industry_segment', $columns, true)) {
            $toAdd[] = 'ADD COLUMN industry_segment VARCHAR(150) NULL AFTER website';
        }
        if (!in_array('country', $columns, true)) {
            $toAdd[] = 'ADD COLUMN country VARCHAR(100) NULL AFTER industry_segment';
        }
        if (!in_array('state', $columns, true)) {
            $toAdd[] = 'ADD COLUMN state VARCHAR(100) NULL AFTER country';
        }
        if (!in_array('owner_id', $columns, true)) {
            // When altering an existing table with data, add as NULL to avoid failing the migration.
            // The CREATE TABLE path already defines NOT NULL for fresh installs.
            $toAdd[] = 'ADD COLUMN owner_id INT NULL AFTER id';
        }
        if (!in_array('created_by_user_id', $columns, true)) {
            $toAdd[] = 'ADD COLUMN created_by_user_id INT NULL AFTER owner_id';
        }
        if ($toAdd) {
            $pdo->exec('ALTER TABLE customers ' . implode(', ', $toAdd));
        }
    }

    public function getAll(array $filters = []): array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $where = [];
        $params = [];
        $where[] = 'owner_id = ?';
        $params[] = $ownerId;
        if (!empty($filters['type'])) { $where[] = 'type = ?'; $params[] = $filters['type']; }
        if (!empty($filters['city'])) { $where[] = 'city = ?'; $params[] = $filters['city']; }
        if (!empty($filters['executive'])) { $where[] = 'executive = ?'; $params[] = $filters['executive']; }
        if (isset($filters['active'])) { $where[] = 'is_active = ?'; $params[] = (int)$filters['active']; }
        if (!empty($filters['q'])) { $where[] = '(company LIKE ? OR contact_name LIKE ?)'; $params[] = '%'.$filters['q'].'%'; $params[] = '%'.$filters['q'].'%'; }
        $sql = 'SELECT id, company, contact_name, contact_phone, contact_email, relation, website, industry_segment, country, state, type, executive, city, last_talk, next_action, is_active FROM customers';
        if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
        $sql .= ' ORDER BY company ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        // Compose contact_name if name parts provided
        if (!empty($data['first_name']) || !empty($data['last_name'])) {
            $title = trim($data['title'] ?? '');
            $first = trim($data['first_name'] ?? '');
            $last = trim($data['last_name'] ?? '');
            $composed = trim(trim($title . ' ' . $first . ' ' . $last));
            if ($composed !== '') { $data['contact_name'] = $composed; }
        }

        $stmt = $pdo->prepare("INSERT INTO customers (owner_id, created_by_user_id, company, contact_name, contact_phone, contact_email, relation, website, industry_segment, country, state, type, executive, city, last_talk, next_action, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $ownerId,
            $userId,
            trim($data['company'] ?? ''),
            $data['contact_name'] ?? null,
            $data['contact_phone'] ?? null,
            $data['contact_email'] ?? null,
            $data['relation'] ?? null,
            $data['website'] ?? null,
            $data['industry_segment'] ?? null,
            $data['country'] ?? null,
            $data['state'] ?? null,
            in_array(($data['type'] ?? 'customer'), ['customer','supplier','neighbour','friend']) ? $data['type'] : 'customer',
            $data['executive'] ?? null,
            $data['city'] ?? null,
            !empty($data['last_talk']) ? $data['last_talk'] : null,
            !empty($data['next_action']) ? $data['next_action'] : null,
            !empty($data['is_active']) ? 1 : 0,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public function get(int $id): ?array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $pdo->prepare('SELECT id, company, contact_name, contact_phone, contact_email, relation, website, industry_segment, country, state, type, executive, city, last_talk, next_action, is_active FROM customers WHERE owner_id = ? AND id = ? LIMIT 1');
        $stmt->execute([$ownerId, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function update(int $id, array $data): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        // Compose contact_name if name parts provided on update
        if (!empty($data['first_name']) || !empty($data['last_name'])) {
            $title = trim($data['title'] ?? '');
            $first = trim($data['first_name'] ?? '');
            $last = trim($data['last_name'] ?? '');
            $composed = trim(trim($title . ' ' . $first . ' ' . $last));
            if ($composed !== '') { $data['contact_name'] = $composed; }
        }

        $stmt = $pdo->prepare("UPDATE customers SET company=?, contact_name=?, contact_phone=?, contact_email=?, relation=?, website=?, industry_segment=?, country=?, state=?, type=?, executive=?, city=?, last_talk=?, next_action=?, is_active=? WHERE owner_id=? AND id=?");
        return $stmt->execute([
            trim($data['company'] ?? ''),
            $data['contact_name'] ?? null,
            $data['contact_phone'] ?? null,
            $data['contact_email'] ?? null,
            $data['relation'] ?? null,
            $data['website'] ?? null,
            $data['industry_segment'] ?? null,
            $data['country'] ?? null,
            $data['state'] ?? null,
            in_array(($data['type'] ?? 'customer'), ['customer','supplier','neighbour','friend']) ? $data['type'] : 'customer',
            $data['executive'] ?? null,
            $data['city'] ?? null,
            !empty($data['last_talk']) ? $data['last_talk'] : null,
            !empty($data['next_action']) ? $data['next_action'] : null,
            !empty($data['is_active']) ? 1 : 0,
            $ownerId,
            $id
        ]);
    }

    public function delete(int $id): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $pdo->prepare('DELETE FROM customers WHERE owner_id = ? AND id = ?');
        return $stmt->execute([$ownerId, $id]);
    }

    public function getByEmail(string $email): ?array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $pdo->prepare('SELECT * FROM customers WHERE owner_id = ? AND contact_email = ? LIMIT 1');
        $stmt->execute([$ownerId, $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }
}

