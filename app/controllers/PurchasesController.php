<?php
require_once __DIR__ . '/../models/SupplierInvoice.php';

class PurchasesController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        try {
            $supplierInvoiceModel = new SupplierInvoice();
            $rows = $supplierInvoiceModel->getAll([]);
            require __DIR__ . '/../views/purchases/index.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function create(): void {
        try {
            $supplierInvoiceModel = new SupplierInvoice();
            $nextNo = $supplierInvoiceModel->getNextInvoiceNo();

            // Get inventory items for client-side picker
            require_once __DIR__ . '/../models/Inventory.php';
            $inventory = new Inventory();
            $items = $inventory->getAll();
            $pickerItems = array_map(function($i){
                return [
                    'id' => $i['id'],
                    'name' => $i['name'],
                    'code' => $i['code'],
                    'hsn_sac' => $i['hsn_sac'] ?? '',
                    'unit' => $i['unit'] ?? 'no.s',
                    'rate' => (float)($i['std_cost'] ?? 0),
                    'gst' => (float)($i['gst'] ?? 0),
                ];
            }, $items);

            // Users for Executive dropdown
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $users = $ownerId > 0 ? $userModel->getByOwner($ownerId) : [];

            $prefill = [];
            require __DIR__ . '/../views/purchases/create.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=purchases');
            exit();
        }

        try {
            $supplierInvoiceModel = new SupplierInvoice();

            // Handle optional attachment upload
            $attachmentPath = null;
            if (!empty($_FILES['attachment']) && is_uploaded_file($_FILES['attachment']['tmp_name'])) {
                $uploadDir = __DIR__ . '/../../public/uploads/purchases/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                $safeName = date('YmdHis') . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($_FILES['attachment']['name']));
                $target = $uploadDir . $safeName;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
                    $attachmentPath = '/uploads/purchases/' . $safeName;
                }
            }

            // Look up supplier_id if supplier name is provided
            $supplierId = null;
            $supplierName = trim($_POST['supplier'] ?? '');
            if (!empty($supplierName)) {
                try {
                    require_once __DIR__ . '/../models/Customer.php';
                    $customerModel = new Customer();
                    $suppliers = $customerModel->getAll(['q' => $supplierName, 'type' => 'supplier']);
                    // Try exact match first, then first result
                    foreach ($suppliers as $s) {
                        if (strcasecmp($s['company'], $supplierName) === 0) {
                            $supplierId = (int)$s['id'];
                            break;
                        }
                    }
                    if ($supplierId === null && !empty($suppliers)) {
                        $supplierId = (int)($suppliers[0]['id'] ?? 0);
                    }
                } catch (Throwable $e) {
                    // If lookup fails, continue without supplier_id
                }
            }
            
            $payload = [
                'supplier' => $supplierName,
                'supplier_id' => $supplierId,
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'party_address' => trim($_POST['party_address'] ?? ''),
                'shipping_address' => trim($_POST['shipping_address'] ?? ''),
                'invoice_no' => trim($_POST['invoice_no'] ?? ''),
                'reference' => trim($_POST['reference'] ?? ''),
                'invoice_date' => $_POST['invoice_date'] ?? date('Y-m-d'),
                'due_date' => $_POST['due_date'] ?? null,
                'executive' => trim($_POST['executive'] ?? ''),
                'taxable_total' => floatval($_POST['taxable_total'] ?? 0),
                'total_amount' => floatval($_POST['total_amount'] ?? 0),
                'items_json' => $_POST['items_json'] ?? '[]',
                'terms_json' => $_POST['terms_json'] ?? '[]',
                'notes' => trim($_POST['notes'] ?? ''),
                'extra_charge' => floatval($_POST['extra_charge'] ?? 0),
                'overall_discount' => floatval($_POST['overall_discount'] ?? 0),
                'bank_account_id' => !empty($_POST['bank_account_id']) ? intval($_POST['bank_account_id']) : null,
                'attachment_path' => $attachmentPath,
                'credit_month' => null,
            ];

            $supplierInvoiceModel->create($payload);
            header('Location: /?action=purchases');
            exit();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function listContacts(): void {
        header('Content-Type: application/json');
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $type = strtolower(trim($_GET['type'] ?? 'supplier'));
            $q = trim($_GET['q'] ?? '');
            if ($type !== 'supplier') { $type = 'supplier'; }
            
            $out = [];
            try {
                require_once __DIR__ . '/../models/Customer.php';
                $m = new Customer();
                $rows = $m->getAll(['q' => $q, 'type' => 'supplier']);
            } catch (Throwable $e) {
                require_once __DIR__ . '/../config/database.php';
                $db = new Database(); $pdo = $db->getConnection();
                $like = '%' . $q . '%';
                $st = $pdo->prepare("SELECT id, company, contact_name, city FROM customers WHERE type = 'supplier' AND (company LIKE ? OR contact_name LIKE ?) ORDER BY company ASC LIMIT 25");
                $st->execute([$like, $like]);
                $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
            foreach ($rows as $r) {
                $out[] = [
                    'id' => (int)($r['id'] ?? 0),
                    'label' => (string)($r['company'] ?? ''),
                    'sublabel' => trim((string)($r['contact_name'] ?? '')),
                    'city' => (string)($r['city'] ?? ''),
                    'type' => 'supplier',
                ];
            }
            echo json_encode($out);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getContact(): void {
        header('Content-Type: application/json');
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $type = strtolower(trim($_GET['type'] ?? 'supplier'));
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) { http_response_code(400); echo json_encode(['error' => 'Invalid id']); return; }
            if ($type !== 'supplier') { $type = 'supplier'; }
            
            try {
                require_once __DIR__ . '/../models/Customer.php';
                $m = new Customer();
                $c = $m->get($id);
            } catch (Throwable $e) {
                require_once __DIR__ . '/../config/database.php';
                $db = new Database(); $pdo = $db->getConnection();
                $st = $pdo->prepare("SELECT * FROM customers WHERE type = 'supplier' AND id = ? LIMIT 1");
                $st->execute([$id]);
                $c = $st->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$c) { http_response_code(404); echo json_encode(['error' => 'Not found']); return; }
            
            // Get addresses with formatting
            $party = '';
            $addresses = [];
            try {
                require_once __DIR__ . '/../models/CustomerAddress.php';
                $addrModel = new CustomerAddress();
                $addrs = $addrModel->listByCustomer($id);
                if (!empty($addrs)) {
                    foreach ($addrs as $a) {
                        $formatted = trim(implode("\n", array_filter([
                            trim((string)($a['line1'] ?? '')),
                            trim((string)($a['line2'] ?? '')),
                            trim(implode(', ', array_filter([
                                (string)($a['city'] ?? ''),
                                (string)($a['state'] ?? ''),
                                (string)($a['country'] ?? ''),
                                (string)($a['pincode'] ?? ''),
                            ]))),
                        ])));
                        $addresses[] = [
                            'id' => (int)($a['id'] ?? 0),
                            'title' => (string)($a['title'] ?? ''),
                            'formatted' => $formatted,
                        ];
                    }
                    $first = $addresses[0] ?? null;
                    if ($first) { $party = $first['formatted']; }
                }
            } catch (Throwable $e) {
                // fall back silently
            }
            if ($party === '') {
                $party = trim(implode("\n", array_filter([
                    (string)($c['city'] ?? ''),
                    (string)($c['state'] ?? ''),
                    (string)($c['country'] ?? ''),
                ])));
            }
            
            echo json_encode([
                'supplier' => $c['company'] ?? '',
                'contact_person' => $c['contact_name'] ?? '',
                'party_address' => $party,
                'addresses' => $addresses,
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
