<?php
require_once __DIR__ . '/../config/database.php';

class Lead {
    private $db;
    private $table_name = "leads";

    public function __construct() {
        $this->db = new Database();
    }

    // Update lead including all extended fields from Add Lead form
    public function updateWithExtras($id, array $data): bool {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "UPDATE " . $this->table_name . " SET 
                business_name = ?, contact_person = ?, contact_email = ?, contact_phone = ?, 
                source = ?, stage = ?, assigned_to = ?, assigned_to_user_id = ?,
                requirements = ?, notes = ?, potential_value = ?, last_contact = ?, next_followup = ?, is_starred = ?,
                salutation = ?, first_name = ?, last_name = ?, designation = ?, website = ?,
                address_line1 = ?, address_line2 = ?, country = ?, city = ?, state = ?,
                gstin = ?, code = ?, since = ?, category = ?, product = ?, tags = ?
                WHERE owner_id = ? AND id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([
                $data['business_name'],
                $data['contact_person'],
                $data['contact_email'],
                $data['contact_phone'],
                $data['source'],
                $data['stage'],
                $data['assigned_to'],
                $data['assigned_to_user_id'] ?? null,
                $data['requirements'],
                $data['notes'],
                $data['potential_value'],
                $data['last_contact'],
                $data['next_followup'],
                $data['is_starred'],
                $data['salutation'] ?? null,
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['designation'] ?? null,
                $data['website'] ?? null,
                $data['address_line1'] ?? null,
                $data['address_line2'] ?? null,
                $data['country'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['gstin'] ?? null,
                $data['code'] ?? null,
                $data['since'] ?? null,
                $data['category'] ?? null,
                $data['product'] ?? null,
                $data['tags'] ?? null,
                $ownerId,
                $id
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error updating lead (extras): " . $e->getMessage());
        }
    }

    public function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            business_name VARCHAR(255) NOT NULL,
            contact_person VARCHAR(255) NOT NULL,
            contact_email VARCHAR(255) NULL,
            contact_phone VARCHAR(50) NULL,
            source VARCHAR(100) NULL,
            stage VARCHAR(50) NULL,
            assigned_to VARCHAR(100) NULL,
            requirements TEXT NULL,
            notes TEXT NULL,
            potential_value DECIMAL(12,2) NOT NULL DEFAULT 0,
            last_contact DATE NULL,
            next_followup DATE NULL,
            is_starred TINYINT(1) NOT NULL DEFAULT 0,
            salutation VARCHAR(10) NULL,
            first_name VARCHAR(100) NULL,
            last_name VARCHAR(100) NULL,
            designation VARCHAR(100) NULL,
            website VARCHAR(255) NULL,
            address_line1 VARCHAR(255) NULL,
            address_line2 VARCHAR(255) NULL,
            country VARCHAR(100) NULL,
            city VARCHAR(100) NULL,
            state VARCHAR(100) NULL,
            gstin VARCHAR(50) NULL,
            code VARCHAR(50) NULL,
            since DATE NULL,
            category VARCHAR(100) NULL,
            product VARCHAR(255) NULL,
            tags VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (stage),
            INDEX (assigned_to),
            INDEX (source),
            INDEX (is_starred),
            INDEX (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->getConnection()->exec($sql);
        
        // Add missing columns if they don't exist
        $this->addMissingColumns();
    }
    
    private function addMissingColumns(): void {
        $pdo = $this->db->getConnection();
        $columns = [
            'salutation' => 'VARCHAR(10) NULL',
            'first_name' => 'VARCHAR(100) NULL',
            'last_name' => 'VARCHAR(100) NULL',
            'designation' => 'VARCHAR(100) NULL',
            'website' => 'VARCHAR(255) NULL',
            'address_line1' => 'VARCHAR(255) NULL',
            'address_line2' => 'VARCHAR(255) NULL',
            'country' => 'VARCHAR(100) NULL',
            'city' => 'VARCHAR(100) NULL',
            'state' => 'VARCHAR(100) NULL',
            'gstin' => 'VARCHAR(50) NULL',
            'code' => 'VARCHAR(50) NULL',
            'since' => 'DATE NULL',
            'category' => 'VARCHAR(100) NULL',
            'product' => 'VARCHAR(255) NULL',
            'tags' => 'VARCHAR(255) NULL',
            'owner_id' => 'INT NULL',
            'created_by_user_id' => 'INT NULL',
            'assigned_to_user_id' => 'INT NULL'
        ];
        
        foreach ($columns as $column => $definition) {
            try {
                $pdo->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN $column $definition");
            } catch (PDOException $e) {
                // Column already exists, ignore error
            }
        }
    }

    public function getAllFiltered(array $filters = []): array {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "SELECT * FROM " . $this->table_name . " WHERE owner_id = ?";
            $params = [$ownerId];

            // Stage filter
            if (!empty($filters['stage']) && $filters['stage'] !== 'all') {
                if ($filters['stage'] === 'active') {
                    $sql .= " AND stage NOT IN ('Decided', 'Inactive')";
                } else {
                    $sql .= " AND stage = ?";
                    $params[] = $filters['stage'];
                }
            }

            // Search filter
            if (!empty($filters['search'])) {
                $sql .= " AND (business_name LIKE ? OR contact_person LIKE ? OR requirements LIKE ? OR notes LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            // Assigned to filter
            if (!empty($filters['assigned_to'])) {
                $sql .= " AND assigned_to = ?";
                $params[] = $filters['assigned_to'];
            }

            // Source filter
            if (!empty($filters['source'])) {
                $sql .= " AND source = ?";
                $params[] = $filters['source'];
            }

            // View/Order filter
            switch ($filters['view'] ?? 'newest') {
                case 'oldest':
                    $sql .= " ORDER BY created_at ASC";
                    break;
                case 'starred':
                    $sql .= " ORDER BY is_starred DESC, created_at DESC";
                    break;
                case 'appointments':
                    $sql .= " ORDER BY next_followup ASC";
                    break;
                default:
                    $sql .= " ORDER BY created_at DESC";
                    break;
            }

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching filtered leads: " . $e->getMessage());
        }
    }

    public function getStats(): array {
        try {
            $pdo = $this->db->getConnection();
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $st1 = $pdo->prepare("SELECT COUNT(*) FROM " . $this->table_name . " WHERE owner_id = ?");
            $st1->execute([$ownerId]);
            $totalCount = $st1->fetchColumn();
            $st2 = $pdo->prepare("SELECT SUM(potential_value) FROM " . $this->table_name . " WHERE owner_id = ?");
            $st2->execute([$ownerId]);
            $totalValue = $st2->fetchColumn();
            
            return [
                'count' => (int)$totalCount,
                'potential_value' => (float)$totalValue
            ];
        } catch (PDOException $e) {
            throw new Exception("Error fetching stats: " . $e->getMessage());
        }
    }

    public function get($id): ?array {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $stmt = $this->db->getConnection()->prepare("SELECT * FROM " . $this->table_name . " WHERE owner_id = ? AND id = ?");
            $stmt->execute([$ownerId, $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            throw new Exception("Error fetching lead: " . $e->getMessage());
        }
    }

    public function create(array $data): bool {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $userId  = (int)($_SESSION['user']['id'] ?? 0);
            if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
            $sql = "INSERT INTO " . $this->table_name . " 
                (owner_id, created_by_user_id, assigned_to_user_id, business_name, contact_person, contact_email, contact_phone, source, stage, 
                 assigned_to, requirements, notes, potential_value, last_contact, next_followup, is_starred,
                 salutation, first_name, last_name, designation, website, address_line1, address_line2,
                 country, city, state, gstin, code, since, category, product, tags) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([
                $ownerId,
                $userId,
                ($data['assigned_to_user_id'] ?? null),
                $data['business_name'],
                $data['contact_person'],
                $data['contact_email'] ?? null,
                $data['contact_phone'] ?? null,
                $data['source'] ?? null,
                $data['stage'] ?? null,
                $data['assigned_to'] ?? null,
                $data['requirements'] ?? null,
                $data['notes'] ?? null,
                $data['potential_value'] ?? 0,
                $data['last_contact'] ?? null,
                $data['next_followup'] ?? null,
                $data['is_starred'] ?? 0,
                $data['salutation'] ?? null,
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['designation'] ?? null,
                $data['website'] ?? null,
                $data['address_line1'] ?? null,
                $data['address_line2'] ?? null,
                $data['country'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['gstin'] ?? null,
                $data['code'] ?? null,
                $data['since'] ?? null,
                $data['category'] ?? null,
                $data['product'] ?? null,
                $data['tags'] ?? null
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error creating lead: " . $e->getMessage());
        }
    }

    public function update($id, array $data): bool {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "UPDATE " . $this->table_name . " SET 
                business_name = ?, contact_person = ?, contact_email = ?, contact_phone = ?, 
                source = ?, stage = ?, assigned_to = ?, requirements = ?, notes = ?, 
                potential_value = ?, last_contact = ?, next_followup = ?, is_starred = ? 
                WHERE owner_id = ? AND id = ?";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([
                $data['business_name'],
                $data['contact_person'],
                $data['contact_email'],
                $data['contact_phone'],
                $data['source'],
                $data['stage'],
                $data['assigned_to'],
                $data['requirements'],
                $data['notes'],
                $data['potential_value'],
                $data['last_contact'],
                $data['next_followup'],
                $data['is_starred'],
                $ownerId,
                $id
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error updating lead: " . $e->getMessage());
        }
    }

    public function delete($id): bool {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $stmt = $this->db->getConnection()->prepare("DELETE FROM " . $this->table_name . " WHERE owner_id = ? AND id = ?");
            return $stmt->execute([$ownerId, $id]);
        } catch (PDOException $e) {
            throw new Exception("Error deleting lead: " . $e->getMessage());
        }
    }

    // Delete all leads and reset the auto-increment counter
    public function deleteAll(): void {
        $pdo = $this->db->getConnection();
        $pdo->exec("DELETE FROM " . $this->table_name);
        try { $pdo->exec("ALTER TABLE " . $this->table_name . " AUTO_INCREMENT = 1"); } catch (PDOException $e) { /* ignore if not supported */ }
    }

    public function toggleStar($id): bool {
        try {
            $sql = "UPDATE " . $this->table_name . " SET is_starred = NOT is_starred WHERE id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new Exception("Error toggling star: " . $e->getMessage());
        }
    }

    public function seedDemoData(): void {
        $demoData = [
            ['THESHUBHAM HOTELS', 'Mr. SHUBHAM', 'shubham@hotels.com', '9876543210', 'REFF LINK', '', 'Ashish Jha', 'INTERCOM', '', 50000, null, null, 0],
            ['Society', 'Mr. Imran ji', 'imran@society.com', '9876543211', 'REFF LINK', 'Proposal', 'Ashish Jha', '8 dome 2 bullet', '', 75000, '2025-08-14', '2025-08-20', 0],
            ['CJ Online Pvt Ltd', 'Mr. Rajesh Jee', 'rajesh@cjonline.com', '9876543212', 'Anand Gupta Reference', 'Proposal', 'Ashish Jha', '4 Analog, 1 ip and 1 wifi camera', '', 120000, '2025-08-11', '2025-08-18', 1],
            ['Kimirica Lifestyle Pvt Ltd', 'Mr. Prateek', 'prateek@kimirica.com', '9876543213', 'REFF LINK', 'Proposal', 'Naveen Kumar', 'Head Counting System', '', 85000, '2025-08-07', '2025-08-15', 0],
            ['Auritas Technologies India Pvt. Ltd', 'Mr. Auritas', 'auritas@tech.com', '9876543214', 'Anand Gupta Reference', 'Proposal', 'Ashish Jha', '4 wifi camera with installation', '', 95000, '2025-08-06', '2025-08-16', 0],
            ['Malik traders', 'Mr. Rajender Kumar', 'rajender@malik.com', '9876543215', 'Indiamart', 'Proposal', 'Ashish Jha', '4 MP CCTV FULL SET', '', 65000, '2025-08-05', '2025-08-12', 0],
            ['Pawan narang', 'Mr. Pawan Narang', 'pawan@narang.com', '9876543216', 'REFF LINK', 'Proposal', 'Ashish Jha', '', '', 45000, '2025-08-04', '2025-08-10', 0],
            ['O general', 'Mr. Juber', 'juber@ogeneral.com', '9876543217', 'Indiamart', 'Proposal', 'Ashish Jha', 'FULL SET OF 2MP DOME CCTV', '', 55000, '2025-08-04', '2025-08-11', 0],
            ['Modulus Infosolvices Pvt. Ltd.', 'Mr. Sailaja', 'sailaja@modulus.com', '9876543218', 'Indiamart', 'Proposal', 'Ashish Jha', '5 Unit cctv camera', '', 70000, '2025-08-01', '2025-08-08', 0],
            ['L. S. Enterprise', 'Mr. Rishu', 'rishu@lsenterprise.com', '9876543219', 'Indiamart', 'Proposal', 'Ashish Jha', 'PTZ camera', '', 40000, '2025-07-31', '2025-08-07', 0],
        ];

        $stmt = $this->db->getConnection()->prepare("INSERT IGNORE INTO " . $this->table_name . " 
            (business_name, contact_person, contact_email, contact_phone, source, stage, 
             assigned_to, requirements, notes, potential_value, last_contact, next_followup, is_starred) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($demoData as $row) {
            $stmt->execute($row);
        }
    }

    // Wipes the leads table and seeds exactly 6 sample rows
    public function seedDemoDataExactSix(): void {
        $demoData = [
            ['THESHUBHAM HOTELS', 'Mr. SHUBHAM', 'shubham@hotels.com', '9876543210', 'REFF LINK', '', 'Ashish Jha', 'INTERCOM', '', 50000, null, null, 0],
            ['Society', 'Mr. Imran ji', 'imran@society.com', '9876543211', 'REFF LINK', 'Proposal', 'Ashish Jha', '8 dome 2 bullet', '', 75000, '2025-08-14', '2025-08-20', 0],
            ['CJ Online Pvt Ltd', 'Mr. Rajesh Jee', 'rajesh@cjonline.com', '9876543212', 'Anand Gupta Reference', 'Proposal', 'Ashish Jha', '4 Analog, 1 ip and 1 wifi camera', '', 120000, '2025-08-11', '2025-08-18', 1],
            ['Kimirica Lifestyle Pvt Ltd', 'Mr. Prateek', 'prateek@kimirica.com', '9876543213', 'REFF LINK', 'Proposal', 'Naveen Kumar', 'Head Counting System', '', 85000, '2025-08-07', '2025-08-15', 0],
            ['Auritas Technologies India Pvt. Ltd', 'Mr. Auritas', 'auritas@tech.com', '9876543214', 'Anand Gupta Reference', 'Proposal', 'Ashish Jha', '4 wifi camera with installation', '', 95000, '2025-08-06', '2025-08-16', 0],
            ['Malik traders', 'Mr. Rajender Kumar', 'rajender@malik.com', '9876543215', 'Indiamart', 'Proposal', 'Ashish Jha', '4 MP CCTV FULL SET', '', 65000, '2025-08-05', '2025-08-12', 0],
        ];

        $pdo = $this->db->getConnection();
        // Remove all existing data
        $pdo->exec("DELETE FROM " . $this->table_name);

        // Reset auto-increment (optional)
        try { $pdo->exec("ALTER TABLE " . $this->table_name . " AUTO_INCREMENT = 1"); } catch (PDOException $e) { /* ignore */ }

        // Insert exactly 6 rows
        $stmt = $pdo->prepare("INSERT INTO " . $this->table_name . " 
            (business_name, contact_person, contact_email, contact_phone, source, stage, 
             assigned_to, requirements, notes, potential_value, last_contact, next_followup, is_starred) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($demoData as $row) { $stmt->execute($row); }
    }
}
?>
