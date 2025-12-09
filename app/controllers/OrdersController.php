<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/User.php';

class OrdersController {
    private Order $orders;

    public function __construct() {
        $this->orders = new Order();
    }

    public function index(): void {
        // Default to list view
        $this->list();
    }

    public function list(): void {
        // HTML view listing orders
        $filters = [
            'status' => $_GET['status'] ?? null,
            'q' => $_GET['q'] ?? null,
        ];
        $orders = $this->orders->getAll($filters);
        require __DIR__ . '/../views/orders/index.php';
    }

    public function create(): void {
        // Create form
        $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        $customer = null;
        if ($customerId) {
            $customerModel = new Customer();
            $customer = $customerModel->get($customerId);
        }

        // Inventory picker items (for item auto-fill from inventory)
        $inventory = new Inventory();
        $invItems = $inventory->getAll();
        $pickerItems = array_map(function($i){
            return [
                'id' => $i['id'],
                'name' => $i['name'],
                'code' => $i['code'],
                'hsn_sac' => $i['hsn_sac'] ?? '',
                'unit' => $i['unit'] ?? 'nos',
                'rate' => (float)($i['std_cost'] ?? 0),
                'gst' => (float)($i['gst'] ?? 0),
            ];
        }, $invItems);

        // Users for Sales Credit dropdown (scope to current owner)
        $userModel = new User();
        if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $users = $ownerId > 0 ? $userModel->getByOwner($ownerId) : [];

        // View helpers
        $mode = 'create';
        $order = null;
        $items = [];
        $terms = [];

        require __DIR__ . '/../views/orders/create.php';
    }

    public function edit(int $id = 0): void {
        // Edit form: load order, items and terms
        if ($id <= 0) {
            header('Location: /?action=orders');
            return;
        }

        try {
            $order = $this->orders->getWithDetails($id);
            if (!$order) {
                echo 'Order not found';
                return;
            }

            // For now we do not reload the linked customer; header shows basic info only
            $customer = null;
            // Inventory picker items (for item auto-fill from inventory)
            $inventory = new Inventory();
            $invItems = $inventory->getAll();
            $pickerItems = array_map(function($i){
                return [
                    'id' => $i['id'],
                    'name' => $i['name'],
                    'code' => $i['code'],
                    'hsn_sac' => $i['hsn_sac'] ?? '',
                    'unit' => $i['unit'] ?? 'nos',
                    'rate' => (float)($i['std_cost'] ?? 0),
                    'gst' => (float)($i['gst'] ?? 0),
                ];
            }, $invItems);

            // Users for Sales Credit dropdown (scope to current owner)
            $userModel = new User();
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $users = $ownerId > 0 ? $userModel->getByOwner($ownerId) : [];
            $mode = 'edit';
            $items = $order['items'] ?? [];
            // Terms arrive as rows with term_text
            $terms = $order['terms'] ?? [];

            require __DIR__ . '/../views/orders/create.php';
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Failed to load order: ' . htmlspecialchars($e->getMessage());
        }
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?action=orders'); exit(); }
        try {
            // Handle optional attachment upload for orders
            $attachmentPath = null;
            if (!empty($_FILES['attachment']) && is_uploaded_file($_FILES['attachment']['tmp_name'])) {
                $err = (int)($_FILES['attachment']['error'] ?? UPLOAD_ERR_OK);
                if ($err === UPLOAD_ERR_OK) {
                    $size = (int)$_FILES['attachment']['size'];
                    if ($size > 10 * 1024 * 1024) { throw new Exception('Attachment too large (max 10MB)'); }
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['attachment']['tmp_name']);
                    finfo_close($finfo);
                    $allowed = ['application/pdf','image/png','image/jpeg','image/gif','image/webp'];
                    if (!in_array($mime, $allowed, true)) { throw new Exception('Unsupported attachment type'); }
                    $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                    $safeName = 'order_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext? ('.' . preg_replace('/[^a-zA-Z0-9]/','',$ext)) : '');
                    $targetDir = realpath(__DIR__ . '/../../public') . '/uploads/orders/';
                    if (!is_dir($targetDir)) { @mkdir($targetDir, 0777, true); }
                    $targetPath = $targetDir . $safeName;
                    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                        throw new Exception('Failed to save attachment');
                    }
                    $attachmentPath = '/uploads/orders/' . $safeName;
                } elseif ($err !== UPLOAD_ERR_NO_FILE) {
                    throw new Exception('Upload error');
                }
            }

            $data = [
                'customer_id' => (int)($_POST['customer_id'] ?? 0),
                'contact_name' => trim($_POST['contact_name'] ?? ''),
                'order_no' => trim($_POST['order_no'] ?? ''),
                'customer_po' => trim($_POST['customer_po'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
                'billing_address' => trim($_POST['billing_address'] ?? ''),
                'shipping_address' => trim($_POST['shipping_address'] ?? ''),
                'bank_account_id' => isset($_POST['bank_account_id']) ? (int)$_POST['bank_account_id'] : null,
                'notes' => trim($_POST['notes'] ?? ''),
                'sales_credit' => trim($_POST['sales_credit'] ?? ''),
                'order_date' => !empty($_POST['order_date']) ? $_POST['order_date'] : null,
                'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                'status' => trim($_POST['status'] ?? 'Pending'),
                'attachment_path' => $attachmentPath,
            ];

            // Prefer quotation-style JSON payload for items/terms if present
            $items = [];
            $terms = [];
            if (!empty($_POST['items_json'])) {
                $decoded = json_decode((string)$_POST['items_json'], true);
                if (is_array($decoded)) {
                    foreach ($decoded as $row) {
                        if (!is_array($row)) { continue; }
                        $name = trim((string)($row['name'] ?? ''));
                        if ($name === '') { continue; }
                        $qty = (float)($row['qty'] ?? 1);
                        $unit = (string)($row['unit'] ?? 'nos');
                        $rate = (float)($row['rate'] ?? 0);
                        $discount = (float)($row['discount'] ?? 0);
                        $taxable = isset($row['taxable']) ? (float)$row['taxable'] : max($qty * $rate - $discount, 0);
                        $amount = isset($row['amount']) ? (float)$row['amount'] : $taxable;
                        $items[] = [
                            'item_name'    => $name,
                            'description'  => (string)($row['description'] ?? ''),
                            'qty'          => $qty,
                            'unit'         => $unit,
                            'rate'         => $rate,
                            'hsn_sac'      => (string)($row['hsn_sac'] ?? ''),
                            'discount'     => $discount,
                            'gst_pct'      => (float)($row['gst'] ?? 0),
                            'gst_included' => !empty($row['gst_included']) ? 1 : 0,
                            'taxable'      => $taxable,
                            'amount'       => $amount,
                        ];
                    }
                }
            }
            if (!$items && isset($_POST['items']) && is_array($_POST['items'])) {
                // Fallback to legacy items[] shape from old Enter Order UI
                $items = $_POST['items'];
            }

            if (!empty($_POST['terms_json'])) {
                $decodedTerms = json_decode((string)$_POST['terms_json'], true);
                if (is_array($decodedTerms)) {
                    foreach ($decodedTerms as $t) {
                        $t = trim((string)$t);
                        if ($t !== '') { $terms[] = $t; }
                    }
                }
            }
            if (!$terms && isset($_POST['tnc']) && is_array($_POST['tnc'])) {
                $terms = array_values(array_filter(array_map('trim', $_POST['tnc'])));
            }

            $data['items'] = $items;
            $data['terms'] = $terms;
            $id = $this->orders->create($data);
            header('Location: /?action=orders');
            exit();
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Failed to save order: ' . htmlspecialchars($e->getMessage());
        }
    }

    public function update(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?action=orders'); exit(); }
        if ($id <= 0) { header('Location: /?action=orders'); exit(); }

        try {
            // Mirror store() header fields so edit updates everything user can change
            $data = [
                'customer_id'      => (int)($_POST['customer_id'] ?? 0),
                'contact_name'     => trim($_POST['contact_name'] ?? ''),
                'order_no'         => trim($_POST['order_no'] ?? ''),
                'customer_po'      => trim($_POST['customer_po'] ?? ''),
                'category'         => trim($_POST['category'] ?? ''),
                'billing_address'  => trim($_POST['billing_address'] ?? ''),
                'shipping_address' => trim($_POST['shipping_address'] ?? ''),
                'bank_account_id'  => isset($_POST['bank_account_id']) ? (int)$_POST['bank_account_id'] : null,
                'notes'            => trim($_POST['notes'] ?? ''),
                'sales_credit'     => trim($_POST['sales_credit'] ?? ''),
                'order_date'       => !empty($_POST['order_date']) ? $_POST['order_date'] : null,
                'due_date'         => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                'status'           => trim($_POST['status'] ?? 'Pending'),
            ];

            $items = [];
            $terms = [];
            if (!empty($_POST['items_json'])) {
                $decoded = json_decode((string)$_POST['items_json'], true);
                if (is_array($decoded)) {
                    foreach ($decoded as $row) {
                        if (!is_array($row)) { continue; }
                        $name = trim((string)($row['name'] ?? ''));
                        if ($name === '') { continue; }
                        $qty = (float)($row['qty'] ?? 1);
                        $unit = (string)($row['unit'] ?? 'nos');
                        $rate = (float)($row['rate'] ?? 0);
                        $discount = (float)($row['discount'] ?? 0);
                        $taxable = isset($row['taxable']) ? (float)$row['taxable'] : max($qty * $rate - $discount, 0);
                        $amount = isset($row['amount']) ? (float)$row['amount'] : $taxable;
                        $items[] = [
                            'item_name'    => $name,
                            'description'  => (string)($row['description'] ?? ''),
                            'qty'          => $qty,
                            'unit'         => $unit,
                            'rate'         => $rate,
                            'hsn_sac'      => (string)($row['hsn_sac'] ?? ''),
                            'discount'     => $discount,
                            'gst_pct'      => (float)($row['gst'] ?? 0),
                            'gst_included' => !empty($row['gst_included']) ? 1 : 0,
                            'taxable'      => $taxable,
                            'amount'       => $amount,
                        ];
                    }
                }
            }
            if (!$items && isset($_POST['items']) && is_array($_POST['items'])) {
                $items = $_POST['items'];
            }

            if (!empty($_POST['terms_json'])) {
                $decodedTerms = json_decode((string)$_POST['terms_json'], true);
                if (is_array($decodedTerms)) {
                    foreach ($decodedTerms as $t) {
                        $t = trim((string)$t);
                        if ($t !== '') { $terms[] = $t; }
                    }
                }
            }
            if (!$terms && isset($_POST['tnc']) && is_array($_POST['tnc'])) {
                $terms = array_values(array_filter(array_map('trim', $_POST['tnc'])));
            }

            $data['items'] = $items;
            $data['terms'] = $terms;
            $this->orders->update($id, $data);
            header('Location: /?action=orders');
            exit();
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Failed to update order: ' . htmlspecialchars($e->getMessage());
        }
    }

    public function updateStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($id <= 0 || $status === '') { http_response_code(400); echo 'Bad Request'; return; }
        try {
            $ok = $this->orders->updateStatus($id, $status);
            header('Content-Type: application/json');
            echo json_encode(['success' => $ok]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function showJson(): void {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Bad Request']); return; }
        try {
            $o = $this->orders->getWithDetails($id);
            if (!$o) { http_response_code(404); echo json_encode(['error'=>'Not found']); return; }
            echo json_encode($o);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error'=>$e->getMessage()]);
        }
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            return;
        }
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid id']);
            return;
        }
        try {
            $ok = $this->orders->delete($id);
            echo json_encode(['success' => (bool)$ok]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Printable view for orders, similar to quotations print
    public function print(int $id): void {
        if ($id <= 0) {
            echo 'Invalid order id';
            return;
        }

        try {
            $order = $this->orders->getWithDetails($id);
            if (!$order) {
                echo 'Order not found';
                return;
            }

            // Map items into a structure compatible with print template
            $items = [];
            foreach (($order['items'] ?? []) as $row) {
                $items[] = [
                    'name'        => $row['item_name'] ?? '',
                    'description' => $row['description'] ?? '',
                    'hsn_sac'     => $row['hsn_sac'] ?? '',
                    'qty'         => $row['qty'] ?? 0,
                    'unit'        => $row['unit'] ?? '',
                    'rate'        => $row['rate'] ?? 0,
                    'discount'    => $row['discount'] ?? 0,
                    'gst'         => $row['gst_pct'] ?? 0,
                    'gst_included'=> $row['gst_included'] ?? 0,
                    'taxable'     => $row['taxable'] ?? 0,
                    'amount'      => $row['amount'] ?? 0,
                ];
            }

            // Terms as simple strings
            $terms = [];
            foreach (($order['terms'] ?? []) as $t) {
                if (is_array($t) && isset($t['term_text'])) {
                    $txt = trim((string)$t['term_text']);
                    if ($txt !== '') { $terms[] = $txt; }
                } elseif (is_string($t)) {
                    $txt = trim($t);
                    if ($txt !== '') { $terms[] = $txt; }
                }
            }

            // Load store settings (company info) and selected bank (for print footer)
            require_once __DIR__ . '/../models/StoreSetting.php';
            require_once __DIR__ . '/../models/BankAccount.php';
            $store = new StoreSetting();
            $settings = $store->getByKeys(['basic_company','basic_city','basic_state','basic_gstin']);
            $bank = null;
            if (!empty($order['bank_account_id'])) {
                $ba = new BankAccount();
                $bank = $ba->findById((int)$order['bank_account_id']);
            }

            // For orders we do a browser-based print view (no Dompdf integration here)
            $row = $order;
            header('Content-Type: text/html; charset=UTF-8');
            include __DIR__ . '/../views/orders/print.php';
        } catch (Throwable $e) {
            echo 'Error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
