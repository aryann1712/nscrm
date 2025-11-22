<?php
class InvoicesController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        require_once __DIR__ . '/../models/Invoice.php';
        if (!class_exists('Invoice')) { throw new Exception('Invoice model not loaded'); }
        $invoiceModel = new Invoice();
        $period = $_GET['period'] ?? 'this_month';
        $search = trim($_GET['q'] ?? '');
        $rows = $invoiceModel->listFiltered($period, $search);
        require __DIR__ . '/../views/invoices/index.php';
    }

    public function create(): void {
        require_once __DIR__ . '/../models/Invoice.php';
        $invoiceModel = new Invoice();
        $nextNo = $invoiceModel->getNextInvoiceNo();

        // Optional: Prefill from a quotation
        $prefill = null;
        $prefillItems = [];
        try {
            $fromQuote = isset($_GET['from_quote']) ? (int)$_GET['from_quote'] : 0;
            if ($fromQuote > 0) {
                require_once __DIR__ . '/../models/Quotation.php';
                $qModel = new Quotation();
                $qModel->ensureSchema();
                $q = $qModel->findById($fromQuote);
                if ($q) {
                    $prefill = [
                        'invoice_no' => $nextNo,
                        'customer' => $q['customer'] ?? '',
                        'reference' => $q['reference'] ?? '',
                        'contact_person' => $q['contact_person'] ?? '',
                        'party_address' => $q['party_address'] ?? '',
                        'shipping_address' => $q['shipping_address'] ?? '',
                        'issued_on' => $q['issued_on'] ?? date('Y-m-d'),
                        'valid_till' => $q['valid_till'] ?? null,
                        'executive' => $q['executive'] ?? '',
                        'notes' => $q['notes'] ?? '',
                        'bank_account_id' => $q['bank_account_id'] ?? '',
                        'extra_charge' => (float)($q['extra_charge'] ?? 0),
                        'overall_discount' => (float)($q['overall_discount'] ?? 0),
                        'amount' => (float)($q['amount'] ?? 0),
                    ];
                    $items = [];
                    if (!empty($q['items_json'])) {
                        $items = json_decode($q['items_json'], true) ?: [];
                    }
                    foreach ($items as $it) {
                        $qty = (float)($it['qty'] ?? 1);
                        $rate = (float)($it['rate'] ?? 0);
                        $discAmt = (float)($it['discount'] ?? 0);
                        $prefillItems[] = [
                            'name' => (string)($it['name'] ?? ($it['description'] ?? 'Item')),
                            'description' => (string)($it['description'] ?? ''),
                            'hsn_sac' => (string)($it['hsn_sac'] ?? ''),
                            'qty' => $qty,
                            'unit' => (string)($it['unit'] ?? 'nos'),
                            'rate' => $rate,
                            'discount' => $discAmt, // invoice UI uses amount discount like quotation
                            'gst' => (float)($it['gst'] ?? $it['igst'] ?? 0),
                            'gst_included' => (int)($it['gst_included'] ?? 0),
                        ];
                    }
                }
            }
        } catch (Throwable $e) { /* ignore prefill errors */ }

        // Inventory picker
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
                'lead_time' => (int)($i['lead_time'] ?? 0),
            ];
        }, $items);

        // Users for Executive dropdown
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        $users = $userModel->getAll();

        // Banks for Payment/Bank details dropdown
        require_once __DIR__ . '/../models/BankAccount.php';
        $bankModel = new BankAccount();
        $banks = $bankModel->getAll();

        require __DIR__ . '/../views/invoices/create.php';
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=invoices');
            exit();
        }
        try {
            require_once __DIR__ . '/../models/Invoice.php';
            $invoiceModel = new Invoice();

            // Attachment
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
                    $safeName = 'invoice_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext? ('.' . preg_replace('/[^a-zA-Z0-9]/','',$ext)) : '');
                    $targetDir = realpath(__DIR__ . '/../../public') . '/uploads/quotations/';
                    if (!is_dir($targetDir)) { @mkdir($targetDir, 0777, true); }
                    $targetPath = $targetDir . $safeName;
                    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                        throw new Exception('Failed to save attachment');
                    }
                    $attachmentPath = '/uploads/quotations/' . $safeName;
                } else if ($err !== UPLOAD_ERR_NO_FILE) {
                    throw new Exception('Upload error');
                }
            }

            // Compute totals
            $itemsRaw = $_POST['items_json'] ?? '[]';
            $itemsArr = json_decode($itemsRaw, true);
            if (!is_array($itemsArr)) $itemsArr = [];
            $taxableTotal = 0.0; foreach ($itemsArr as $it) { $taxableTotal += (float)($it['taxable'] ?? 0); }

            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $issuedByVal = '';
            if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
                $issuedByVal = trim(($_SESSION['user']['name'] ?? '') ?: ($_SESSION['user']['email'] ?? ''));
            } elseif (!empty($_SESSION['user_name'])) {
                $issuedByVal = (string)$_SESSION['user_name'];
            } elseif (!empty($_SESSION['email'])) {
                $issuedByVal = (string)$_SESSION['email'];
            }

            $receivedNow = (float)($_POST['payment_received'] ?? 0);
            $statusIn = trim($_POST['status'] ?? '');
            $notesIn = trim($_POST['notes'] ?? '');
            $internalNote = trim($_POST['internal_note'] ?? '');
            if ($internalNote !== '') {
                $notesIn .= (strlen($notesIn) ? "\n" : '') . '[Note] ' . $internalNote;
            }

            $payload = [
                'invoice_no' => intval($_POST['invoice_no'] ?? ($_POST['quote_no'] ?? 0)),
                'customer' => trim($_POST['customer'] ?? ''),
                'reference' => trim($_POST['reference'] ?? ''),
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'party_address' => trim($_POST['party_address'] ?? ''),
                'shipping_address' => trim($_POST['shipping_address'] ?? ''),
                'issued_on' => $_POST['issued_on'] ?? date('Y-m-d'),
                'valid_till' => $_POST['valid_till'] ?? null,
                'issued_by' => $issuedByVal,
                'type' => $_POST['type'] ?? 'Invoice',
                'executive' => trim($_POST['executive'] ?? ''),
                'status' => $statusIn !== '' ? $statusIn : ((max(0, $receivedNow) >= floatval($_POST['grand_total'] ?? 0) - 0.005) ? 'Paid' : ((max(0,$receivedNow) > 0) ? 'Partial' : 'Pending')),
                'received_amount' => max(0, $receivedNow),
                'amount' => floatval($_POST['grand_total'] ?? 0),
                'items_json' => $itemsRaw,
                'terms_json' => $_POST['terms_json'] ?? '[]',
                'notes' => $notesIn,
                'extra_charge' => floatval($_POST['extra_charge'] ?? 0),
                'overall_discount' => floatval($_POST['overall_discount'] ?? 0),
                'bank_account_id' => !empty($_POST['bank_account_id']) ? intval($_POST['bank_account_id']) : null,
                'attachment_path' => $attachmentPath,
                'taxable_total' => $taxableTotal,
            ];

            $invoiceModel->create($payload);
            header('Location: /?action=invoices');
            exit();
        } catch (Throwable $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function receive(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }
        try {
            require_once __DIR__ . '/../models/Invoice.php';
            $invoiceModel = new Invoice();
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Invalid id']); return; }
            $addReceived = (float)($_POST['received_amount'] ?? 0);
            $newStatus = trim((string)($_POST['status'] ?? ''));
            $notes = trim((string)($_POST['notes'] ?? ''));
            $res = $invoiceModel->receive($id, $addReceived, $newStatus, $notes);
            header('Content-Type: application/json');
            echo json_encode($res);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function edit(int $id): void {
        try {
            require_once __DIR__ . '/../models/Invoice.php';
            $invoiceModel = new Invoice();
            $invoice = $invoiceModel->findById($id);
            if (!$invoice) { throw new Exception('Invoice not found'); }

            // Inventory picker
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
                    'lead_time' => (int)($i['lead_time'] ?? 0),
                ];
            }, $items);

            // Users
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            $users = $userModel->getAll();

            $invoice = $invoice; // expose as $invoice
            require __DIR__ . '/../views/invoices/edit.php';
        } catch (Throwable $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function update(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=invoices');
            exit();
        }
        try {
            require_once __DIR__ . '/../models/Invoice.php';
            $invoiceModel = new Invoice();

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
                    $safeName = 'invoice_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext? ('.' . preg_replace('/[^a-zA-Z0-9]/','',$ext)) : '');
                    $targetDir = realpath(__DIR__ . '/../../public') . '/uploads/quotations/';
                    if (!is_dir($targetDir)) { @mkdir($targetDir, 0777, true); }
                    $targetPath = $targetDir . $safeName;
                    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                        throw new Exception('Failed to save attachment');
                    }
                    $attachmentPath = '/uploads/quotations/' . $safeName;
                } else if ($err !== UPLOAD_ERR_NO_FILE) {
                    throw new Exception('Upload error');
                }
            }

            $itemsRaw = $_POST['items_json'] ?? '[]';
            $itemsArr = json_decode($itemsRaw, true);
            if (!is_array($itemsArr)) $itemsArr = [];
            $taxableTotal = 0.0; foreach ($itemsArr as $it) { $taxableTotal += (float)($it['taxable'] ?? 0); }

            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $issuedByVal = '';
            if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
                $issuedByVal = trim(($_SESSION['user']['name'] ?? '') ?: ($_SESSION['user']['email'] ?? ''));
            } elseif (!empty($_SESSION['user_name'])) {
                $issuedByVal = (string)$_SESSION['user_name'];
            } elseif (!empty($_SESSION['email'])) {
                $issuedByVal = (string)$_SESSION['email'];
            }

            $payload = [
                'invoice_no' => intval($_POST['invoice_no'] ?? ($_POST['quote_no'] ?? 0)),
                'customer' => trim($_POST['customer'] ?? ''),
                'reference' => trim($_POST['reference'] ?? ''),
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'party_address' => trim($_POST['party_address'] ?? ''),
                'shipping_address' => trim($_POST['shipping_address'] ?? ''),
                'issued_on' => $_POST['issued_on'] ?? date('Y-m-d'),
                'valid_till' => $_POST['valid_till'] ?? null,
                'issued_by' => $issuedByVal,
                'type' => $_POST['type'] ?? 'Invoice',
                'executive' => trim($_POST['executive'] ?? ''),
                'status' => $_POST['status'] ?? 'Pending',
                'received_amount' => floatval($_POST['received_amount'] ?? 0),
                'amount' => floatval($_POST['grand_total'] ?? 0),
                'items_json' => $itemsRaw,
                'terms_json' => $_POST['terms_json'] ?? '[]',
                'notes' => $_POST['notes'] ?? '',
                'extra_charge' => floatval($_POST['extra_charge'] ?? 0),
                'overall_discount' => floatval($_POST['overall_discount'] ?? 0),
                'bank_account_id' => !empty($_POST['bank_account_id']) ? intval($_POST['bank_account_id']) : null,
                'attachment_path' => $attachmentPath,
                'taxable_total' => $taxableTotal,
            ];

            $invoiceModel->update($id, $payload);
            header('Location: /?action=invoices');
            exit();
        } catch (Throwable $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}
