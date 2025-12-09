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
            billing_address TEXT NULL,
            shipping_address TEXT NULL,
            bank_account_id INT NULL,
            notes TEXT NULL,
            sales_credit VARCHAR(150) NULL,
            order_date DATE NULL,
            due_date DATE NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'Pending',
            total DECIMAL(12,2) NOT NULL DEFAULT 0,
            attachment_path VARCHAR(255) NULL,
            terms_json LONGTEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(customer_id), INDEX(status), INDEX(due_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Order items
        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            item_name VARCHAR(200) NOT NULL,
            description TEXT NULL,
            qty DECIMAL(12,2) NOT NULL DEFAULT 1,
            done_qty DECIMAL(12,2) NOT NULL DEFAULT 0,
            unit VARCHAR(20) NOT NULL DEFAULT 'no.s',
            rate DECIMAL(12,2) NOT NULL DEFAULT 0,
            hsn_sac VARCHAR(50) NULL,
            discount DECIMAL(12,2) NOT NULL DEFAULT 0,
            gst_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
            gst_included TINYINT(1) NOT NULL DEFAULT 0,
            taxable DECIMAL(12,2) NOT NULL DEFAULT 0,
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
        // New header fields for richer order form
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN billing_address TEXT NULL AFTER category"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_address TEXT NULL AFTER billing_address"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN bank_account_id INT NULL AFTER shipping_address"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN notes TEXT NULL AFTER bank_account_id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN sales_credit VARCHAR(150) NULL AFTER notes"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN order_date DATE NULL AFTER sales_credit"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN attachment_path VARCHAR(255) NULL AFTER total"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN terms_json LONGTEXT NULL AFTER attachment_path"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD INDEX idx_orders_owner_id (owner_id)"); } catch (Throwable $e) {}
        // Order items
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN owner_id INT NULL AFTER id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN created_by_user_id INT NULL AFTER owner_id"); } catch (Throwable $e) {}
        // New per-item pricing / tax fields
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN hsn_sac VARCHAR(50) NULL AFTER rate"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN discount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER hsn_sac"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN gst_pct DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER discount"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN gst_included TINYINT(1) NOT NULL DEFAULT 0 AFTER gst_pct"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN taxable DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER gst_included"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN description TEXT NULL AFTER item_name"); } catch (Throwable $e) {}
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

            // Auto-assign next order number per owner if none provided
            $orderNo = trim((string)($data['order_no'] ?? ''));
            if ($orderNo === '') {
                $stNext = $pdo->prepare('SELECT MAX(CAST(order_no AS UNSIGNED)) AS max_no FROM orders WHERE owner_id = ?');
                $stNext->execute([$ownerId]);
                $rowNext = $stNext->fetch(PDO::FETCH_ASSOC);
                $next = isset($rowNext['max_no']) ? (int)$rowNext['max_no'] : 0;
                $orderNo = (string)($next + 1);
            }
            $st = $pdo->prepare('INSERT INTO orders (
                owner_id, created_by_user_id, customer_id, contact_name, order_no, customer_po, category,
                billing_address, shipping_address, bank_account_id, notes, sales_credit,
                order_date, due_date, status, attachment_path, terms_json
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $st->execute([
                $ownerId,
                $userId,
                (int)$data['customer_id'],
                $data['contact_name'] ?? null,
                $orderNo,
                $data['customer_po'] ?? null,
                $data['category'] ?? null,
                $data['billing_address'] ?? null,
                $data['shipping_address'] ?? null,
                isset($data['bank_account_id']) ? (int)$data['bank_account_id'] : null,
                $data['notes'] ?? null,
                $data['sales_credit'] ?? null,
                !empty($data['order_date']) ? $data['order_date'] : null,
                !empty($data['due_date']) ? $data['due_date'] : null,
                $data['status'] ?? 'Purchase order / Work Order Received',
                $data['attachment_path'] ?? null,
                isset($data['terms']) && is_array($data['terms']) ? json_encode(array_values($data['terms']), JSON_UNESCAPED_UNICODE) : json_encode([], JSON_UNESCAPED_UNICODE),
            ]);
            $orderId = (int)$pdo->lastInsertId();
            $total = 0;
            $items = is_array($data['items'] ?? null) ? $data['items'] : [];
            if ($items) {
                $ist = $pdo->prepare('INSERT INTO order_items (
                    owner_id, created_by_user_id, order_id,
                    item_name, description, qty, done_qty, unit, rate,
                    hsn_sac, discount, gst_pct, gst_included, taxable, amount
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                foreach ($items as $it) {
                    $name = trim($it['item_name'] ?? '');
                    if ($name === '') { continue; }
                    $qty = (float)($it['qty'] ?? 1);
                    $unit = $it['unit'] ?? 'no.s';
                    $rate = (float)($it['rate'] ?? 0);
                    $hsn  = $it['hsn_sac'] ?? null;
                    $desc = $it['description'] ?? null;
                    $discount = (float)($it['discount'] ?? 0);
                    $gstPct   = (float)($it['gst_pct'] ?? 0);
                    $gstIncl  = !empty($it['gst_included']) ? 1 : 0;
                    $taxable  = isset($it['taxable']) ? (float)$it['taxable'] : max($qty * $rate - $discount, 0);
                    $amount   = isset($it['amount']) ? (float)$it['amount'] : $taxable;
                    $total   += $amount;
                    $ist->execute([
                        $ownerId,
                        $userId,
                        $orderId,
                        $name,
                        $desc,
                        $qty,
                        0,
                        $unit,
                        $rate,
                        $hsn,
                        $discount,
                        $gstPct,
                        $gstIncl,
                        $taxable,
                        $amount,
                    ]);
                }
            }

            // Persist order-local Terms & Conditions only into JSON column (no more order_terms wiring)
            $terms = is_array($data['terms'] ?? null) ? $data['terms'] : [];
            $termsJson = json_encode(array_values($terms), JSON_UNESCAPED_UNICODE);
            $upd = $pdo->prepare('UPDATE orders SET total = ?, terms_json = ? WHERE owner_id = ? AND id = ?');
            $upd->execute([$total, $termsJson, $ownerId, $orderId]);
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
        $it = $pdo->prepare('SELECT 
                id, item_name, description, qty, done_qty, unit, rate,
                hsn_sac, discount, gst_pct, gst_included, taxable, amount
            FROM order_items
            WHERE owner_id = ? AND order_id = ?
            ORDER BY id ASC');
        $it->execute([$ownerId, $id]);
        $items = $it->fetchAll(PDO::FETCH_ASSOC);

        // Load order-local Terms & Conditions
        $terms = [];
        if (!empty($order['terms_json'])) {
            try {
                $decoded = json_decode((string)$order['terms_json'], true);
                if (is_array($decoded)) {
                    foreach ($decoded as $t) {
                        $t = trim((string)$t);
                        if ($t !== '') { $terms[] = $t; }
                    }
                }
            } catch (Throwable $e) { /* ignore */ }
        }
        $subtotal = 0.0; foreach ($items as $row) { $subtotal += (float)($row['amount'] ?? 0); }
        $order['items'] = $items;
        $order['terms'] = $terms;
        $order['pre_tax'] = $subtotal;
        $order['amount'] = (float)($order['total'] ?? $subtotal);
        return $order;
    }
    
    public function update(int $id, array $data): bool {
        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $userId  = (int)($_SESSION['user']['id'] ?? 0);
            if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }

            // Ensure the order belongs to this owner
            $chk = $pdo->prepare('SELECT id FROM orders WHERE owner_id = ? AND id = ?');
            $chk->execute([$ownerId, $id]);
            if (!$chk->fetchColumn()) {
                throw new Exception('Order not found');
            }

            // Update header (keep total 0 for now, will recalc below)
            $up = $pdo->prepare('UPDATE orders SET 
                customer_id = ?, contact_name = ?, order_no = ?, customer_po = ?, category = ?,
                billing_address = ?, shipping_address = ?, bank_account_id = ?, notes = ?, sales_credit = ?,
                order_date = ?, due_date = ?, status = ?, total = 0
            WHERE owner_id = ? AND id = ?');
            $up->execute([
                (int)($data['customer_id'] ?? 0),
                $data['contact_name'] ?? null,
                $data['order_no'] ?? null,
                $data['customer_po'] ?? null,
                $data['category'] ?? null,
                $data['billing_address'] ?? null,
                $data['shipping_address'] ?? null,
                isset($data['bank_account_id']) ? (int)$data['bank_account_id'] : null,
                $data['notes'] ?? null,
                $data['sales_credit'] ?? null,
                !empty($data['order_date']) ? $data['order_date'] : null,
                !empty($data['due_date']) ? $data['due_date'] : null,
                $data['status'] ?? 'Pending',
                $ownerId,
                $id,
            ]);

            // Clear existing items for this order (terms are now only in JSON column)
            $pdo->prepare('DELETE FROM order_items WHERE owner_id = ? AND order_id = ?')->execute([$ownerId, $id]);

            // Reinsert items and recompute total (mirror create() structure, including description)
            $total = 0;
            $items = is_array($data['items'] ?? null) ? $data['items'] : [];
            if ($items) {
                $ist = $pdo->prepare('INSERT INTO order_items (
                    owner_id, created_by_user_id, order_id,
                    item_name, description, qty, done_qty, unit, rate,
                    hsn_sac, discount, gst_pct, gst_included, taxable, amount
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                foreach ($items as $it) {
                    $name = trim($it['item_name'] ?? '');
                    if ($name === '') { continue; }
                    $qty = (float)($it['qty'] ?? 1);
                    $unit = $it['unit'] ?? 'no.s';
                    $rate = (float)($it['rate'] ?? 0);
                    $hsn  = $it['hsn_sac'] ?? null;
                    $desc = $it['description'] ?? null;
                    $discount = (float)($it['discount'] ?? 0);
                    $gstPct   = (float)($it['gst_pct'] ?? 0);
                    $gstIncl  = !empty($it['gst_included']) ? 1 : 0;
                    $taxable  = isset($it['taxable']) ? (float)$it['taxable'] : max($qty * $rate - $discount, 0);
                    $amount   = isset($it['amount']) ? (float)$it['amount'] : $taxable;
                    $total   += $amount;
                    $ist->execute([
                        $ownerId,
                        $userId,
                        $id,
                        $name,
                        $desc,
                        $qty,
                        0,
                        $unit,
                        $rate,
                        $hsn,
                        $discount,
                        $gstPct,
                        $gstIncl,
                        $taxable,
                        $amount,
                    ]);
                }
            }

            // Persist order-local Terms & Conditions only into JSON column
            $terms = is_array($data['terms'] ?? null) ? $data['terms'] : [];
            $termsJson = json_encode(array_values($terms), JSON_UNESCAPED_UNICODE);
            $upd = $pdo->prepare('UPDATE orders SET total = ?, terms_json = ? WHERE owner_id = ? AND id = ?');
            $upd->execute([$total, $termsJson, $ownerId, $id]);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo->beginTransaction();
        try {
            // Ensure ownership
            $chk = $pdo->prepare('SELECT id FROM orders WHERE owner_id = ? AND id = ?');
            $chk->execute([$ownerId, $id]);
            if (!$chk->fetchColumn()) {
                $pdo->rollBack();
                return false;
            }
            // Delete children first
            $pdo->prepare('DELETE FROM order_items WHERE owner_id = ? AND order_id = ?')->execute([$ownerId, $id]);
            $pdo->prepare('DELETE FROM order_terms WHERE owner_id = ? AND order_id = ?')->execute([$ownerId, $id]);
            // Delete header
            $del = $pdo->prepare('DELETE FROM orders WHERE owner_id = ? AND id = ?');
            $ok = $del->execute([$ownerId, $id]);
            $pdo->commit();
            return $ok;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}

