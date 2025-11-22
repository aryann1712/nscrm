<?php
require_once __DIR__ . '/../config/database.php';

class Inventory {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
        $this->ensureTenantColumns();
    }

    private function ensureTenantColumns(): void {
        $pdo = $this->db->getConnection();
        try { $pdo->exec("ALTER TABLE inventory ADD COLUMN owner_id INT NULL AFTER id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory ADD COLUMN created_by_user_id INT NULL AFTER owner_id"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory ADD INDEX idx_inventory_owner_id (owner_id)"); } catch (Throwable $e) {}
    }
    
    public function getAll() {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "SELECT *, COALESCE(active, 1) as active FROM inventory WHERE owner_id = ? ORDER BY id DESC";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$ownerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching inventory: " . $e->getMessage());
        }
    }

    public function getAllFiltered($filters = []) {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "SELECT *, COALESCE(active, 1) as active FROM inventory WHERE owner_id = ?";
            $params = [$ownerId];
            
            // Category filter
            if (!empty($filters['category'])) {
                $sql .= " AND category = ?";
                $params[] = $filters['category'];
            }
            
            // Sub-category filter
            if (!empty($filters['sub_category'])) {
                $sql .= " AND sub_category = ?";
                $params[] = $filters['sub_category'];
            }
            
            // Stock filter
            if (!empty($filters['stock'])) {
                switch ($filters['stock']) {
                    case 'in_stock':
                        $sql .= " AND quantity > 0";
                        break;
                    case 'out_of_stock':
                        $sql .= " AND quantity <= 0";
                        break;
                    case 'low_stock':
                        $sql .= " AND quantity <= min_stock AND quantity > 0";
                        break;
                }
            }
            
            // Importance filter
            if (!empty($filters['importance'])) {
                $sql .= " AND importance = ?";
                $params[] = $filters['importance'];
            }
            
            // Status filter
            if ($filters['status'] !== '' && $filters['status'] !== null) {
                $sql .= " AND COALESCE(active, 1) = ?";
                $params[] = $filters['status'];
            }
            
            // Tag search filter
            if (!empty($filters['tag_search'])) {
                $sql .= " AND (tags LIKE ? OR name LIKE ? OR code LIKE ?)";
                $searchTerm = '%' . $filters['tag_search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Item type filter
            if (!empty($filters['item_type'])) {
                $sql .= " AND item_type = ?";
                $params[] = $filters['item_type'];
            }
            
            $sql .= " ORDER BY id DESC";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching filtered inventory: " . $e->getMessage());
        }
    }
    
    public function get($id) {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "SELECT *, COALESCE(active, 1) as active FROM inventory WHERE owner_id = ? AND id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$ownerId, $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching inventory item: " . $e->getMessage());
        }
    }
    
    public function create($data) {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $userId  = (int)($_SESSION['user']['id'] ?? 0);
            if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
            $sql = "INSERT INTO inventory (owner_id, created_by_user_id, name, code, importance, category, sub_category, batch, quantity, unit, store, item_type, internal_manufacturing, purchase, rate, value, std_cost, purch_cost, std_sale_price, hsn_sac, gst, description, internal_notes, min_stock, lead_time, tags, active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([
                $ownerId,
                $userId,
                $data['name'],
                $data['code'],
                $data['importance'],
                $data['category'],
                $data['sub_category'],
                $data['batch'] ?? 'No',
                $data['quantity'],
                $data['unit'] ?? 'no.s',
                $data['store'],
                $data['item_type'] ?? 'products',
                isset($data['internal_manufacturing']) ? 1 : 0,
                isset($data['purchase']) ? 1 : 0,
                $data['rate'] ?? 0,
                $data['value'] ?? 0,
                $data['std_cost'] ?? 0,
                $data['purch_cost'] ?? 0,
                $data['std_sale_price'] ?? 0,
                $data['hsn_sac'] ?? '',
                $data['gst'] ?? 0,
                $data['description'] ?? '',
                $data['internal_notes'] ?? '',
                $data['min_stock'] ?? 0,
                $data['lead_time'] ?? 0,
                $data['tags'] ?? '',
                $data['active'] ?? 1
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error creating inventory item: " . $e->getMessage());
        }
    }
    
    public function update($id, $data) {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "UPDATE inventory SET name = ?, code = ?, importance = ?, category = ?, sub_category = ?, batch = ?, quantity = ?, unit = ?, store = ?, item_type = ?, internal_manufacturing = ?, purchase = ?, rate = ?, value = ?, std_cost = ?, purch_cost = ?, std_sale_price = ?, hsn_sac = ?, gst = ?, description = ?, internal_notes = ?, min_stock = ?, lead_time = ?, tags = ?, active = ?, updated_at = NOW() WHERE owner_id = ? AND id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([
                $data['name'],
                $data['code'],
                $data['importance'],
                $data['category'],
                $data['sub_category'],
                $data['batch'] ?? 'No',
                $data['quantity'],
                $data['unit'] ?? 'no.s',
                $data['store'],
                $data['item_type'] ?? 'products',
                isset($data['internal_manufacturing']) ? 1 : 0,
                isset($data['purchase']) ? 1 : 0,
                $data['rate'] ?? 0,
                $data['value'] ?? 0,
                $data['std_cost'] ?? 0,
                $data['purch_cost'] ?? 0,
                $data['std_sale_price'] ?? 0,
                $data['hsn_sac'] ?? '',
                $data['gst'] ?? 0,
                $data['description'] ?? '',
                $data['internal_notes'] ?? '',
                $data['min_stock'] ?? 0,
                $data['lead_time'] ?? 0,
                $data['tags'] ?? '',
                $data['active'] ?? 1,
                $ownerId,
                $id
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error updating inventory item: " . $e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "DELETE FROM inventory WHERE owner_id = ? AND id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([$ownerId, $id]);
        } catch (PDOException $e) {
            throw new Exception("Error deleting inventory item: " . $e->getMessage());
        }
    }
    
    public function getCategories() {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "SELECT DISTINCT category FROM inventory WHERE owner_id = ? ORDER BY category";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$ownerId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            throw new Exception("Error fetching categories: " . $e->getMessage());
        }
    }
    
    public function getSubCategories() {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "SELECT DISTINCT sub_category FROM inventory WHERE owner_id = ? ORDER BY sub_category";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$ownerId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            throw new Exception("Error fetching sub categories: " . $e->getMessage());
        }
    }

    // New: fetch sub-categories for a given category (dependent dropdown)
    public function getSubCategoriesByCategory(string $category) {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "SELECT DISTINCT sub_category FROM inventory WHERE owner_id = ? AND category = ? AND sub_category IS NOT NULL AND sub_category <> '' ORDER BY sub_category";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$ownerId, $category]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            throw new Exception("Error fetching sub categories by category: " . $e->getMessage());
        }
    }
    
    public function getImportanceLevels() {
        return ['Low', 'Normal', 'High'];
    }
    
    public function getBatchOptions() {
        return ['No', 'Yes', 'Yes, Expirable'];
    }
    
    public function getTotalItems() {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "SELECT COUNT(*) as total FROM inventory WHERE owner_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$ownerId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            throw new Exception("Error counting inventory items: " . $e->getMessage());
        }
    }
    
    public function getTotalValue() {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "SELECT SUM(value) as total FROM inventory WHERE owner_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$ownerId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            throw new Exception("Error calculating total value: " . $e->getMessage());
        }
    }

    public function toggleStatus($id, $status) {
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
            $sql = "UPDATE inventory SET active = ?, updated_at = NOW() WHERE owner_id = ? AND id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([$status, $ownerId, $id]);
        } catch (PDOException $e) {
            throw new Exception("Error updating inventory status: " . $e->getMessage());
        }
    }
}
?> 