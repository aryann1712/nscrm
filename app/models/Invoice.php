<?php
require_once __DIR__ . '/../config/database.php';

class Invoice {
    private Database $db;
    private ?PDO $pdo;

    // Set to true for the first successful run to recreate table and seed sample data.
    // After it works, set to false so your data will persist.
    private const RECREATE_TABLE_ON_FIRST_RUN = false;

    public function __construct() {
        $this->db = new Database();
        $this->pdo = $this->db->getConnection();
        if (!$this->pdo) {
            throw new Exception('DB connection failed');
        }
        $this->ensureTable();
        $this->ensureTenantColumns();
    }

    private function ensureTable(): void {
        if (self::RECREATE_TABLE_ON_FIRST_RUN) {
            $this->pdo->exec("DROP TABLE IF EXISTS invoices");
        }

        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS invoices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                owner_id INT NOT NULL,
                created_by_user_id INT NOT NULL,
                invoice_no INT NOT NULL,
                customer VARCHAR(200) NOT NULL,
                reference VARCHAR(150) NULL,
                contact_person VARCHAR(150) NULL,
                party_address TEXT NULL,
                shipping_address TEXT NULL,
                issued_on DATE NOT NULL,
                valid_till DATE NULL,
                issued_by VARCHAR(150) NULL,
                type VARCHAR(30) DEFAULT 'Invoice',
                executive VARCHAR(120) NULL,
                status ENUM('Pending','Paid','Partial','Cancelled','Overdue') DEFAULT 'Pending',
                received_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                taxable_total DECIMAL(12,2) NOT NULL DEFAULT 0,
                items_json MEDIUMTEXT,
                terms_json MEDIUMTEXT,
                notes TEXT NULL,
                extra_charge DECIMAL(12,2) NOT NULL DEFAULT 0,
                overall_discount DECIMAL(12,2) NOT NULL DEFAULT 0,
                bank_account_id INT NULL,
                attachment_path VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX(invoice_no),
                INDEX(customer),
                INDEX(issued_on),
                INDEX(status),
                INDEX idx_invoices_owner_id (owner_id),
                INDEX idx_invoices_created_by (created_by_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        // Seed initial demo data if empty and we have an owner context
        $countRow = $this->pdo->query("SELECT COUNT(*) AS c FROM invoices")->fetch(PDO::FETCH_ASSOC);
        $count = (int)($countRow['c'] ?? 0);
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($count === 0 && $ownerId > 0 && $userId > 0) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO invoices
                    (owner_id, created_by_user_id, invoice_no, customer, reference, issued_on, issued_by, type, executive, status, received_amount, amount, taxable_total, items_json, terms_json, notes)
                 VALUES
                    (:own1, :usr1, 8, 'HEAT CRAFT INDUSTRIES', 'REF-001', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'System', 'Invoice', 'Alex', 'Pending', 0.00, 682000.00, 580000.00, :items1, '[]', 'Seed row 1'),
                    (:own2, :usr2, 9, 'ACME CORP', 'REF-002', CURDATE(), 'System', 'Invoice', 'Sam', 'Partial', 120000.00, 220000.00, 200000.00, :items2, '[]', 'Seed row 2')"
            );
            $stmt->execute([
                ':own1' => $ownerId,
                ':usr1' => $userId,
                ':own2' => $ownerId,
                ':usr2' => $userId,
                ':items1' => json_encode([
                    ['name' => 'Item A', 'qty' => 1, 'rate' => 580000.00, 'taxable' => 580000.00, 'gst' => 18],
                ], JSON_UNESCAPED_UNICODE),
                ':items2' => json_encode([
                    ['name' => 'Item B', 'qty' => 2, 'rate' => 100000.00, 'taxable' => 200000.00, 'gst' => 18],
                ], JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    private function ensureTenantColumns(): void {
        try { $this->pdo->exec("ALTER TABLE invoices ADD COLUMN owner_id INT NULL AFTER id"); } catch (Throwable $e) {}
        try { $this->pdo->exec("ALTER TABLE invoices ADD COLUMN created_by_user_id INT NULL AFTER owner_id"); } catch (Throwable $e) {}
        try { $this->pdo->exec("ALTER TABLE invoices ADD INDEX idx_invoices_owner_id (owner_id)"); } catch (Throwable $e) {}
        try { $this->pdo->exec("ALTER TABLE invoices ADD INDEX idx_invoices_created_by (created_by_user_id)"); } catch (Throwable $e) {}
    }

    public function listFiltered(string $period, string $search = ''): array {
        [$from, $to] = $this->resolvePeriod($period);
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $where = [];
        $params = [];

        $where[] = 'owner_id = ?';
        $params[] = $ownerId;
        if ($from !== null) { $where[] = 'issued_on >= ?'; $params[] = $from; }
        if ($to !== null)   { $where[] = 'issued_on <= ?'; $params[] = $to; }
        if ($search !== '') {
            $where[] = '(customer LIKE ? OR CAST(invoice_no AS CHAR) LIKE ?)';
            $params[] = '%'.$search.'%';
            $params[] = '%'.$search.'%';
        }

        $sql = "SELECT id, invoice_no, customer, issued_on, status, amount, received_amount, items_json
                FROM invoices";
        if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
        $sql .= ' ORDER BY issued_on DESC, id DESC LIMIT 200';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['items_json'] = $r['items_json'] ?? '[]';
            $r['status'] = $r['status'] ?? 'Pending';
            $r['received_amount'] = (float)($r['received_amount'] ?? 0);
            $r['amount'] = (float)($r['amount'] ?? 0);
        }
        return $rows;
    }

    private function resolvePeriod(string $period): array {
        $period = trim($period ?: 'this_month');
        $today = new DateTimeImmutable('today');

        switch ($period) {
            case 'last_month':
                $start = $today->modify('first day of last month')->format('Y-m-d');
                $end   = $today->modify('last day of last month')->format('Y-m-d');
                return [$start, $end];
            case 'this_quarter':
                $month = (int)$today->format('n');
                $q = (int)ceil($month / 3);
                $startMonth = (($q - 1) * 3) + 1;
                $start = DateTimeImmutable::createFromFormat('Y-n-j', $today->format('Y').'-'.$startMonth.'-1');
                $end = $start->modify('+2 months')->modify('last day of this month');
                return [$start->format('Y-m-d'), $end->format('Y-m-d')];
            case 'this_year':
                $start = $today->modify('first day of january')->format('Y-m-d');
                $end   = $today->modify('last day of december')->format('Y-m-d');
                return [$start, $end];
            case 'all':
                return [null, null];
            case 'this_month':
            default:
                $start = $today->modify('first day of this month')->format('Y-m-d');
                $end   = $today->modify('last day of this month')->format('Y-m-d');
                return [$start, $end];
        }
    }

    public function getNextInvoiceNo(): int {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $st = $this->pdo->prepare("SELECT MAX(invoice_no) AS mx FROM invoices WHERE owner_id = ?");
        $st->execute([$ownerId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return ((int)($row['mx'] ?? 0)) + 1;
    }

    public function create(array $data): int {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        $stmt = $this->pdo->prepare(
            "INSERT INTO invoices 
                (owner_id, created_by_user_id, invoice_no, customer, reference, contact_person, party_address, shipping_address,
                 issued_on, valid_till, issued_by, type, executive, status, received_amount, amount,
                 items_json, terms_json, notes, extra_charge, overall_discount, bank_account_id, attachment_path, taxable_total)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );

        $itemsJson = $data['items_json'] ?? '[]';
        $itemsArr = json_decode($itemsJson, true);
        if (!is_array($itemsArr)) $itemsArr = [];
        $taxable = 0.0; foreach ($itemsArr as $it) { $taxable += (float)($it['taxable'] ?? 0); }

        $stmt->execute([
            $ownerId,
            $userId,
            (int)($data['invoice_no'] ?? $this->getNextInvoiceNo()),
            trim($data['customer'] ?? ''),
            $data['reference'] ?? null,
            $data['contact_person'] ?? null,
            $data['party_address'] ?? null,
            $data['shipping_address'] ?? null,
            $data['issued_on'] ?? date('Y-m-d'),
            $data['valid_till'] ?? null,
            $data['issued_by'] ?? null,
            $data['type'] ?? 'Invoice',
            $data['executive'] ?? null,
            $data['status'] ?? 'Pending',
            (float)($data['received_amount'] ?? 0),
            (float)($data['amount'] ?? 0),
            $itemsJson,
            $data['terms_json'] ?? '[]',
            $data['notes'] ?? null,
            (float)($data['extra_charge'] ?? 0),
            (float)($data['overall_discount'] ?? 0),
            !empty($data['bank_account_id']) ? (int)$data['bank_account_id'] : null,
            $data['attachment_path'] ?? null,
            isset($data['taxable_total']) ? (float)$data['taxable_total'] : $taxable,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function receive(int $id, float $addReceived, string $newStatus, string $notes): array {
        $row = $this->findById($id);
        if (!$row) { throw new Exception('Invoice not found'); }

        $received = (float)$row['received_amount'] + max(0, $addReceived);
        $amount   = (float)$row['amount'];
        $pending  = max(0, $amount - $received);
        $status   = trim($newStatus) !== '' ? $newStatus : ($pending <= 0.005 ? 'Paid' : ($received > 0 ? 'Partial' : 'Pending'));

        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $this->pdo->prepare("UPDATE invoices SET received_amount=?, status=?, notes = CONCAT(COALESCE(notes,''), ?) WHERE owner_id = ? AND id=?");
        $noteAppend = $notes !== '' ? ("\n[Receive] " . $notes) : '';
        $stmt->execute([$received, $status, $noteAppend, $ownerId, $id]);

        return [
            'status' => $status,
            'received_amount' => $received,
            'pending' => $pending,
        ];
    }

    public function findById(int $id): ?array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $this->pdo->prepare("SELECT * FROM invoices WHERE owner_id = ? AND id=? LIMIT 1");
        $stmt->execute([$ownerId, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function update(int $id, array $data): bool {
        $itemsJson = $data['items_json'] ?? '[]';
        $itemsArr = json_decode($itemsJson, true);
        if (!is_array($itemsArr)) $itemsArr = [];
        $taxable = 0.0; foreach ($itemsArr as $it) { $taxable += (float)($it['taxable'] ?? 0); }

        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $this->pdo->prepare(
            "UPDATE invoices SET
                invoice_no=?, customer=?, reference=?, contact_person=?, party_address=?, shipping_address=?,
                issued_on=?, valid_till=?, issued_by=?, type=?, executive=?, status=?, received_amount=?, amount=?,
                items_json=?, terms_json=?, notes=?, extra_charge=?, overall_discount=?, bank_account_id=?, attachment_path=?, taxable_total=?
             WHERE owner_id = ? AND id=?"
        );

        return $stmt->execute([
            (int)($data['invoice_no'] ?? 0),
            trim($data['customer'] ?? ''),
            $data['reference'] ?? null,
            $data['contact_person'] ?? null,
            $data['party_address'] ?? null,
            $data['shipping_address'] ?? null,
            $data['issued_on'] ?? date('Y-m-d'),
            $data['valid_till'] ?? null,
            $data['issued_by'] ?? null,
            $data['type'] ?? 'Invoice',
            $data['executive'] ?? null,
            $data['status'] ?? 'Pending',
            (float)($data['received_amount'] ?? 0),
            (float)($data['amount'] ?? 0),
            $itemsJson,
            $data['terms_json'] ?? '[]',
            $data['notes'] ?? null,
            (float)($data['extra_charge'] ?? 0),
            (float)($data['overall_discount'] ?? 0),
            !empty($data['bank_account_id']) ? (int)$data['bank_account_id'] : null,
            $data['attachment_path'] ?? null,
            isset($data['taxable_total']) ? (float)$data['taxable_total'] : $taxable,
            $ownerId,
            $id
        ]);
    }
}

