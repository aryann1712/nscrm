<?php
require_once __DIR__ . '/../config/database.php';

class InventoryCategory {
    /** @var Database */
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->ensureTables();
    }

    public function updateCategory(int $id, string $name): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $name = trim($name);
        if ($id <= 0 || $name === '') { throw new Exception('Invalid input'); }
        $stmt = $pdo->prepare("UPDATE inventory_categories SET name = ? WHERE id = ? AND owner_id = ?");
        return (bool)$stmt->execute([$name, $id, $ownerId]);
    }

    public function deleteCategory(int $id): bool {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM inventory_categories WHERE id = ? AND owner_id = ?");
        return (bool)$stmt->execute([$id, $ownerId]);
    }

    public function ensureTables(): void {
        $pdo = $this->db->getConnection();
        // Categories table with tenant columns
        $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            created_by_user_id INT NULL,
            name VARCHAR(190) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_inv_cat_owner_name (owner_id, name),
            KEY idx_inv_cat_owner (owner_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Try to upgrade existing table
        try { $pdo->exec("ALTER TABLE inventory_categories ADD COLUMN owner_id INT NOT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_categories ADD COLUMN created_by_user_id INT NULL"); } catch (\Throwable $e) {}
        // Drop old global unique on name if exists, then add per-tenant unique
        try { $pdo->exec("ALTER TABLE inventory_categories DROP INDEX name"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_categories DROP INDEX uniq_inv_cat_owner_name"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_categories ADD UNIQUE KEY uniq_inv_cat_owner_name (owner_id, name)"); } catch (\Throwable $e) {}

        // Sub-categories table with tenant columns
        $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_sub_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            created_by_user_id INT NULL,
            category_id INT NOT NULL,
            name VARCHAR(190) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_inv_sub_owner_cat_name (owner_id, category_id, name),
            KEY idx_inv_sub_owner (owner_id),
            KEY idx_inv_sub_cat (category_id),
            CONSTRAINT fk_inv_sub_cat FOREIGN KEY (category_id) REFERENCES inventory_categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        try { $pdo->exec("ALTER TABLE inventory_sub_categories ADD COLUMN owner_id INT NOT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_sub_categories ADD COLUMN created_by_user_id INT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_sub_categories DROP INDEX uniq_cat_sub"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_sub_categories DROP INDEX uniq_inv_sub_owner_cat_name"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE inventory_sub_categories ADD UNIQUE KEY uniq_inv_sub_owner_cat_name (owner_id, category_id, name)"); } catch (\Throwable $e) {}
    }

    public function getAllCategories(): array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT id, name FROM inventory_categories WHERE owner_id = ? ORDER BY name");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getCategoryByName(string $name): ?array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT id, name FROM inventory_categories WHERE owner_id = ? AND name = ? LIMIT 1");
        $stmt->execute([$ownerId, $name]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function getCategoryById(int $id): ?array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT id, name FROM inventory_categories WHERE id = ? AND owner_id = ? LIMIT 1");
        $stmt->execute([$id, $ownerId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function createCategory(string $name): int {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        $pdo = $this->db->getConnection();
        $name = trim($name);
        if ($name === '') { throw new Exception('Category name required'); }
        // If exists, return id
        $existing = $this->getCategoryByName($name);
        if ($existing) { return (int)$existing['id']; }
        $stmt = $pdo->prepare("INSERT INTO inventory_categories (owner_id, created_by_user_id, name) VALUES (?, ?, ?)");
        $stmt->execute([$ownerId, $userId, $name]);
        return (int)$pdo->lastInsertId();
    }

    public function getSubCategoriesByCategoryId(int $categoryId): array {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { throw new Exception('Owner context missing'); }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT id, name FROM inventory_sub_categories WHERE owner_id = ? AND category_id = ? ORDER BY name");
        $stmt->execute([$ownerId, $categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getSubCategoryNamesByCategoryName(string $categoryName): array {
        $cat = $this->getCategoryByName($categoryName);
        if (!$cat) return [];
        $list = $this->getSubCategoriesByCategoryId((int)$cat['id']);
        return array_map(function ($r) {
            return $r['name'];
        }, $list);
    }

    public function createSubCategory(int $categoryId, string $name): int {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) { throw new Exception('User not authenticated'); }
        $pdo = $this->db->getConnection();
        $name = trim($name);
        if ($categoryId <= 0) { throw new Exception('category_id required'); }
        if ($name === '') { throw new Exception('Sub-category name required'); }
        // If exists, return id
        $stmt = $pdo->prepare("SELECT id FROM inventory_sub_categories WHERE owner_id = ? AND category_id = ? AND name = ? LIMIT 1");
        $stmt->execute([$ownerId, $categoryId, $name]);
        $id = (int)($stmt->fetchColumn() ?: 0);
        if ($id > 0) return $id;
        $ins = $pdo->prepare("INSERT INTO inventory_sub_categories (owner_id, created_by_user_id, category_id, name) VALUES (?, ?, ?, ?)");
        $ins->execute([$ownerId, $userId, $categoryId, $name]);
        return (int)$pdo->lastInsertId();
    }

    // Optional: seed from existing inventory distinct values
    public function seedFromInventoryIfEmpty(): void {
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        if ($ownerId <= 0) { return; }
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM inventory_categories WHERE owner_id = ?");
        $stmt->execute([$ownerId]);
        $count = (int)$stmt->fetchColumn();
        if ($count > 0) return;
        // categories for this owner's inventory
        $catsStmt = $pdo->prepare("SELECT DISTINCT category FROM inventory WHERE owner_id = ? AND category IS NOT NULL AND category <> '' ORDER BY category");
        $catsStmt->execute([$ownerId]);
        $cats = $catsStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        foreach ($cats as $c) {
            $cid = $this->createCategory($c);
            // subcategories inferred for this owner
            $stmt = $pdo->prepare("SELECT DISTINCT sub_category FROM inventory WHERE owner_id = ? AND category = ? AND sub_category IS NOT NULL AND sub_category <> '' ORDER BY sub_category");
            $stmt->execute([$ownerId, $c]);
            $subs = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            foreach ($subs as $s) { $this->createSubCategory($cid, $s); }
        }
    }

    // Return categories with nested sub-categories: [ {id, name, subs: [{id,name}]} ]
    public function listCategoriesWithSubs(): array {
        $pdo = $this->db->getConnection();
        $cats = $this->getAllCategories();
        $out = [];
        foreach ($cats as $c) {
            $subs = $this->getSubCategoriesByCategoryId((int)$c['id']);
            $out[] = [
                'id' => (int)$c['id'],
                'name' => $c['name'],
                'subs' => array_map(function($r){
                    return [ 'id' => (int)$r['id'], 'name' => $r['name'] ];
                }, $subs)
            ];
        }
        return $out;
    }
}
