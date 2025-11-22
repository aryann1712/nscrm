<?php
require_once __DIR__ . '/../models/Inventory.php';

class InventoryController {
    public function index() {
        try {
            $inventory = new Inventory();
            
            // Get filter parameters
            $category = $_GET['category'] ?? '';
            $subCategory = $_GET['sub_category'] ?? '';
            $stock = $_GET['stock'] ?? '';
            $importance = $_GET['importance'] ?? '';
            $status = $_GET['status'] ?? '';
            $tagSearch = $_GET['tag_search'] ?? '';
            $itemType = $_GET['item_type'] ?? '';
            
            // Apply filters
            $items = $inventory->getAllFiltered([
                'category' => $category,
                'sub_category' => $subCategory,
                'stock' => $stock,
                'importance' => $importance,
                'status' => $status,
                'tag_search' => $tagSearch,
                'item_type' => $itemType
            ]);
            
            $totalItems = $inventory->getTotalItems();
            $totalValue = $inventory->getTotalValue();
            // Prefer master categories for filters
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $catModel->ensureTables();
            $catModel->seedFromInventoryIfEmpty();
            $categoriesRows = $catModel->getAllCategories();
            $categories = array_map(function($r) {
                return $r['name'];
            }, $categoriesRows);
            $subCategories = $inventory->getSubCategories();
            $importanceLevels = $inventory->getImportanceLevels();
            
            require __DIR__ . '/../views/inventory/index.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    // Inventory taxonomy management (Categories and Sub-Categories)
    public function taxonomy() {
        try {
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $catModel->ensureTables();
            // Optionally seed from existing inventory on first open
            $catModel->seedFromInventoryIfEmpty();
            require __DIR__ . '/../views/inventory/taxonomy.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    // Inventory Settings landing (cards UI)
    public function settings() {
        try {
            // Load users for stores modal
            require_once __DIR__ . '/../models/InventoryStore.php';
            $storeModel = new InventoryStore();
            $users = $storeModel->getOwnerUsers();
            $stores = $storeModel->listStoresWithUsers();
            require __DIR__ . '/../views/inventory/settings.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    // Stores & Rights
    public function stores() {
        try {
            require_once __DIR__ . '/../models/InventoryStore.php';
            $m = new InventoryStore();
            $stores = $m->listStoresWithUsers();
            $users  = $m->getOwnerUsers();
            require __DIR__ . '/../views/inventory/stores.php';
        } catch (Exception $e) { echo "Error: " . $e->getMessage(); }
    }

    public function saveStore() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?action=inventory&subaction=stores'); return; }
        try {
            require_once __DIR__ . '/../models/InventoryStore.php';
            $m = new InventoryStore();
            $name = trim($_POST['name'] ?? '');
            $storeId = (int)($_POST['store_id'] ?? 0);
            $userIds = isset($_POST['user_ids']) && is_array($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : [];
            $m->createStore($name, $userIds, $storeId);
            $_SESSION['success_message'] = 'Store saved.';
        } catch (Throwable $e) {
            $_SESSION['error_message'] = 'Failed to save store: ' . $e->getMessage();
        }
        header('Location: /?action=inventory&subaction=stores');
        exit();
    }

    // JSON: list stores with assigned users
    public function listStoresWithUsers() {
        try {
            header('Content-Type: application/json');
            require_once __DIR__ . '/../models/InventoryStore.php';
            $m = new InventoryStore();
            echo json_encode($m->listStoresWithUsers());
        } catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
    }

    public function deleteStore() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?action=inventory&subaction=stores'); return; }
        try {
            require_once __DIR__ . '/../models/InventoryStore.php';
            $m = new InventoryStore();
            $storeId = (int)($_POST['store_id'] ?? 0);
            if ($storeId > 0) {
                $m->deleteStore($storeId);
                $_SESSION['success_message'] = 'Store deleted.';
            }
        } catch (Throwable $e) {
            $_SESSION['error_message'] = 'Failed to delete store: ' . $e->getMessage();
        }
        header('Location: /?action=inventory&subaction=stores');
        exit();
    }

    // Units page (manage units)
    public function units() {
        try {
            require_once __DIR__ . '/../models/InventoryUnit.php';
            $u = new InventoryUnit();
            $u->ensureTable();
            $units = $u->getAll();
            require __DIR__ . '/../views/inventory/units.php';
        } catch (Exception $e) { echo "Error: " . $e->getMessage(); }
    }

    // HSN/SAC page (manage HSN codes)
    public function hsn() {
        try {
            require_once __DIR__ . '/../models/InventoryHsn.php';
            $m = new InventoryHsn();
            $m->ensureTable();
            $hsn = $m->getAll();
            require __DIR__ . '/../views/inventory/hsn.php';
        } catch (Exception $e) { echo "Error: " . $e->getMessage(); }
    }

    // JSON units
    public function listUnits() {
        try {
            header('Content-Type: application/json');
            require_once __DIR__ . '/../models/InventoryUnit.php';
            $u = new InventoryUnit();
            $u->ensureTable();
            echo json_encode($u->getAll());
        } catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
    }
    // HSN JSON
    public function listHsn() {
        try {
            header('Content-Type: application/json');
            require_once __DIR__ . '/../models/InventoryHsn.php';
            $m = new InventoryHsn();
            $m->ensureTable();
            echo json_encode($m->getAll());
        } catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
    }
    public function createUnit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $code = trim($_POST['code'] ?? '');
            $label = trim($_POST['label'] ?? '') ?: null;
            $prec = trim($_POST['precision_format'] ?? '') ?: null;
            require_once __DIR__ . '/../models/InventoryUnit.php';
            $u = new InventoryUnit();
            $u->ensureTable();
            $id = $u->create($code, $label, $prec);
            echo json_encode(['id'=>$id, 'code'=>$code, 'label'=>$label, 'precision_format'=>$prec]);
        } catch (Exception $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
    }
    public function createHsn() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $code = trim($_POST['code'] ?? '');
            $rate = isset($_POST['rate']) && $_POST['rate'] !== '' ? floatval($_POST['rate']) : null;
            $note = trim($_POST['note'] ?? '');
            require_once __DIR__ . '/../models/InventoryHsn.php';
            $m = new InventoryHsn();
            $m->ensureTable();
            $id = $m->create($code, $rate, $note);
            echo json_encode(['id'=>$id, 'code'=>$code, 'rate'=>$rate, 'note'=>$note]);
        } catch (Exception $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
    }
    public function updateUnit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            $code = trim($_POST['code'] ?? '');
            $label = trim($_POST['label'] ?? '') ?: null;
            $prec = trim($_POST['precision_format'] ?? '') ?: null;
            $active = (int)($_POST['active'] ?? 1);
            require_once __DIR__ . '/../models/InventoryUnit.php';
            $u = new InventoryUnit();
            $ok = $u->update($id, $code, $label, $prec, $active);
            echo json_encode(['success'=>(bool)$ok]);
        } catch (Exception $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
    }
    public function updateHsn() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            $code = trim($_POST['code'] ?? '');
            $rate = isset($_POST['rate']) && $_POST['rate'] !== '' ? floatval($_POST['rate']) : null;
            $note = trim($_POST['note'] ?? '');
            $active = (int)($_POST['active'] ?? 1);
            require_once __DIR__ . '/../models/InventoryHsn.php';
            $m = new InventoryHsn();
            $ok = $m->update($id, $code, $rate, $note, $active);
            echo json_encode(['success'=>(bool)$ok]);
        } catch (Exception $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
    }
    public function deleteUnit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            require_once __DIR__ . '/../models/InventoryUnit.php';
            $u = new InventoryUnit();
            $ok = $u->delete($id);
            echo json_encode(['success'=>(bool)$ok]);
        } catch (Exception $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
    }
    public function deleteHsn() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            require_once __DIR__ . '/../models/InventoryHsn.php';
            $m = new InventoryHsn();
            $ok = $m->delete($id);
            echo json_encode(['success'=>(bool)$ok]);
        } catch (Exception $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
    }

    // JSON: list all categories (id, name)
    public function listCategories() {
        try {
            header('Content-Type: application/json');
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $catModel->ensureTables();
            $catModel->seedFromInventoryIfEmpty();
            $rows = $catModel->getAllCategories();
            echo json_encode($rows);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // JSON: create category {name}
    public function createCategory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $name = trim($_POST['name'] ?? '');
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $catModel->ensureTables();
            $id = $catModel->createCategory($name);
            echo json_encode(['id'=>$id,'name'=>$name]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // JSON: create sub-category {category_id, name}
    public function createSubCategory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $catModel->ensureTables();
            if ($categoryId <= 0) { throw new Exception('category_id required'); }
            $id = $catModel->createSubCategory($categoryId, $name);
            echo json_encode(['id'=>$id,'name'=>$name]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // JSON: update/delete for settings management
    public function updateCategory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $ok = $catModel->updateCategory($id, $name);
            echo json_encode(['success'=>(bool)$ok]);
        } catch (Exception $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
    }
    public function deleteCategoryApi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $ok = $catModel->deleteCategory($id);
            echo json_encode(['success'=>(bool)$ok]);
        } catch (Exception $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
    }
    public function updateSubCategory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $ok = $catModel->updateSubCategory($id, $name);
            echo json_encode(['success'=>(bool)$ok]);
        } catch (Exception $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
    }
    public function deleteSubCategoryApi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        try {
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $ok = $catModel->deleteSubCategory($id);
            echo json_encode(['success'=>(bool)$ok]);
        } catch (Exception $e) { http_response_code(400); echo json_encode(['error'=>$e->getMessage()]); }
    }

    // JSON: list categories with sub-categories
    public function listCategoriesWithSubs() {
        try {
            header('Content-Type: application/json');
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $catModel->ensureTables();
            $catModel->seedFromInventoryIfEmpty();
            $rows = $catModel->listCategoriesWithSubs();
            echo json_encode($rows);
        } catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
    }

    public function create() {
        try {
            $inventory = new Inventory();
            // Load categories/sub-categories from master tables
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $catModel->ensureTables();
            $catModel->seedFromInventoryIfEmpty();
            $categoriesRows = $catModel->getAllCategories();
            $categories = array_map(function ($r) {
                return $r['name'];
            }, $categoriesRows);
            // Units master
            require_once __DIR__ . '/../models/InventoryUnit.php';
            $u = new InventoryUnit();
            $u->ensureTable();
            $units = $u->getAll();
            // HSN/SAC master
            require_once __DIR__ . '/../models/InventoryHsn.php';
            $hsnModel = new InventoryHsn();
            $hsnModel->ensureTable();
            $hsnList = $hsnModel->getAll();
            // initial full list of subs (optional)
            $subCategories = $inventory->getSubCategories();
            $importanceLevels = $inventory->getImportanceLevels();
            $batchOptions = $inventory->getBatchOptions();
            // Stores modal needs owner users
            require_once __DIR__ . '/../models/InventoryStore.php';
            $storeModel = new InventoryStore();
            $users = $storeModel->getOwnerUsers();
            $stores = $storeModel->listStoresWithUsers();
            
            require __DIR__ . '/../views/inventory/create.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $inventory = new Inventory();
                
                // Calculate value
                $quantity = floatval($_POST['quantity']);
                $stdCost = floatval($_POST['std_cost'] ?? 0);
                $purchCost = floatval($_POST['purch_cost'] ?? 0);
                $rate = $stdCost; // Rate is same as std_cost
                $value = $quantity * $stdCost; // Total value
                
                $data = [
                    'name' => $_POST['name'],
                    'code' => $_POST['code'],
                    'importance' => $_POST['importance'],
                    'category' => $_POST['category'],
                    'sub_category' => $_POST['sub_category'],
                    'batch' => $_POST['batch'] ?? 'No',
                    'quantity' => $quantity,
                    'unit' => $_POST['unit'] ?? 'no.s',
                    'store' => $_POST['store'] ?? '',
                    'item_type' => $_POST['item_type'] ?? 'products',
                    'internal_manufacturing' => isset($_POST['internal_manufacturing']) ? 1 : 0,
                    'purchase' => isset($_POST['purchase']) ? 1 : 0,
                    'rate' => $rate,
                    'value' => $value,
                    'std_cost' => $stdCost,
                    'purch_cost' => $purchCost,
                    'std_sale_price' => floatval($_POST['std_sale_price'] ?? 0),
                    'hsn_sac' => $_POST['hsn_sac'] ?? '',
                    'gst' => floatval($_POST['gst'] ?? 0),
                    'description' => $_POST['description'] ?? '',
                    'internal_notes' => $_POST['internal_notes'] ?? '',
                    'min_stock' => floatval($_POST['min_stock'] ?? 0),
                    'lead_time' => intval($_POST['lead_time'] ?? 0),
                    'tags' => $_POST['tags'] ?? '',
                    'active' => intval($_POST['active'] ?? 1)
                ];
                
                // Sync master categories mapping
                try {
                    require_once __DIR__ . '/../models/InventoryCategory.php';
                    $catModel = new InventoryCategory();
                    $catModel->ensureTables();
                    if (!empty($data['category'])) {
                        $categoryId = $catModel->createCategory($data['category']);
                        if (!empty($data['sub_category'])) {
                            $catModel->createSubCategory($categoryId, $data['sub_category']);
                        }
                    }
                    // Sync unit master
                    if (!empty($data['unit'])) {
                        require_once __DIR__ . '/../models/InventoryUnit.php';
                        $u = new InventoryUnit();
                        $u->ensureTable();
                        $u->create($data['unit']);
                    }
                    // Sync HSN master (code only)
                    if (!empty($data['hsn_sac'])) {
                        require_once __DIR__ . '/../models/InventoryHsn.php';
                        $h = new InventoryHsn();
                        $h->ensureTable();
                        $h->create($data['hsn_sac']);
                    }
                } catch (Throwable $syncEx) { /* ignore sync errors */ }

                if ($inventory->create($data)) {
                    header("Location: /?action=inventory");
                    exit();
                } else {
                    header("Location: /?action=inventory&subaction=create");
                    exit();
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            header("Location: /?action=inventory");
            exit();
        }
    }

    public function show($id) {
        try {
            $inventory = new Inventory();
            $item = $inventory->get($id);
            require __DIR__ . '/../views/inventory/show.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function edit($id) {
        try {
            $inventory = new Inventory();
            $item = $inventory->get($id);
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $catModel->ensureTables();
            $catModel->seedFromInventoryIfEmpty();
            $categoriesRows = $catModel->getAllCategories();
            $categories = array_map(function ($r) {
                return $r['name'];
            }, $categoriesRows);
            // Units master
            require_once __DIR__ . '/../models/InventoryUnit.php';
            $u = new InventoryUnit();
            $u->ensureTable();
            $units = $u->getAll();
            // HSN/SAC master
            require_once __DIR__ . '/../models/InventoryHsn.php';
            $hsnModel = new InventoryHsn();
            $hsnModel->ensureTable();
            $hsnList = $hsnModel->getAll();
            // initial subs - will be dynamically filtered on load
            $subCategories = $inventory->getSubCategories();
            $importanceLevels = $inventory->getImportanceLevels();
            $batchOptions = $inventory->getBatchOptions();
            // Stores modal needs owner users
            require_once __DIR__ . '/../models/InventoryStore.php';
            $storeModel = new InventoryStore();
            $users = $storeModel->getOwnerUsers();
            $stores = $storeModel->listStoresWithUsers();
            
            require __DIR__ . '/../views/inventory/edit.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $inventory = new Inventory();
                
                // Calculate value
                $quantity = floatval($_POST['quantity']);
                $stdCost = floatval($_POST['std_cost'] ?? 0);
                $purchCost = floatval($_POST['purch_cost'] ?? 0);
                $rate = $stdCost; // Rate is same as std_cost
                $value = $quantity * $stdCost; // Total value
                
                $data = [
                    'name' => $_POST['name'],
                    'code' => $_POST['code'],
                    'importance' => $_POST['importance'],
                    'category' => $_POST['category'],
                    'sub_category' => $_POST['sub_category'],
                    'batch' => $_POST['batch'] ?? 'No',
                    'quantity' => $quantity,
                    'unit' => $_POST['unit'] ?? 'no.s',
                    'store' => $_POST['store'] ?? '',
                    'item_type' => $_POST['item_type'] ?? 'products',
                    'internal_manufacturing' => isset($_POST['internal_manufacturing']) ? 1 : 0,
                    'purchase' => isset($_POST['purchase']) ? 1 : 0,
                    'rate' => $rate,
                    'value' => $value,
                    'std_cost' => $stdCost,
                    'purch_cost' => $purchCost,
                    'std_sale_price' => floatval($_POST['std_sale_price'] ?? 0),
                    'hsn_sac' => $_POST['hsn_sac'] ?? '',
                    'gst' => floatval($_POST['gst'] ?? 0),
                    'description' => $_POST['description'] ?? '',
                    'internal_notes' => $_POST['internal_notes'] ?? '',
                    'min_stock' => floatval($_POST['min_stock'] ?? 0),
                    'lead_time' => intval($_POST['lead_time'] ?? 0),
                    'tags' => $_POST['tags'] ?? '',
                    'active' => intval($_POST['active'] ?? 1)
                ];
                
                // Sync master categories mapping
                try {
                    require_once __DIR__ . '/../models/InventoryCategory.php';
                    $catModel = new InventoryCategory();
                    $catModel->ensureTables();
                    if (!empty($data['category'])) {
                        $categoryId = $catModel->createCategory($data['category']);
                        if (!empty($data['sub_category'])) {
                            $catModel->createSubCategory($categoryId, $data['sub_category']);
                        }
                    }
                } catch (Throwable $syncEx) { /* ignore sync errors */ }

                if ($inventory->update($id, $data)) {
                    header("Location: /?action=inventory");
                    exit();
                } else {
                    header("Location: /?action=inventory&subaction=edit&id=" . $id);
                    exit();
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            header("Location: /?action=inventory");
            exit();
        }
    }

    public function delete($id) {
        try {
            $inventory = new Inventory();
            if ($inventory->delete($id)) {
                header("Location: /?action=inventory");
                exit();
            } else {
                header("Location: /?action=inventory");
                exit();
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    // JSON: list sub-categories for a given category
    public function listSubCategories() {
        try {
            header('Content-Type: application/json');
            $category = trim($_GET['category'] ?? '');
            if ($category === '') {
                echo json_encode([]);
                return;
            }
            require_once __DIR__ . '/../models/InventoryCategory.php';
            $catModel = new InventoryCategory();
            $catModel->ensureTables();
            // Prefer master mapping; fallback to inventory table if master empty
            $subs = $catModel->getSubCategoryNamesByCategoryName($category);
            if (empty($subs)) {
                $inventory = new Inventory();
                $subs = $inventory->getSubCategoriesByCategory($category);
            }
            echo json_encode(array_values($subs));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function toggleStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $inventory = new Inventory();
                $status = $_POST['status'] === 'active' ? 1 : 0;
                
                if ($inventory->toggleStatus($id, $status)) {
                    echo "Status updated successfully";
                } else {
                    echo "Error updating status";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            header("Location: /?action=inventory");
            exit();
        }
    }

    public function downloadTemplate() {
        try {
            $templateFile = __DIR__ . '/../../public/inventory_template_clean.csv';
            
            if (file_exists($templateFile)) {
                // Set headers for CSV download
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment;filename="inventory_template.csv"');
                header('Cache-Control: max-age=0');
                
                // Output the template file
                readfile($templateFile);
                exit();
            } else {
                throw new Exception('Template file not found');
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    private function resetTemplate() {
        $originalTemplateFile = __DIR__ . '/../../public/inventory_template.csv';
        $cleanTemplateFile = __DIR__ . '/../../public/inventory_template_clean.csv';
        
        if (file_exists($cleanTemplateFile)) {
            copy($cleanTemplateFile, $originalTemplateFile);
        }
    }

    public function importExcel() {
        // Suppress warnings to prevent them from breaking JSON response
        error_reporting(E_ERROR | E_PARSE);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!isset($_FILES['excel_file'])) {
                    throw new Exception('No file uploaded');
                }
                
                $file = $_FILES['excel_file'];
                
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $uploadErrors = [
                        UPLOAD_ERR_INI_SIZE => 'File too large (exceeds php.ini limit)',
                        UPLOAD_ERR_FORM_SIZE => 'File too large (exceeds form limit)',
                        UPLOAD_ERR_PARTIAL => 'File upload was incomplete',
                        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
                    ];
                    $errorMsg = $uploadErrors[$file['error']] ?? 'Unknown upload error: ' . $file['error'];
                    throw new Exception('Upload error: ' . $errorMsg);
                }
                
                $fileName = $file['name'];
                $fileTmpName = $file['tmp_name'];
                
                // Check file extension
                $allowedExtensions = ['xlsx', 'xls', 'csv'];
                $fileExtension = strtolower(trim(pathinfo($fileName, PATHINFO_EXTENSION)));
                
                // Debug information
                error_log("File upload debug - Name: $fileName, Extension: $fileExtension, Size: " . $file['size']);
                
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new Exception('Invalid file format. Please upload Excel or CSV file. Got: ' . $fileExtension);
                }
                
                // Handle CSV files
                if ($fileExtension === 'csv') {
                    $handle = fopen($fileTmpName, 'r');
                    if (!$handle) {
                        throw new Exception('Failed to open CSV file');
                    }
                    
                    $header = fgetcsv($handle, 0, ',', '"', '\\');
                    if (!$header) {
                        throw new Exception('Failed to read CSV header');
                    }
                    
                    $imported = 0;
                    $errors = [];
                    $rowCount = 0;
                    $skippedRows = 0;
                    
                    while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                        $rowCount++;
                        try {
                            if (count($data) >= count($header)) {
                                $row = array_combine($header, $data);
                                
                                // Skip if this is a header row (check if Name contains "Name" and Code contains "Code")
                                if (strtolower(trim($row['Name'])) === 'name' && strtolower(trim($row['Code'])) === 'code') {
                                    $skippedRows++;
                                    continue; // Skip header row
                                }
                                
                                // Batch is now always 'No' by default - no need to check
                                
                                // Skip empty rows (all fields empty)
                                if (empty(trim($row['Name'])) && empty(trim($row['Code'])) && empty(trim($row['Category']))) {
                                    $skippedRows++;
                                    continue;
                                }
                                
                                // Skip sample data rows (check for known sample items)
                                $sampleItems = ['sample item', 'fire alarm panel', 'led emergency light'];
                                if (in_array(strtolower(trim($row['Name'])), $sampleItems)) {
                                    $skippedRows++;
                                    continue; // Skip sample data rows
                                }
                                
                                // Validate required fields
                                if (empty(trim($row['Name'])) || empty(trim($row['Code']))) {
                                    $errors[] = "Row $rowCount: Name and Code are required fields";
                                    continue;
                                }
                                
                                // Prepare data for import
                                $itemData = [
                                    'name' => $row['Name'] ?? '',
                                    'code' => $row['Code'] ?? '',
                                    'importance' => $row['Importance'] ?? 'Normal',
                                    'category' => $row['Category'] ?? '',
                                    'sub_category' => $row['Sub Category'] ?? '',
                                    'batch' => 'No', // Always set to 'No' by default
                                    'quantity' => floatval($row['Quantity'] ?? 0),
                                    'unit' => $row['Unit'] ?? 'no.s',
                                    'store' => $row['Store'] ?? '',
                                    'item_type' => $row['Item Type'] ?? 'products',
                                    'internal_manufacturing' => intval($row['Internal Manufacturing'] ?? 0),
                                    'purchase' => intval($row['Purchase'] ?? 0),
                                    'std_cost' => floatval($row['Standard Cost'] ?? 0),
                                    'purch_cost' => floatval($row['Purchase Cost'] ?? 0),
                                    'std_sale_price' => floatval($row['Standard Sale Price'] ?? 0),
                                    'hsn_sac' => $row['HSN/SAC'] ?? '',
                                    'gst' => floatval($row['GST'] ?? 0),
                                    'description' => $row['Description'] ?? '',
                                    'internal_notes' => $row['Internal Notes'] ?? '',
                                    'min_stock' => floatval($row['Min Stock'] ?? 0),
                                    'lead_time' => intval($row['Lead Time'] ?? 0),
                                    'tags' => $row['Tags'] ?? '',
                                    'active' => intval($row['Status'] ?? 1)
                                ];
                                
                                // Calculate rate and value
                                $itemData['rate'] = $itemData['std_cost'];
                                $itemData['value'] = $itemData['quantity'] * $itemData['std_cost'];
                                
                                $inventory = new Inventory();
                                try {
                                    if ($inventory->create($itemData)) {
                                        $imported++;
                                    } else {
                                        $errors[] = "Row $rowCount: Failed to create item";
                                    }
                                } catch (Exception $e) {
                                    $errors[] = "Row $rowCount: " . $e->getMessage();
                                }
                            }
                        } catch (Exception $e) {
                            $errors[] = "Row " . ($imported + 1) . ": " . $e->getMessage();
                        }
                    }
                    
                    fclose($handle);
                    
                    // Reset template to clean version after import
                    $this->resetTemplate();
                    
                    $response = [
                        'success' => true,
                        'message' => "Successfully imported $imported items" . 
                                   (count($errors) > 0 ? ". " . count($errors) . " errors occurred." : "") .
                                   ($skippedRows > 0 ? " Skipped $skippedRows header/sample rows." : ""),
                        'imported' => $imported,
                        'errors' => $errors,
                        'total_rows' => $rowCount,
                        'skipped_rows' => $skippedRows
                    ];
                    
                    // Clear any output buffers to ensure clean JSON response
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit();
                } else {
                    throw new Exception('Excel file processing not yet implemented. Please use CSV format.');
                }
                
            } catch (Exception $e) {
                $response = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                
                // Clear any output buffers to ensure clean JSON response
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }
        } else {
            header("Location: /?action=inventory");
            exit();
        }
    }
}
?> 