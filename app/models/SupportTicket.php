<?php
require_once __DIR__ . '/../config/database.php';

class SupportTicket {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS support_tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            customer_id INT NOT NULL,
            issue_type VARCHAR(100) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('pending','open','closed') NOT NULL DEFAULT 'pending',
            priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
            source VARCHAR(50) DEFAULT 'customer_portal',
            created_by_user_id INT NULL,
            assigned_to_user_id INT NULL,
            related_order_id INT NULL,
            related_order_number VARCHAR(50) NULL,
            attachment_path VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            INDEX(owner_id),
            INDEX(customer_id),
            INDEX(status),
            INDEX(assigned_to_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo = $this->db->getConnection();
        $pdo->exec($sql);

        // Ensure new columns exist on legacy tables
        $cols = $pdo->query('DESCRIBE support_tickets')->fetchAll(PDO::FETCH_COLUMN);
        $alter = [];
        if (!in_array('assigned_to_user_id', $cols, true)) {
            $alter[] = 'ADD COLUMN assigned_to_user_id INT NULL AFTER created_by_user_id';
            $alter[] = 'ADD INDEX idx_assigned_to_user_id (assigned_to_user_id)';
        }
        if (!in_array('related_order_id', $cols, true)) {
            $alter[] = 'ADD COLUMN related_order_id INT NULL AFTER assigned_to_user_id';
        }
        if (!in_array('related_order_number', $cols, true)) {
            $alter[] = 'ADD COLUMN related_order_number VARCHAR(50) NULL AFTER related_order_id';
        }
        if (!in_array('attachment_path', $cols, true)) {
            $alter[] = 'ADD COLUMN attachment_path VARCHAR(255) NULL AFTER related_order_number';
        }
        if ($alter) {
            $pdo->exec('ALTER TABLE support_tickets ' . implode(', ', $alter));
        }
    }

    private function generateTicketCode(?string $createdAt, int $id): string {
        // Format: YYYYMMDDHHMM + 6-digit ticket id
        // Example: 202512031045000123 => created 2025-12-03 10:45, internal id 123
        if ($id < 0) { $id = 0; }
        $timestamp = $createdAt ?: date('Y-m-d H:i:s');
        $dt = 
            (
                ($t = strtotime($timestamp)) !== false
            )
            ? date('YmdHi', $t)
            : date('YmdHi');
        return sprintf('%s%06d', $dt, $id);
    }

    public function create(array $data): int {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("INSERT INTO support_tickets (owner_id, customer_id, issue_type, subject, message, status, priority, source, created_by_user_id, assigned_to_user_id, related_order_id, related_order_number, attachment_path) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            (int)$data['owner_id'],
            (int)$data['customer_id'],
            $data['issue_type'],
            $data['subject'],
            $data['message'],
            $data['status'] ?? 'pending',
            $data['priority'] ?? 'medium',
            $data['source'] ?? 'customer_portal',
            $data['created_by_user_id'] ?? null,
            $data['assigned_to_user_id'] ?? null,
            $data['related_order_id'] ?? null,
            $data['related_order_number'] ?? null,
            $data['attachment_path'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public function listByCustomer(int $ownerId, int $customerId): array {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare('SELECT id, issue_type, subject, message, status, priority, source, attachment_path, created_at FROM support_tickets WHERE owner_id = ? AND customer_id = ? ORDER BY created_at DESC, id DESC');
        $stmt->execute([$ownerId, $customerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $rowId = isset($row['id']) ? (int)$row['id'] : 0;
            $row['ticket_code'] = $this->generateTicketCode($row['created_at'] ?? null, $rowId);
        }
        unset($row);
        return $rows;
    }

    public function listForUser(int $ownerId, int $userId, ?string $status = null): array {
        $pdo = $this->db->getConnection();
        $sql = 'SELECT t.id, t.issue_type, t.subject, t.message, t.status, t.priority, t.source, t.attachment_path, t.created_at,
                       t.assigned_to_user_id, u.name AS assigned_to_name,
                       c.company, c.contact_name, c.contact_email
                FROM support_tickets t
                LEFT JOIN customers c ON c.id = t.customer_id AND c.owner_id = t.owner_id
                LEFT JOIN users u ON u.id = t.assigned_to_user_id AND u.owner_id = t.owner_id
                WHERE t.owner_id = ? AND t.assigned_to_user_id = ?';
        $params = [$ownerId, $userId];
        if ($status) {
            $sql .= ' AND t.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY t.created_at DESC, t.id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $rowId = isset($row['id']) ? (int)$row['id'] : 0;
            $row['ticket_code'] = $this->generateTicketCode($row['created_at'] ?? null, $rowId);
        }
        unset($row);
        return $rows;
    }

    public function listByOwner(int $ownerId, ?string $status = null): array {
        $pdo = $this->db->getConnection();
        $sql = 'SELECT t.id, t.issue_type, t.subject, t.message, t.status, t.priority, t.source, t.attachment_path, t.created_at,
                       t.assigned_to_user_id, u.name AS assigned_to_name,
                       c.company, c.contact_name, c.contact_email
                FROM support_tickets t
                LEFT JOIN customers c ON c.id = t.customer_id AND c.owner_id = t.owner_id
                LEFT JOIN users u ON u.id = t.assigned_to_user_id AND u.owner_id = t.owner_id
                WHERE t.owner_id = ?';
        $params = [$ownerId];
        if ($status) {
            $sql .= ' AND t.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY t.created_at DESC, t.id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $rowId = isset($row['id']) ? (int)$row['id'] : 0;
            $row['ticket_code'] = $this->generateTicketCode($row['created_at'] ?? null, $rowId);
        }
        unset($row);
        return $rows;
    }

    public function findById(int $ownerId, int $id): ?array {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare('SELECT * FROM support_tickets WHERE owner_id = ? AND id = ? LIMIT 1');
        $stmt->execute([$ownerId, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        $row['ticket_code'] = $this->generateTicketCode($row['created_at'] ?? null, (int)$row['id']);
        return $row;
    }

    public function updateStatus(int $ownerId, int $id, string $status, ?int $assignedToUserId = null): bool {
        $allowed = ['pending', 'open', 'closed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare('UPDATE support_tickets SET status = ?, assigned_to_user_id = ?, updated_at = NOW() WHERE owner_id = ? AND id = ?');
        $stmt->execute([$status, $assignedToUserId, $ownerId, $id]);
        return $stmt->rowCount() > 0;
    }
}
