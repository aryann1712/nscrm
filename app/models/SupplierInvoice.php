<?php
require_once __DIR__ . '/../config/database.php';

class SupplierInvoice {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTable();
    }

    public function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS supplier_invoices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            created_by_user_id INT NOT NULL,
            supplier VARCHAR(200) NOT NULL,
            supplier_id INT NULL,
            contact_person VARCHAR(150) NULL,
            party_address TEXT NULL,
            shipping_address TEXT NULL,
            invoice_no VARCHAR(100) NOT NULL,
            reference VARCHAR(150) NULL,
            invoice_date DATE NOT NULL,
            due_date DATE NULL,
            executive VARCHAR(120) NULL,
            taxable_total DECIMAL(12,2) NOT NULL DEFAULT 0,
            cgst DECIMAL(12,2) NOT NULL DEFAULT 0,
            sgst DECIMAL(12,2) NOT NULL DEFAULT 0,
            total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            items_json MEDIUMTEXT,
            terms_json MEDIUMTEXT,
            notes TEXT NULL,
            extra_charge DECIMAL(12,2) NOT NULL DEFAULT 0,
            overall_discount DECIMAL(12,2) NOT NULL DEFAULT 0,
            bank_account_id INT NULL,
            attachment_path VARCHAR(255) NULL,
            credit_month VARCHAR(20) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX(invoice_no),
            INDEX(supplier),
            INDEX(invoice_date),
            INDEX idx_supplier_invoices_owner_id (owner_id),
            INDEX idx_supplier_invoices_created_by (created_by_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->getConnection()->exec($sql);
        
        // Migration: Add missing columns if they don't exist
        $pdo = $this->db->getConnection();
        try {
            $stmt = $pdo->query("DESCRIBE supplier_invoices");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $alter = [];
            
            if (!in_array('supplier', $columns, true)) {
                $alter[] = "ADD COLUMN supplier VARCHAR(200) NULL AFTER supplier_id";
            }
            if (!in_array('contact_person', $columns, true)) {
                $alter[] = "ADD COLUMN contact_person VARCHAR(150) NULL AFTER supplier";
            }
            if (!in_array('party_address', $columns, true)) {
                $alter[] = "ADD COLUMN party_address TEXT NULL AFTER contact_person";
            }
            if (!in_array('shipping_address', $columns, true)) {
                $alter[] = "ADD COLUMN shipping_address TEXT NULL AFTER party_address";
            }
            if (!in_array('reference', $columns, true)) {
                $alter[] = "ADD COLUMN reference VARCHAR(150) NULL AFTER invoice_no";
            }
            if (!in_array('due_date', $columns, true)) {
                $alter[] = "ADD COLUMN due_date DATE NULL AFTER invoice_date";
            }
            if (!in_array('executive', $columns, true)) {
                $alter[] = "ADD COLUMN executive VARCHAR(120) NULL AFTER due_date";
            }
            if (!in_array('taxable_total', $columns, true)) {
                // Check if taxable_amount exists (old name)
                if (in_array('taxable_amount', $columns, true)) {
                    $alter[] = "CHANGE COLUMN taxable_amount taxable_total DECIMAL(12,2) NOT NULL DEFAULT 0";
                } else {
                    $alter[] = "ADD COLUMN taxable_total DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER executive";
                }
            }
            if (!in_array('cgst', $columns, true)) {
                $alter[] = "ADD COLUMN cgst DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER taxable_total";
            }
            if (!in_array('sgst', $columns, true)) {
                $alter[] = "ADD COLUMN sgst DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER cgst";
            }
            if (!in_array('items_json', $columns, true)) {
                $alter[] = "ADD COLUMN items_json MEDIUMTEXT AFTER total_amount";
            }
            if (!in_array('terms_json', $columns, true)) {
                $alter[] = "ADD COLUMN terms_json MEDIUMTEXT AFTER items_json";
            }
            if (!in_array('notes', $columns, true)) {
                $alter[] = "ADD COLUMN notes TEXT NULL AFTER terms_json";
            }
            if (!in_array('extra_charge', $columns, true)) {
                $alter[] = "ADD COLUMN extra_charge DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER notes";
            }
            if (!in_array('overall_discount', $columns, true)) {
                $alter[] = "ADD COLUMN overall_discount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER extra_charge";
            }
            if (!in_array('bank_account_id', $columns, true)) {
                $alter[] = "ADD COLUMN bank_account_id INT NULL AFTER overall_discount";
            }
            if (!in_array('attachment_path', $columns, true)) {
                $alter[] = "ADD COLUMN attachment_path VARCHAR(255) NULL AFTER bank_account_id";
            }
            if (!in_array('updated_at', $columns, true)) {
                $alter[] = "ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at";
            }
            
            if (!empty($alter)) {
                $pdo->exec("ALTER TABLE supplier_invoices " . implode(", ", $alter));
            }
        } catch (Throwable $e) {
            // Ignore migration errors - table might be in use
        }
    }

    public function getNextInvoiceNo(): int {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $st = $pdo->prepare("SELECT COALESCE(MAX(CAST(invoice_no AS UNSIGNED)), 0) FROM supplier_invoices WHERE owner_id = ? AND invoice_no REGEXP '^[0-9]+$'");
        $st->execute([$ownerId]);
        $n = (int)$st->fetchColumn();
        return $n + 1;
    }

    public function getAll(array $filters = []): array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        
        $where = "WHERE si.owner_id = ?";
        $params = [$ownerId];
        
        if (!empty($filters['period']) && $filters['period'] !== 'all') {
            switch ($filters['period']) {
                case 'this_month':
                    $where .= " AND DATE_FORMAT(si.invoice_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
                    break;
                case 'last_month':
                    $where .= " AND DATE_FORMAT(si.invoice_date, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m')";
                    break;
                case 'this_quarter':
                    $where .= " AND QUARTER(si.invoice_date) = QUARTER(CURDATE()) AND YEAR(si.invoice_date) = YEAR(CURDATE())";
                    break;
                case 'this_year':
                    $where .= " AND YEAR(si.invoice_date) = YEAR(CURDATE())";
                    break;
            }
        }
        
        if (!empty($filters['search'])) {
            $where .= " AND (COALESCE(si.supplier, c.company, '') LIKE ? OR si.invoice_no LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        // Check what columns exist
        try {
            $stmt = $pdo->query("DESCRIBE supplier_invoices");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $hasSupplierColumn = in_array('supplier', $columns, true);
            $hasTaxableTotal = in_array('taxable_total', $columns, true);
            $hasTaxableAmount = in_array('taxable_amount', $columns, true);
            $hasContactPerson = in_array('contact_person', $columns, true);
        } catch (Throwable $e) {
            $hasSupplierColumn = false;
            $hasTaxableTotal = false;
            $hasTaxableAmount = false;
            $hasContactPerson = false;
        }
        
        // Determine taxable column to use
        $taxableColumn = '0';
        if ($hasTaxableTotal) {
            $taxableColumn = 'si.taxable_total';
        } elseif ($hasTaxableAmount) {
            $taxableColumn = 'si.taxable_amount';
        }
        
        // Determine contact column to use
        $contactColumn = $hasContactPerson ? 'si.contact_person' : 'si.contact_name';
        
        if ($hasSupplierColumn) {
            $sql = "SELECT 
                        COALESCE(si.supplier, '') AS supplier,
                        $contactColumn AS contact,
                        si.invoice_no,
                        DATE_FORMAT(si.invoice_date, '%Y-%m-%d') AS invoice_date,
                        $taxableColumn AS taxable,
                        si.total_amount AS amount,
                        si.credit_month
                    FROM supplier_invoices si
                    LEFT JOIN customers c ON si.supplier_id = c.id
                    $where 
                    ORDER BY si.invoice_date DESC, si.invoice_no DESC 
                    LIMIT 200";
        } else {
            // Fallback: use supplier_id and join with customers table
            $sql = "SELECT 
                        COALESCE(c.company, 'Unknown Supplier') AS supplier,
                        $contactColumn AS contact,
                        si.invoice_no,
                        DATE_FORMAT(si.invoice_date, '%Y-%m-%d') AS invoice_date,
                        $taxableColumn AS taxable,
                        si.total_amount AS amount,
                        si.credit_month
                    FROM supplier_invoices si
                    LEFT JOIN customers c ON si.supplier_id = c.id AND c.type = 'supplier'
                    $where 
                    ORDER BY si.invoice_date DESC, si.invoice_no DESC 
                    LIMIT 200";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        
        // Calculate CGST and SGST from GST (assuming split equally)
        $gst = (float)($data['gst_total'] ?? 0);
        $cgst = $gst / 2;
        $sgst = $gst / 2;
        
        $sql = "INSERT INTO supplier_invoices
            (owner_id, created_by_user_id, supplier, supplier_id, contact_person, party_address, shipping_address, 
             invoice_no, reference, invoice_date, due_date, executive,
             taxable_total, cgst, sgst, total_amount, items_json, terms_json, notes, 
             extra_charge, overall_discount, bank_account_id, attachment_path, credit_month)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $ownerId,
            $userId,
            $data['supplier'],
            $data['supplier_id'] ?? null,
            $data['contact_person'] ?? null,
            $data['party_address'] ?? null,
            $data['shipping_address'] ?? null,
            $data['invoice_no'],
            $data['reference'] ?? null,
            $data['invoice_date'],
            $data['due_date'] ?? null,
            $data['executive'] ?? null,
            $data['taxable_total'],
            $cgst,
            $sgst,
            $data['total_amount'],
            $data['items_json'] ?? '[]',
            $data['terms_json'] ?? '[]',
            $data['notes'] ?? '',
            $data['extra_charge'] ?? 0,
            $data['overall_discount'] ?? 0,
            $data['bank_account_id'] ?? null,
            $data['attachment_path'] ?? null,
            $data['credit_month'] ?? null,
        ]);
    }

    public function findById(int $id): ?array {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $pdo->prepare("SELECT * FROM supplier_invoices WHERE owner_id = ? AND id = ? LIMIT 1");
        $stmt->execute([$ownerId, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function delete(int $id): bool {
        $pdo = $this->db->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $stmt = $pdo->prepare("DELETE FROM supplier_invoices WHERE owner_id = ? AND id = ?");
        return $stmt->execute([$ownerId, $id]);
    }
}
?>

