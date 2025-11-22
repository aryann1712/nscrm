<?php
require_once __DIR__ . '/../config/database.php';

class Order {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTables();
        $this->ensureTenantColumns();
    }

    private function ensureTables(): void {
        $pdo = $this->db->getConnection();
        // Orders table
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            contact_name VARCHAR(150) NULL,
            order_no VARCHAR(50) NULL,
            customer_po VARCHAR(100) NULL,
            category VARCHAR(100) NULL,
            due_date DATE NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'Pending',
            total DECIMAL(12,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(customer_id), INDEX(status), INDEX(due_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Order items
        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            item_name VARCHAR(200) NOT NULL,
            qty DECIMAL(12,2) NOT NULL DEFAULT 1,
            done_qty DECIMAL(12,2) NOT NULL DEFAULT 0,
            unit VARCHAR(20) NOT NULL DEFAULT 'no.s',
            rate DECIMAL(12,2) NOT NULL DEFAULT 0,
            amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            INDEX(order_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Order-local Terms & Conditions
        $pdo->exec("CREATE TABLE IF NOT EXISTS order_terms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            term_text VARCHAR(500) NOT NULL,
            display_order INT NOT NULL DEFAULT 0,
            INDEX(order_id), INDEX(display_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    private function ensureTenantColumns(): void {
        $pdo = $this->db->getConnection();
        // Orders
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN owner_id INT NULL AFTER id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN created_by_user_id INT NULL AFTER owner_id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD INDEX idx_orders_owner_id (owner_id)"); } catch (Throwable $e) {}
        // Order items
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN owner_id INT NULL AFTER id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN created_by_user_id INT NULL AFTER owner_id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_items ADD INDEX idx_order_items_owner_id (owner_id)"); } catch (Throwable $e) {}
        // Order terms
        try { $pdo->exec("ALTER TABLE order_terms ADD COLUMN owner_id INT NULL AFTER id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_terms ADD COLUMN created_by_user_id INT NULL AFTER owner_id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_terms ADD INDEX idx_order_terms_owner_id (owner_id)"); } catch (Throwable $e) {}
    }

    public function getAll(array $filters = []): array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $w = ['o.owner_id = ?'];
        $p = [$ownerId];
        if (!empty($filters['status'])) { $w[]='status = ?'; $p[]=$filters['status']; }
        if (!empty($filters['q'])) { $w[]='(order_no LIKE ? OR customer_po LIKE ? OR category LIKE ?)'; $p[]='%'.$filters['q'].'%'; $p[]='%'.$filters['q'].'%'; $p[]='%'.$filters['q'].'%'; }
        $sql = 'SELECT 
            o.*, 
            c.company AS customer_name,
            (
              SELECT oi.item_name FROM order_items oi 
              WHERE oi.owner_id = o.owner_id AND oi.order_id = o.id 
              ORDER BY oi.id ASC LIMIT 1
            ) AS first_item_name,
            (
              SELECT oi.qty FROM order_items oi 
              WHERE oi.owner_id = o.owner_id AND oi.order_id = o.id 
              ORDER BY oi.id ASC LIMIT 1
            ) AS first_item_qty,
            (
              SELECT oi.done_qty FROM order_items oi 
              WHERE oi.owner_id = o.owner_id AND oi.order_id = o.id 
              ORDER BY oi.id ASC LIMIT 1
            ) AS first_item_done_qty,
            (
              SELECT oi.unit FROM order_items oi 
              WHERE oi.owner_id = o.owner_id AND oi.order_id = o.id 
              ORDER BY oi.id ASC LIMIT 1
            ) AS first_item_unit
          FROM orders o 
          LEFT JOIN customers c ON c.id = o.customer_id AND c.owner_id = o.owner_id';
        if ($w) { $sql .= ' WHERE '.implode(' AND ', $w); }
        $sql .= ' ORDER BY o.created_at DESC';
        $st = $pdo->prepare($sql);
        $st->execute($p);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int {
        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $userId  = (int)($_SESSION['user']['id'] ?? 0);
            if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
            $st = $pdo->prepare('INSERT INTO orders (owner_id, created_by_user_id, customer_id, contact_name, order_no, customer_po, category, due_date, status, total) VALUES (?,?,?,?,?,?,?,?,?,0)');
            $st->execute([
                $ownerId,
                $userId,
                (int)$data['customer_id'],
                $data['contact_name'] ?? null,
                $data['order_no'] ?? null,
                $data['customer_po'] ?? null,
                $data['category'] ?? null,
                !empty($data['due_date']) ? $data['due_date'] : null,
                $data['status'] ?? 'Pending',
            ]);
            $orderId = (int)$pdo->lastInsertId();
            $total = 0;
            $items = is_array($data['items'] ?? null) ? $data['items'] : [];
            if ($items) {
                $ist = $pdo->prepare('INSERT INTO order_items (owner_id, created_by_user_id, order_id, item_name, qty, done_qty, unit, rate, amount) VALUES (?,?,?,?,?,?,?,?,?)');
                foreach ($items as $it) {
                    $name = trim($it['item_name'] ?? '');
                    if ($name === '') { continue; }
                    $qty = (float)($it['qty'] ?? 1);
                    $unit = $it['unit'] ?? 'no.s';
                    $rate = (float)($it['rate'] ?? 0);
                    $amount = $qty * $rate;
                    $total += $amount;
                    $ist->execute([$ownerId, $userId, $orderId, $name, $qty, 0, $unit, $rate, $amount]);
                }
            }

            // Persist order-local Terms & Conditions
            $terms = is_array($data['terms'] ?? null) ? $data['terms'] : [];
            if ($terms) {
                $tst = $pdo->prepare('INSERT INTO order_terms (owner_id, created_by_user_id, order_id, term_text, display_order) VALUES (?,?,?,?,?)');
                $i = 1;
                foreach ($terms as $t) {
                    $text = trim((string)$t);
                    if ($text === '') { continue; }
                    $tst->execute([$ownerId, $userId, $orderId, $text, $i++]);
                }
            }
            $upd = $pdo->prepare('UPDATE orders SET total = ? WHERE owner_id = ? AND id = ?');
            $upd->execute([$total, $ownerId, $orderId]);
            $pdo->commit();
            return $orderId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $id, string $status): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $st = $pdo->prepare('UPDATE orders SET status = ? WHERE owner_id = ? AND id = ?');
        return $st->execute([$status, $ownerId, $id]);
    }

    public function getWithDetails(int $id): ?array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $st = $pdo->prepare('SELECT o.*, c.company AS customer_name FROM orders o LEFT JOIN customers c ON c.id = o.customer_id AND c.owner_id = o.owner_id WHERE o.owner_id = ? AND o.id = ?');
        $st->execute([$ownerId, $id]);
        $order = $st->fetch(PDO::FETCH_ASSOC);
        if (!$order) { return null; }
        $it = $pdo->prepare('SELECT id, item_name, qty, done_qty, unit, rate, amount FROM order_items WHERE owner_id = ? AND order_id = ? ORDER BY id ASC');
        $it->execute([$ownerId, $id]);
        $items = $it->fetchAll(PDO::FETCH_ASSOC);
        $subtotal = 0.0; foreach ($items as $row) { $subtotal += (float)($row['amount'] ?? 0); }
        // For now, no tax fields; expose both pre_tax and amount as subtotal/total
        $order['items'] = $items;
        $order['pre_tax'] = $subtotal;
        $order['amount'] = (float)($order['total'] ?? $subtotal);
        return $order;
    }
}

