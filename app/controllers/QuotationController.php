<?php
require_once __DIR__ . '/../models/Quotation.php';

class QuotationController {
    public function index() {
        try {
            $quotationModel = new Quotation();

            $tab = $_GET['tab'] ?? 'quotations'; // quotations | proforma | all
            $period = $_GET['period'] ?? 'this_month'; // today|this_week|this_month|all
            $search = trim($_GET['q'] ?? '');

            $filters = [
                'tab' => $tab,
                'period' => $period,
                'search' => $search,
            ];

            $stats = $quotationModel->getStats($filters);
            $rows = $quotationModel->getAllFiltered($filters);

            require __DIR__ . '/../views/quotations/index.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    // Return Customers or Leads for the contact picker (JSON)
    public function listContacts() {
        header('Content-Type: application/json');
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $type = strtolower(trim($_GET['type'] ?? 'customer'));
            $q = trim($_GET['q'] ?? '');
            if ($type !== 'lead') { $type = 'customer'; }
            $out = [];
            if ($type === 'customer') {
                try {
                    require_once __DIR__ . '/../models/Customer.php';
                    $m = new Customer();
                    $rows = $m->getAll(['q' => $q]);
                } catch (Throwable $e) {
                    // Fallback: direct DB query without owner filter (read-only, limited)
                    require_once __DIR__ . '/../config/database.php';
                    $db = new Database(); $pdo = $db->getConnection();
                    $like = '%' . $q . '%';
                    $st = $pdo->prepare("SELECT id, company, contact_name, city FROM customers WHERE company LIKE ? OR contact_name LIKE ? ORDER BY company ASC LIMIT 25");
                    $st->execute([$like, $like]);
                    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
                }
                foreach ($rows as $r) {
                    $out[] = [
                        'id' => (int)($r['id'] ?? 0),
                        'label' => (string)($r['company'] ?? ''),
                        'sublabel' => trim((string)($r['contact_name'] ?? '')),
                        'city' => (string)($r['city'] ?? ''),
                        'type' => 'customer',
                    ];
                }
            } else {
                try {
                    require_once __DIR__ . '/../models/Lead.php';
                    $m = new Lead();
                    $rows = $m->getAllFiltered(['search' => $q]);
                } catch (Throwable $e) {
                    // Fallback: direct DB query without owner filter (read-only, limited)
                    require_once __DIR__ . '/../config/database.php';
                    $db = new Database(); $pdo = $db->getConnection();
                    $like = '%' . $q . '%';
                    $st = $pdo->prepare("SELECT id, business_name, contact_person, city FROM leads WHERE business_name LIKE ? OR contact_person LIKE ? ORDER BY business_name ASC LIMIT 25");
                    $st->execute([$like, $like]);
                    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
                }
                foreach ($rows as $r) {
                    $out[] = [
                        'id' => (int)($r['id'] ?? 0),
                        'label' => (string)($r['business_name'] ?? ''),
                        'sublabel' => trim((string)($r['contact_person'] ?? '')),
                        'city' => (string)($r['city'] ?? ''),
                        'type' => 'lead',
                    ];
                }
            }
            echo json_encode($out);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Return one contact detail for autofill (JSON)
    public function getContact() {
        header('Content-Type: application/json');
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $type = strtolower(trim($_GET['type'] ?? 'customer'));
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) { http_response_code(400); echo json_encode(['error' => 'Invalid id']); return; }
            if ($type !== 'lead') { $type = 'customer'; }
            if ($type === 'customer') {
                require_once __DIR__ . '/../models/Customer.php';
                $m = new Customer();
                $c = $m->get($id);
                if (!$c) { http_response_code(404); echo json_encode(['error' => 'Not found']); return; }

                // Try to use latest saved address from Manage Addresses (no GST in address lines)
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
                    // fall back silently if address table/model not available
                }
                if ($party === '') {
                    // Fallback to basic location if no address rows
                    $party = trim(implode("\n", array_filter([
                        (string)($c['city'] ?? ''),
                        (string)($c['state'] ?? ''),
                        (string)($c['country'] ?? ''),
                    ])));
                }

                echo json_encode([
                    'customer' => (string)($c['company'] ?? ''),
                    'contact_person' => (string)($c['contact_name'] ?? ''),
                    'party_address' => $party,
                    'shipping_address' => $party,
                    'addresses' => $addresses,
                ]);
                return;
            }
            // lead
            require_once __DIR__ . '/../models/Lead.php';
            $m = new Lead();
            $l = $m->get($id);
            if (!$l) { http_response_code(404); echo json_encode(['error' => 'Not found']); return; }
            // Party address should NOT include business or contact names
            $party = trim(implode("\n", array_filter([
                trim(((string)($l['address_line1'] ?? '') . ' ' . (string)($l['address_line2'] ?? ''))),
                (string)($l['city'] ?? ''),
                (string)($l['state'] ?? ''),
                (string)($l['country'] ?? ''),
            ])));
            echo json_encode([
                'customer' => (string)($l['business_name'] ?? ''),
                'contact_person' => (string)($l['contact_person'] ?? ''),
                'party_address' => $party,
                'shipping_address' => $party,
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function convertToInvoice(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }
        header('Content-Type: application/json');
        try {
            $quotationModel = new Quotation();
            $quotationModel->ensureSchema();
            $ok = $quotationModel->convertToInvoice($id);
            echo json_encode(['success' => (bool)$ok]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Generate PDF (uses Dompdf if installed via composer: composer require dompdf/dompdf)
    public function pdf(int $id) {
        try {
            $quotationModel = new Quotation();
            $quotationModel->ensureSchema();
            $row = $quotationModel->findById($id);
            if (!$row) { echo 'Quotation not found'; return; }
            $items = [];
            if (!empty($row['items_json'])) { $items = json_decode($row['items_json'], true) ?: []; }
            $terms = [];
            if (!empty($row['terms_json'])) { $terms = json_decode($row['terms_json'], true) ?: []; }
            // Load store settings (company info) and selected bank (for print footer)
            require_once __DIR__ . '/../models/StoreSetting.php';
            require_once __DIR__ . '/../models/BankAccount.php';
            $store = new StoreSetting();
            $settings = $store->getByKeys(['basic_company','basic_city','basic_state','basic_gstin']);
            $bank = null;
            if (!empty($row['bank_account_id'])) {
                $ba = new BankAccount();
                $bank = $ba->findById((int)$row['bank_account_id']);
            }

            // Render HTML using the same print view but mark as PDF render to avoid external assets
            $forPdf = true;
            ob_start();
            include __DIR__ . '/../views/quotations/print.php';
            $html = ob_get_clean();

            // If Dompdf exists, stream PDF
            if (class_exists('Dompdf\\Dompdf')) {
                $options = new \Dompdf\Options();
                $options->set('isRemoteEnabled', true);
                $options->set('isHtml5ParserEnabled', true);
                $options->set('defaultFont', 'DejaVu Sans');
                $dompdf = new \Dompdf\Dompdf($options);
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $fname = 'Quotation_' . ($row['quote_no'] ?? $id) . '.pdf';
                $dompdf->stream($fname, ['Attachment' => true]);
                return;
            }

            // Fallback: show HTML with note
            header('Content-Type: text/html; charset=UTF-8');
            echo '<div style="position:fixed;top:8px;left:8px;right:8px;padding:8px;background:#fff3cd;border:1px solid #ffeeba;color:#856404;font:14px/1.4 sans-serif;z-index:99999">'
                . 'PDF generator not installed. Run <code>composer require dompdf/dompdf</code> to enable direct PDF download. You can still use the browser\'s Print &gt; Save as PDF.'
                . '</div>';
            echo $html;
        } catch (Throwable $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateStatus(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }
        try {
            $status = trim($_POST['status'] ?? '');
            $allowed = ['Open','Expired','Cancelled','Rejected','Replaced','Converted'];
            if (!in_array($status, $allowed, true)) {
                http_response_code(400);
                echo 'Invalid status';
                return;
            }
            $quotationModel = new Quotation();
            $quotationModel->ensureTable();
            $quotationModel->ensureSchema();
            $ok = $quotationModel->updateStatus($id, $status);
            header('Content-Type: application/json');
            echo json_encode(['success' => (bool)$ok]);
        } catch (Exception $e) {
            http_response_code(500);
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function create() {
        try {
            $quotationModel = new Quotation();
            $quotationModel->ensureTable();
            $quotationModel->ensureSchema();

            // Prefill next quote number
            $nextNo = $quotationModel->getNextQuoteNo();

            // Get inventory items for client-side picker (limited fields)
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

            // Users for Executive dropdown (scope to current owner)
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $users = $ownerId > 0 ? $userModel->getByOwner($ownerId) : [];

            // Determine issued_by from session (fallback to empty)
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $issuedBy = '';
            if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
                $issuedBy = trim(($_SESSION['user']['name'] ?? '') ?: ($_SESSION['user']['email'] ?? ''));
            } elseif (!empty($_SESSION['user_name'])) {
                $issuedBy = (string)$_SESSION['user_name'];
            } elseif (!empty($_SESSION['email'])) {
                $issuedBy = (string)$_SESSION['email'];
            }

            // Optional prefill from query params (e.g., coming from a Lead quick action)
            $customer_q = trim($_GET['customer'] ?? '');
            $reference_q = trim($_GET['reference'] ?? '');
            $contact_person_q = trim($_GET['contact_person'] ?? '');
            $type_q = $_GET['type'] ?? 'Quotation';
            $type_q = in_array($type_q, ['Quotation','Proforma'], true) ? $type_q : 'Quotation';
            $prefill = [
                'customer' => $customer_q,
                'reference' => $reference_q,
                'contact_person' => $contact_person_q,
                'type' => $type_q,
            ];

            require __DIR__ . '/../views/quotations/create.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function edit(int $id) {
        try {
            $quotationModel = new Quotation();
            $quotationModel->ensureTable();
            $quotationModel->ensureSchema();

            $q = $quotationModel->findById($id);
            if (!$q) { throw new Exception('Quotation not found'); }

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

            // Users for Executive dropdown in edit view (scope to current owner)
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $users = $ownerId > 0 ? $userModel->getByOwner($ownerId) : [];

            // Determine issued_by from session for display override in edit view
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $issuedBy = '';
            if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
                $issuedBy = trim(($_SESSION['user']['name'] ?? '') ?: ($_SESSION['user']['email'] ?? ''));
            } elseif (!empty($_SESSION['user_name'])) {
                $issuedBy = (string)$_SESSION['user_name'];
            } elseif (!empty($_SESSION['email'])) {
                $issuedBy = (string)$_SESSION['email'];
            }

            require __DIR__ . '/../views/quotations/edit.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=quotations');
            exit();
        }

        try {
            $quotationModel = new Quotation();
            $quotationModel->ensureTable();
            $quotationModel->ensureSchema();

            // Handle optional attachment upload
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
                    $safeName = 'quote_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext? ('.' . preg_replace('/[^a-zA-Z0-9]/','',$ext)) : '');
                    $targetDir = realpath(__DIR__ . '/../../public') . '/uploads/quotations/';
                    if (!is_dir($targetDir)) { @mkdir($targetDir, 0777, true); }
                    $targetPath = $targetDir . $safeName;
                    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                        throw new Exception('Failed to save attachment');
                    }
                    $attachmentPath = '/uploads/quotations/' . $safeName; // web path
                } else if ($err !== UPLOAD_ERR_NO_FILE) {
                    throw new Exception('Upload error');
                }
            }

            // Compute issued_by from session (authoritative)
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
                'quote_no' => intval($_POST['quote_no']),
                'customer' => trim($_POST['customer'] ?? ''),
                'reference' => trim($_POST['reference'] ?? ''),
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'party_address' => trim($_POST['party_address'] ?? ''),
                'shipping_address' => trim($_POST['shipping_address'] ?? ''),
                'issued_on' => $_POST['issued_on'] ?? date('Y-m-d'),
                'valid_till' => $_POST['valid_till'] ?? null,
                'issued_by' => $issuedByVal,
                'type' => $_POST['type'] ?? 'Quotation',
                'executive' => trim($_POST['executive'] ?? ''),
                'response' => '',
                'amount' => floatval($_POST['grand_total'] ?? 0),
                'items_json' => $_POST['items_json'] ?? '[]',
                'terms_json' => $_POST['terms_json'] ?? '[]',
                'notes' => $_POST['notes'] ?? '',
                'extra_charge' => floatval($_POST['extra_charge'] ?? 0),
                'overall_discount' => floatval($_POST['overall_discount'] ?? 0),
                'overall_gst_pct' => floatval($_POST['overall_gst_pct'] ?? 0),
                'bank_account_id' => !empty($_POST['bank_account_id']) ? intval($_POST['bank_account_id']) : null,
                'attachment_path' => $attachmentPath,
            ];

            $quotationModel->create($payload);
            header('Location: /?action=quotations');
            exit();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function update(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=quotations');
            exit();
        }

        try {
            $quotationModel = new Quotation();
            $quotationModel->ensureTable();
            $quotationModel->ensureSchema();

            // Optional replace attachment
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
                    $safeName = 'quote_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext? ('.' . preg_replace('/[^a-zA-Z0-9]/','',$ext)) : '');
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

            // Compute issued_by from session (authoritative)
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
                'quote_no' => intval($_POST['quote_no']),
                'customer' => trim($_POST['customer'] ?? ''),
                'reference' => trim($_POST['reference'] ?? ''),
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'party_address' => trim($_POST['party_address'] ?? ''),
                'shipping_address' => trim($_POST['shipping_address'] ?? ''),
                'issued_on' => $_POST['issued_on'] ?? date('Y-m-d'),
                'valid_till' => $_POST['valid_till'] ?? null,
                'issued_by' => $issuedByVal,
                'type' => $_POST['type'] ?? 'Quotation',
                'executive' => trim($_POST['executive'] ?? ''),
                'response' => '',
                'amount' => floatval($_POST['grand_total'] ?? 0),
                'items_json' => $_POST['items_json'] ?? '[]',
                'terms_json' => $_POST['terms_json'] ?? '[]',
                'notes' => $_POST['notes'] ?? '',
                'extra_charge' => floatval($_POST['extra_charge'] ?? 0),
                'overall_discount' => floatval($_POST['overall_discount'] ?? 0),
                'overall_gst_pct' => floatval($_POST['overall_gst_pct'] ?? 0),
                'bank_account_id' => !empty($_POST['bank_account_id']) ? intval($_POST['bank_account_id']) : null,
                'attachment_path' => $attachmentPath, // null means keep existing
            ];

            $quotationModel->update($id, $payload);
            header('Location: /?action=quotations');
            exit();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function delete(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }
        try {
            $quotationModel = new Quotation();
            $quotationModel->ensureTable();
            $quotationModel->ensureSchema();
            $ok = $quotationModel->delete($id);
            header('Content-Type: application/json');
            echo json_encode(['success' => (bool)$ok]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function sendEmail(int $id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }
        header('Content-Type: application/json');
        try {
            $toEmail = trim($_POST['email'] ?? '');
            if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid email']);
                return;
            }
            $quotationModel = new Quotation();
            $quotation = $quotationModel->findById($id);
            if (!$quotation) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Quotation not found']);
                return;
            }
            require_once __DIR__ . '/../services/EmailService.php';
            $mailer = new EmailService();
            $subject = 'Quotation Reminder #' . htmlspecialchars((string)($quotation['quote_no'] ?? $id));
            $customer = htmlspecialchars((string)($quotation['customer'] ?? 'Customer'));
            $amount = number_format((float)($quotation['amount'] ?? 0), 2);
            $issuedOn = htmlspecialchars((string)($quotation['issued_on'] ?? ''));
            $validTill = htmlspecialchars((string)($quotation['valid_till'] ?? ''));
            $type = htmlspecialchars((string)($quotation['type'] ?? 'Quotation'));
            $link = sprintf('%s/?action=quotations&subaction=edit&id=%d', rtrim((isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : (isset($_SERVER['HTTP_HOST'])? ('http://' . $_SERVER['HTTP_HOST']) : '')), '/'), (int)$id);
            $html = "<html><body>"
                  . "<p>Dear {$customer},</p>"
                  . "<p>This is a friendly reminder regarding your {$type}.</p>"
                  . "<ul>"
                  . "<li><strong>Quotation No:</strong> " . htmlspecialchars((string)$quotation['quote_no']) . "</li>"
                  . "<li><strong>Date:</strong> {$issuedOn}</li>"
                  . "<li><strong>Valid till:</strong> {$validTill}</li>"
                  . "<li><strong>Amount:</strong> ₹ {$amount}</li>"
                  . "</ul>"
                  . "<p>You may review the quotation here: <a href='{$link}'>{$link}</a></p>"
                  . "<p>Regards,<br>NS Technology</p>"
                  . "</body></html>";
            $ok = $mailer->sendHtmlEmail($toEmail, $customer, $subject, $html);
            echo json_encode(['success' => (bool)$ok]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Printable/PDF view
    public function print(int $id) {
        try {
            $quotationModel = new Quotation();
            $quotationModel->ensureSchema();
            $row = $quotationModel->findById($id);
            if (!$row) { echo 'Quotation not found'; return; }
            // Provide decoded arrays to the view
            $items = [];
            if (!empty($row['items_json'])) {
                $items = json_decode($row['items_json'], true) ?: [];
            }
            $terms = [];
            if (!empty($row['terms_json'])) {
                $terms = json_decode($row['terms_json'], true) ?: [];
            }
            // Load store settings for header details and bank info
            require_once __DIR__ . '/../models/StoreSetting.php';
            require_once __DIR__ . '/../models/BankAccount.php';
            $store = new StoreSetting();
            $settings = $store->getByKeys(['basic_company','basic_city','basic_state','basic_gstin']);
            $bank = null;
            if (!empty($row['bank_account_id'])) {
                $ba = new BankAccount();
                $bank = $ba->findById((int)$row['bank_account_id']);
            }
            include __DIR__ . '/../views/quotations/print.php';
        } catch (Throwable $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}
?>

