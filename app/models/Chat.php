<?php
require_once __DIR__ . '/../config/database.php';

class Chat {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS chat_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            sender_user_id INT NOT NULL,
            recipient_user_id INT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(owner_id),
            INDEX(sender_user_id),
            INDEX(recipient_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo = $this->db->getConnection();
        $pdo->exec($sql);
    }

    public function addMessage(int $ownerId, int $senderId, ?int $recipientId, string $message): int {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare('INSERT INTO chat_messages (owner_id, sender_user_id, recipient_user_id, message) VALUES (?,?,?,?)');
        $stmt->execute([$ownerId, $senderId, $recipientId, $message]);
        return (int)$pdo->lastInsertId();
    }

    public function listGlobal(int $ownerId, int $limit = 100): array {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare('SELECT m.*, u.name AS sender_name FROM chat_messages m LEFT JOIN users u ON u.id = m.sender_user_id AND u.owner_id = m.owner_id WHERE m.owner_id = ? AND m.recipient_user_id IS NULL ORDER BY m.id DESC LIMIT ?');
        $stmt->bindValue(1, $ownerId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_reverse($rows);
    }

    public function listDm(int $ownerId, int $userId, int $otherUserId, int $limit = 100): array {
        $pdo = $this->db->getConnection();
        $sql = 'SELECT m.*, u.name AS sender_name
                FROM chat_messages m
                LEFT JOIN users u ON u.id = m.sender_user_id AND u.owner_id = m.owner_id
                WHERE m.owner_id = ?
                  AND (
                    (m.sender_user_id = ? AND m.recipient_user_id = ?)
                    OR (m.sender_user_id = ? AND m.recipient_user_id = ?)
                  )
                ORDER BY m.id DESC
                LIMIT ?';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $ownerId, PDO::PARAM_INT);
        $stmt->bindValue(2, $userId, PDO::PARAM_INT);
        $stmt->bindValue(3, $otherUserId, PDO::PARAM_INT);
        $stmt->bindValue(4, $otherUserId, PDO::PARAM_INT);
        $stmt->bindValue(5, $userId, PDO::PARAM_INT);
        $stmt->bindValue(6, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_reverse($rows);
    }
}
