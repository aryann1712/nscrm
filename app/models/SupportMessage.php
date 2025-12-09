<?php
require_once __DIR__ . '/../config/database.php';

class SupportMessage {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS support_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            ticket_id INT NOT NULL,
            sender_type ENUM('customer','owner') NOT NULL,
            sender_user_id INT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(owner_id),
            INDEX(ticket_id),
            INDEX(sender_type),
            CONSTRAINT fk_support_messages_ticket FOREIGN KEY (ticket_id)
                REFERENCES support_tickets(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo = $this->db->getConnection();
        $pdo->exec($sql);

        // Ensure columns exist for legacy tables
        $cols = $pdo->query('DESCRIBE support_messages')->fetchAll(PDO::FETCH_COLUMN);
        $alter = [];
        if (!in_array('sender_user_id', $cols, true)) {
            $alter[] = 'ADD COLUMN sender_user_id INT NULL AFTER sender_type';
        }
        if ($alter) {
            $pdo->exec('ALTER TABLE support_messages ' . implode(', ', $alter));
        }
    }

    public function create(int $ownerId, int $ticketId, string $senderType, ?int $senderUserId, string $message): int {
        $pdo = $this->db->getConnection();
        $senderType = $senderType === 'owner' ? 'owner' : 'customer';
        $stmt = $pdo->prepare('INSERT INTO support_messages (owner_id, ticket_id, sender_type, sender_user_id, message) VALUES (?,?,?,?,?)');
        $stmt->execute([
            $ownerId,
            $ticketId,
            $senderType,
            $senderUserId,
            $message,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public function listByTicket(int $ownerId, int $ticketId): array {
        // Use ticket_id as the primary filter so both owner and customer
        // sessions can see the same conversation, even if their owner_id
        // in session differs. Ticket IDs are unique and already validated
        // at the controller layer.
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare('SELECT id, owner_id, ticket_id, sender_type, sender_user_id, message, created_at FROM support_messages WHERE ticket_id = ? ORDER BY created_at ASC, id ASC');
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
