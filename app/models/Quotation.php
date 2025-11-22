<?php
require_once __DIR__ . '/../config/database.php';

class Quotation {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS quotations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quote_no INT NOT NULL,
            customer VARCHAR(255) NOT NULL,
            reference VARCHAR(255) NULL,
            contact_person VARCHAR(100) NULL,
            party_address TEXT NULL,
            amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            valid_till DATE NULL,
            issued_on DATE NULL,
            issued_by VARCHAR(100) NULL,
            type ENUM('Quotation','Proforma') NOT NULL DEFAULT 'Quotation',
            executive VARCHAR(100) NULL,
            response VARCHAR(255) NULL,
            items_json JSON NULL,
            terms_json JSON NULL,
            notes TEXT NULL,
            extra_charge DECIMAL(12,2) NOT NULL DEFAULT 0,
            overall_discount DECIMAL(12,2) NOT NULL DEFAULT 0,
            overall_gst_pct DECIMAL(6,2) NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'Open',
            attachment_path VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (quote_no),
            INDEX (issued_on),
            INDEX (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->getConnection()->exec($sql);
    }

    public function ensureSchema(): void {
        $pdo = $this->db->getConnection();
        $addIfMissing = function(string $table, string $column, string $definition) use ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
                $stmt->execute([$table, $column]);
                $exists = (int)$stmt->fetchColumn() > 0;
                if (!$exists) {
                    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN {$column} {$definition}");
                }
            } catch (Throwable $e) { /* ignore to avoid breaking UI */ }
        };

        $addIfMissing('quotations', 'reference', 'VARCHAR(255) NULL');
        $addIfMissing('quotations', 'contact_person', 'VARCHAR(100) NULL');
        $addIfMissing('quotations', 'party_address', 'TEXT NULL');
        $addIfMissing('quotations', 'items_json', 'JSON NULL');
        $addIfMissing('quotations', 'terms_json', 'JSON NULL');
        $addIfMissing('quotations', 'notes', 'TEXT NULL');
        $addIfMissing('quotations', 'extra_charge', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
        $addIfMissing('quotations', 'overall_discount', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
        $addIfMissing('quotations', 'bank_account_id', 'INT NULL');
        $addIfMissing('quotations', 'status', "VARCHAR(20) NOT NULL DEFAULT 'Open'");
        $addIfMissing('quotations', 'attachment_path', 'VARCHAR(255) NULL');
        $addIfMissing('quotations', 'received_amount', 'DECIMAL(12,2) NOT NULL DEFAULT 0');
        $addIfMissing('quotations', 'shipping_address', 'TEXT NULL');
        $addIfMissing('quotations', 'overall_gst_pct', 'DECIMAL(6,2) NOT NULL DEFAULT 0');
        $addIfMissing('quotations', 'owner_id', 'INT NULL');
        $addIfMissing('quotations', 'created_by_user_id', 'INT NULL');
        try { $pdo->exec("ALTER TABLE quotations ADD INDEX idx_quotations_owner_id (owner_id)"); } catch (Throwable $e) {}

        // Ensure the ENUM 'type' supports Invoice and Retail as well (used by Invoices module)
        try {
            $pdo->exec("ALTER TABLE `quotations` MODIFY COLUMN `type` ENUM('Quotation','Proforma','Invoice','Retail') NOT NULL DEFAULT 'Quotation'");
        } catch (Throwable $e) { /* ignore if already modified */ }
    }

    public function seedDemo(): void {
        $pdo = $this->db->getConnection();
        $count = (int) $pdo->query("SELECT COUNT(*) FROM quotations")->fetchColumn();
        if ($count > 0) return;

        $rows = [
            [1463,'Modulus Infosolvices Pvt. Ltd.',13490.34,'2025-08-03','2025-08-01','Ashish Jha','Quotation','Ashish Jha','-'],
            [1464,'O general',40592,'2025-08-04','2025-08-04','Ashish Jha','Quotation','Ashish Jha','-'],
            [1465,'Pawan narang',56975,'2025-08-04','2025-08-04','Ashish Jha','Quotation','Ashish Jha','-'],
        ];
        $stmt = $pdo->prepare("INSERT INTO quotations
            (quote_no, customer, amount, valid_till, issued_on, issued_by, type, executive, response)
            VALUES (?,?,?,?,?,?,?,?,?)");
        foreach ($rows as $r) { $stmt->execute($r); }
    }

    public function getStats(array $filters): array {
        $pdo = $this->db->getConnection();
        [$where,$params] = $this->buildWhere($filters);
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS total, COUNT(*) AS cnt FROM quotations $where");
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'cnt'=>0];
        return [
            'count' => (int)$row['cnt'],
            'total' => (float)$row['total'],
        ];
    }

    public function getAllFiltered(array $filters): array {
        $pdo = $this->db->getConnection();
        [$where,$params] = $this->buildWhere($filters);
        $sql = "SELECT * FROM quotations $where ORDER BY quote_no DESC LIMIT 200";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNextQuoteNo(): int {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $st = $pdo->prepare("SELECT COALESCE(MAX(quote_no),1470) FROM quotations WHERE owner_id = ?");
        $st->execute([$ownerId]);
        $n = (int)$st->fetchColumn();
        return $n + 1;
    }

    public function delete(int $id): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $pdo->prepare("SELECT attachment_path FROM quotations WHERE owner_id = ? AND id = ?");
        $stmt->execute([$ownerId, $id]);
        $path = (string)($stmt->fetchColumn() ?: '');

        $del = $pdo->prepare("DELETE FROM quotations WHERE owner_id = ? AND id = ?");
        $ok = $del->execute([$ownerId, $id]);

        if ($ok && $path) {
            $publicDir = realpath(__DIR__ . '/../../public');
            // ensure path begins with a slash without using PHP 8's str_starts_with
            if ($publicDir && strpos($path, '/') === 0) {
                $full = $publicDir . $path;
                if (is_file($full)) { @unlink($full); }
            }
        }
        return (bool)$ok;
    }

    public function create(array $data): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        $sql = "INSERT INTO quotations
            (owner_id, created_by_user_id, quote_no, customer, reference, contact_person, party_address, shipping_address, amount, valid_till, issued_on, issued_by, type, executive, response,
             items_json, terms_json, notes, extra_charge, overall_discount, overall_gst_pct, bank_account_id, attachment_path, status, received_amount)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $ownerId,
            $userId,
            $data['quote_no'],
            $data['customer'],
            $data['reference'] ?? null,
            $data['contact_person'] ?? null,
            $data['party_address'] ?? null,
            $data['shipping_address'] ?? null,
            $data['amount'],
            $data['valid_till'],
            $data['issued_on'],
            $data['issued_by'],
            $data['type'],
            $data['executive'],
            $data['response'] ?? '',
            $data['items_json'] ?? '[]',
            $data['terms_json'] ?? '[]',
            $data['notes'] ?? '',
            $data['extra_charge'] ?? 0,
            $data['overall_discount'] ?? 0,
            $data['overall_gst_pct'] ?? 0,
            $data['bank_account_id'] ?? null,
            $data['attachment_path'] ?? null,
            $data['status'] ?? 'Open',
            $data['received_amount'] ?? 0,
        ]);
    }

    public function convertToInvoice(int $id): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $sql = "UPDATE quotations SET type = 'Invoice', status = 'Converted' WHERE owner_id = ? AND id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$ownerId, $id]);
    }

    public function updateStatus(int $id, string $status): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $pdo->prepare("UPDATE quotations SET status = ? WHERE owner_id = ? AND id = ?");
        return $stmt->execute([$status, $ownerId, $id]);
    }

    public function findById(int $id): ?array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $pdo->prepare("SELECT * FROM quotations WHERE owner_id = ? AND id = ? LIMIT 1");
        $stmt->execute([$ownerId, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function update(int $id, array $data): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $sql = "UPDATE quotations SET
            quote_no = ?, customer = ?, reference = ?, contact_person = ?, party_address = ?, shipping_address = ?, amount = ?, valid_till = ?, issued_on = ?, issued_by = ?,
            type = ?, executive = ?, response = ?, items_json = ?, terms_json = ?, notes = ?,
            extra_charge = ?, overall_discount = ?, overall_gst_pct = ?, bank_account_id = ?, attachment_path = ?
            WHERE owner_id = ? AND id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['quote_no'],
            $data['customer'],
            $data['reference'] ?? null,
            $data['contact_person'] ?? null,
            $data['party_address'] ?? null,
            $data['shipping_address'] ?? null,
            $data['amount'],
            $data['valid_till'],
            $data['issued_on'],
            $data['issued_by'],
            $data['type'],
            $data['executive'],
            $data['response'] ?? '',
            $data['items_json'] ?? '[]',
            $data['terms_json'] ?? '[]',
            $data['notes'] ?? '',
            $data['extra_charge'] ?? 0,
            $data['overall_discount'] ?? 0,
            $data['overall_gst_pct'] ?? 0,
            $data['bank_account_id'] ?? null,
            $data['attachment_path'] ?? null,
            $ownerId,
            $id,
        ]);
    }

    private function buildWhere(array $filters): array {
        $clauses = [];
        $params = [];
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $clauses[] = 'owner_id = ?';
        $params[] = $ownerId;

        if (!empty($filters['tab'])) {
            if ($filters['tab'] === 'all') {
                // Show only quotation-related types in Quotations module
                $clauses[] = "type IN ('Quotation','Proforma')";
            } else {
                $clauses[] = 'type = ?';
                $params[] = ($filters['tab'] === 'proforma') ? 'Proforma' : 'Quotation';
            }
        }

        if (!empty($filters['period']) && $filters['period'] !== 'all') {
            switch ($filters['period']) {
                case 'today':
                    $clauses[] = 'issued_on = CURDATE()';
                    break;
                case 'this_week':
                    $clauses[] = 'YEARWEEK(issued_on,1) = YEARWEEK(CURDATE(),1)';
                    break;
                case 'this_month':
                default:
                    $clauses[] = 'DATE_FORMAT(issued_on, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
                    break;
            }
        }

        if (!empty($filters['search'])) {
            $clauses[] = '(customer LIKE ? OR quote_no LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $where = count($clauses) ? ('WHERE ' . implode(' AND ', $clauses)) : '';
        return [$where,$params];
    }
}
?>

