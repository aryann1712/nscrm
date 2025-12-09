<?php
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/CustomerAddress.php';

class CustomersController {
    private Customer $customers;
    private CustomerAddress $addresses;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->customers = new Customer();
        $this->addresses = new CustomerAddress();
    }

    private function json($data, int $code = 200): void {
        // Clear any previous output
        if (ob_get_level()) {
            ob_clean();
        }
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function index(): void {
        // Render list page; data loads via AJAX
        require __DIR__ . '/../views/customers/index.php';
    }

    // JSON: list with filters
    public function list(): void {
        $filters = [
            'type' => $_GET['type'] ?? null,
            'city' => $_GET['city'] ?? null,
            'executive' => $_GET['executive'] ?? null,
            'active' => isset($_GET['active']) ? (int)$_GET['active'] : null,
            'q' => $_GET['q'] ?? null,
        ];
        $this->json($this->customers->getAll($filters));
    }

    public function create(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        $data = $_POST;
        $company = trim($data['company'] ?? '');
        if ($company === '') { $this->json(['error'=>'Company is required'], 400); }
        try {
            // Generate 4-digit pin
            $pin = str_pad(strval(rand(0, 9999)), 4, '0', STR_PAD_LEFT);
            // Save pin as password
            $data['password'] = $pin;
            $email = trim($data['contact_email'] ?? '');
            $contact_name = $data['contact_name'] ?? '';
            $phone = $data['contact_phone'] ?? '';
            // Create customer row as before
            $id = $this->customers->create($data);

            // Also create user so login works - link to current admin as owner
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            $currentOwnerId = (int)($_SESSION['user']['owner_id'] ?? $_SESSION['user']['id'] ?? 0);
            $userModel->createWithPassword([
                'name' => $contact_name ?: $company,
                'email' => $email,
                'phone' => $phone,
                'password' => $pin,
                'company_name' => $company,
                'type' => 'customer',
                'owner_id' => $currentOwnerId, // Link to admin who created this customer
                'email_verified' => 1 // auto-verify customers for login
            ]);

            // Send mail to customer
            require_once __DIR__ . '/../services/EmailService.php';
            $mailer = new EmailService();
            $subject = 'Welcome to NS Technology!';
            $message = "<p>Hello $contact_name,</p><p>Your account has been created.</p><p><strong>Username (email):</strong> $email<br><strong>Password (PIN):</strong> $pin</p><p>Use these details to log in.</p>";
            $mailer->sendHtmlEmail($email, $contact_name, $subject, $message);
            $this->json(['id'=>$id]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to create','detail'=>$e->getMessage()], 500);
        }
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { $this->json(['error'=>'Invalid ID'], 400); }
        try {
            // Merge incoming partial data with existing record to avoid overwriting fields with null/empty values
            $existing = $this->customers->get($id);
            if (!$existing) { $this->json(['error' => 'Customer not found'], 404); }
            $in = $_POST;
            // Build merged payload. Use isset to allow intentional empty strings from forms, else fallback to existing
            $payload = [
                'company' => array_key_exists('company', $in) ? trim((string)$in['company']) : ($existing['company'] ?? ''),
                'contact_name' => array_key_exists('contact_name', $in) ? $in['contact_name'] : ($existing['contact_name'] ?? null),
                'contact_phone' => array_key_exists('contact_phone', $in) ? $in['contact_phone'] : ($existing['contact_phone'] ?? null),
                'contact_email' => array_key_exists('contact_email', $in) ? $in['contact_email'] : ($existing['contact_email'] ?? null),
                'relation' => array_key_exists('relation', $in) ? $in['relation'] : ($existing['relation'] ?? null),
                'website' => array_key_exists('website', $in) ? $in['website'] : ($existing['website'] ?? null),
                'industry_segment' => array_key_exists('industry_segment', $in) ? $in['industry_segment'] : ($existing['industry_segment'] ?? null),
                'country' => array_key_exists('country', $in) ? $in['country'] : ($existing['country'] ?? null),
                'state' => array_key_exists('state', $in) ? $in['state'] : ($existing['state'] ?? null),
                'type' => array_key_exists('type', $in) ? $in['type'] : ($existing['type'] ?? 'customer'),
                'executive' => array_key_exists('executive', $in) ? $in['executive'] : ($existing['executive'] ?? null),
                'city' => array_key_exists('city', $in) ? $in['city'] : ($existing['city'] ?? null),
                'last_talk' => array_key_exists('last_talk', $in) ? $in['last_talk'] : ($existing['last_talk'] ?? null),
                'next_action' => array_key_exists('next_action', $in) ? $in['next_action'] : ($existing['next_action'] ?? null),
                'is_active' => array_key_exists('is_active', $in) ? (int)!!$in['is_active'] : ((int)$existing['is_active'] ?? 1),
            ];
            $ok = $this->customers->update($id, $payload);
            $this->json(['success'=>$ok]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to update','detail'=>$e->getMessage()], 500);
        }
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { $this->json(['error'=>'Invalid ID'], 400); }
        try {
            $ok = $this->customers->delete($id);
            $this->json(['success'=>$ok]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to delete','detail'=>$e->getMessage()], 500);
        }
    }

    // Addresses JSON endpoints
    public function listAddresses(): void {
        $customerId = (int)($_GET['customer_id'] ?? 0);
        if ($customerId <= 0) { $this->json(['error'=>'Invalid customer_id'], 400); }
        $this->json($this->addresses->listByCustomer($customerId));
    }

    public function createAddress(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        $customerId = (int)($_POST['customer_id'] ?? 0);
        if ($customerId <= 0) { $this->json(['error'=>'Invalid customer_id'], 400); }
        try {
            // Validate customer exists and belongs to current owner
            $cust = $this->customers->get($customerId);
            if (!$cust) { $this->json(['error' => 'Customer not found for current owner'], 404); }

            // Sanitize/normalize expected fields
            $in = $_POST;
            $payload = [
                'title' => trim((string)($in['title'] ?? '')) ?: null,
                'line1' => trim((string)($in['line1'] ?? '')) ?: null,
                'line2' => trim((string)($in['line2'] ?? '')) ?: null,
                'city' => trim((string)($in['city'] ?? '')) ?: null,
                'country' => trim((string)($in['country'] ?? '')) ?: null,
                'state' => trim((string)($in['state'] ?? '')) ?: null,
                'pincode' => trim((string)($in['pincode'] ?? '')) ?: null,
                'gstin' => trim((string)($in['gstin'] ?? '')) ?: null,
                'extra_key' => trim((string)($in['extra_key'] ?? '')) ?: null,
                'extra_value' => trim((string)($in['extra_value'] ?? '')) ?: null,
            ];
            $id = $this->addresses->create($customerId, $payload);
            $this->json(['id' => $id]);
        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to create address','detail'=>$e->getMessage()], 500);
        }
    }

    public function updateAddress(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { $this->json(['error'=>'Invalid ID'], 400); }
        try {
            $ok = $this->addresses->update($id, $_POST);
            $this->json(['success' => $ok]);
        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to update address','detail'=>$e->getMessage()], 500);
        }
    }

    public function deleteAddress(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { $this->json(['error'=>'Invalid ID'], 400); }
        try {
            $ok = $this->addresses->delete($id);
            $this->json(['success' => $ok]);
        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to delete address','detail'=>$e->getMessage()], 500);
        }
    }

    public function customerDashboard(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // STRICT CHECK: Only customers can access this dashboard
        if (empty($_SESSION['user'])) {
            header('Location: /?action=auth');
            exit;
        }
        
        // Check if user is a customer by type or by checking is_owner flag
        $userType = $_SESSION['user']['type'] ?? null;
        $isOwner = (int)($_SESSION['user']['is_owner'] ?? 0);
        
        // If not explicitly marked as customer, check if they're not an owner
        if ($userType !== 'customer' && $isOwner === 1) {
            // This is an admin/owner, redirect to admin dashboard
            header('Location: /?action=dashboard');
            exit;
        }
        
        // If type is not set but is_owner is 0, treat as customer (backward compatibility)
        if ($userType === null && $isOwner === 0) {
            // Could be a customer, continue
        } elseif ($userType !== 'customer') {
            // Not a customer, redirect to login
            header('Location: /?action=auth');
            exit;
        }
        
        $userEmail = $_SESSION['user']['email'] ?? '';
        if (empty($userEmail)) {
            // Session is invalid; clear and send to login to avoid redirect loops
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']
                );
            }
            session_destroy();
            header('Location: /?action=auth');
            exit;
        }
        
        // Customer info from customers table - use owner_id from session
        $customer = $this->customers->getByEmail($userEmail);
        
        if (!$customer) {
            // Customer record not found; clear session and redirect to login to prevent loops
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']
                );
            }
            session_destroy();
            header('Location: /?action=auth');
            exit;
        }
        
        $customerInsights = [
            'work_done' => 0,
            'pending_tasks' => 0,
            'last_activity' => '-',
        ];

        // Customer's quotations (by company name) - filtered by owner_id
        require_once __DIR__ . '/../models/Quotation.php';
        $quotationModel = new Quotation();
        $quotationModel->ensureSchema();
        $allQuotations = $quotationModel->getAllFiltered([]);
        $myQuotations = [];
        foreach ($allQuotations as $q) {
            // Match by company name and ensure same owner
            if (isset($q['customer']) && strtolower(trim($q['customer'])) === strtolower(trim($customer['company']))) {
                $myQuotations[] = $q;
            }
        }

        // Customer's invoices (by company name) - filtered by owner_id
        require_once __DIR__ . '/../models/Invoice.php';
        $invoiceModel = new Invoice();
        $allInvoices = $invoiceModel->listFiltered('all', '');
        $myInvoices = [];
        foreach ($allInvoices as $inv) {
            // Match by company name
            if (isset($inv['customer']) && strtolower(trim($inv['customer'])) === strtolower(trim($customer['company']))) {
                $myInvoices[] = $inv;
            }
        }

        // Customer's orders (by customer_id) - already filtered by owner_id in model
        require_once __DIR__ . '/../models/Order.php';
        $orderModel = new Order();
        $allOrders = $orderModel->getAll([]);
        $myOrders = [];
        foreach ($allOrders as $od) {
            if (isset($od['customer_id']) && (int)$od['customer_id'] === (int)$customer['id']) {
                $myOrders[] = $od;
            }
        }

        // Customer addresses - already filtered by customer_id
        $customerAddresses = [];
        if (isset($customer['id'])) {
            $customerAddresses = $this->addresses->listByCustomer($customer['id']);
        }

        // Support section is static/info for now
        require __DIR__ . '/../views/customer_dashboard.php';
    }

    public function updateCustomerDetails(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
            $this->json(['error'=>'Unauthorized'], 401);
        }
        try {
            $userEmail = $_SESSION['user']['email'] ?? '';
            $customer = $this->customers->getByEmail($userEmail);
            if (!$customer) { $this->json(['error' => 'Customer not found'], 404); }
            
            $id = (int)$customer['id'];
            $payload = [
                'contact_name' => trim($_POST['contact_name'] ?? ''),
                'contact_phone' => trim($_POST['contact_phone'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'website' => trim($_POST['website'] ?? ''),
            ];
            $ok = $this->customers->update($id, $payload);
            $this->json(['success'=>$ok]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to update','detail'=>$e->getMessage()], 500);
        }
    }

    public function changePassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
            $this->json(['error'=>'Unauthorized'], 401);
        }
        try {
            $currentPin = trim($_POST['current_password'] ?? '');
            $newPin = trim($_POST['new_password'] ?? '');
            $confirmPin = trim($_POST['confirm_password'] ?? '');
            
            if (strlen($newPin) !== 4 || !preg_match('/^\d{4}$/', $newPin)) {
                $this->json(['error'=>'New password must be exactly 4 digits'], 400);
            }
            
            if ($newPin !== $confirmPin) {
                $this->json(['error'=>'New passwords do not match'], 400);
            }
            
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            $userEmail = $_SESSION['user']['email'] ?? '';
            $user = $userModel->getByEmail($userEmail);
            
            if (!$user) { $this->json(['error' => 'User not found'], 404); }
            
            // Verify current password
            if (!$userModel->verifyPasswordLogin($userEmail, $currentPin)) {
                $this->json(['error'=>'Current password is incorrect'], 400);
            }
            
            // Update password (hash it)
            $hashed = password_hash($newPin, PASSWORD_BCRYPT);
            if (!$userModel->updatePassword((int)$user['id'], $hashed)) {
                $this->json(['error' => 'Unable to update password'], 500);
            }
            
            $this->json(['success'=>true]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to change password','detail'=>$e->getMessage()], 500);
        }
    }

    public function createSupportTicket(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error' => 'Method Not Allowed'], 405); }
        if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        try {
            $userEmail = $_SESSION['user']['email'] ?? '';
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if (!$userEmail || $ownerId <= 0) {
                $this->json(['error' => 'Customer context missing'], 400);
            }

            $customer = $this->customers->getByEmail($userEmail);
            if (!$customer || !isset($customer['id'])) {
                $this->json(['error' => 'Customer not found'], 404);
            }

            $issueType = trim($_POST['issue_type'] ?? '');
            $otherReason = trim($_POST['other_reason'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if ($issueType === '') {
                $this->json(['error' => 'Issue type is required'], 400);
            }

            // If "Other" is selected but no reason provided, treat as validation error
            if ($issueType === 'other' && $otherReason === '') {
                $this->json(['error' => 'Please describe your issue in Other reason'], 400);
            }

            $subject = $issueType === 'other' ? $otherReason : $issueType;
            if ($message === '') {
                $message = $subject;
            }

            require_once __DIR__ . '/../models/SupportTicket.php';
            $ticketModel = new SupportTicket();

            $relatedOrderId = null;
            $relatedOrderNumber = null;
            if ($issueType === 'order_issue') {
                $relatedOrderId = isset($_POST['related_order_id']) ? (int)$_POST['related_order_id'] : null;
                if ($relatedOrderId && $relatedOrderId > 0) {
                    // Try to get order number for display
                    require_once __DIR__ . '/../models/Order.php';
                    $orderModel = new Order();
                    $allOrders = $orderModel->getAll([]);
                    foreach ($allOrders as $od) {
                        if ((int)($od['id'] ?? 0) === $relatedOrderId) {
                            $relatedOrderNumber = $od['order_no'] ?? $od['id'] ?? null;
                            break;
                        }
                    }
                }
            }

            // Optional attachment upload
            $attachmentPath = null;
            if (!empty($_FILES['attachment']) && is_uploaded_file($_FILES['attachment']['tmp_name'])) {
                $err = (int)($_FILES['attachment']['error'] ?? UPLOAD_ERR_OK);
                if ($err === UPLOAD_ERR_OK) {
                    $size = (int)($_FILES['attachment']['size'] ?? 0);
                    if ($size > 10 * 1024 * 1024) {
                        $this->json(['error' => 'Attachment too large (max 10MB)'], 400);
                    }
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['attachment']['tmp_name']);
                    finfo_close($finfo);
                    $allowed = ['application/pdf','image/png','image/jpeg','image/gif','image/webp'];
                    if (!in_array($mime, $allowed, true)) {
                        $this->json(['error' => 'Unsupported attachment type'], 400);
                    }
                    $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                    $safeExt = $ext ? preg_replace('/[^a-zA-Z0-9]/', '', $ext) : '';
                    $safeName = 'support_' . time() . '_' . bin2hex(random_bytes(4)) . ($safeExt ? ('.' . $safeExt) : '');
                    $targetDir = realpath(__DIR__ . '/../../public') . '/uploads/support/';
                    if (!is_dir($targetDir)) {
                        @mkdir($targetDir, 0777, true);
                    }
                    $targetPath = $targetDir . $safeName;
                    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                        $this->json(['error' => 'Failed to save attachment'], 500);
                    }
                    $attachmentPath = '/uploads/support/' . $safeName; // web path
                } elseif ($err !== UPLOAD_ERR_NO_FILE) {
                    $this->json(['error' => 'Upload error'], 400);
                }
            }

            $ticketId = $ticketModel->create([
                'owner_id' => $ownerId,
                'customer_id' => (int)$customer['id'],
                'issue_type' => $issueType,
                'subject' => $subject,
                'message' => $message,
                'status' => 'pending',
                'priority' => 'medium',
                'source' => 'customer_portal',
                'created_by_user_id' => (int)($_SESSION['user']['id'] ?? null),
                'related_order_id' => $relatedOrderId,
                'related_order_number' => $relatedOrderNumber,
                'attachment_path' => $attachmentPath,
            ]);

            $this->json(['success' => true, 'ticket_id' => $ticketId]);
        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to create support ticket', 'detail' => $e->getMessage()], 500);
        }
    }

    public function listSupportTickets(): void {
        // Ensure no output before JSON
        if (ob_get_level()) {
            ob_clean();
        }
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
                $this->json(['error' => 'Unauthorized'], 401);
            }

            $userEmail = $_SESSION['user']['email'] ?? '';
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if (!$userEmail || $ownerId <= 0) {
                $this->json(['error' => 'Customer context missing'], 400);
            }

            $customer = $this->customers->getByEmail($userEmail);
            if (!$customer || !isset($customer['id'])) {
                $this->json(['error' => 'Customer not found'], 404);
            }

            require_once __DIR__ . '/../models/SupportTicket.php';
            $ticketModel = new SupportTicket();
            $tickets = $ticketModel->listByCustomer($ownerId, (int)$customer['id']);

            $this->json(['tickets' => $tickets]);
        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to load tickets', 'detail' => $e->getMessage()], 500);
        }
    }

    public function listTicketMessages(): void {
        // Ensure no output before JSON
        if (ob_get_level()) {
            ob_clean();
        }
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
                $this->json(['error' => 'Unauthorized'], 401);
            }

            $ticketId = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
            if ($ticketId <= 0) {
                $this->json(['error' => 'Invalid ticket_id'], 400);
            }

            $userEmail = $_SESSION['user']['email'] ?? '';
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if (!$userEmail || $ownerId <= 0) {
                $this->json(['error' => 'Customer context missing'], 400);
            }

            $customer = $this->customers->getByEmail($userEmail);
            if (!$customer || !isset($customer['id'])) {
                $this->json(['error' => 'Customer not found'], 404);
            }

            require_once __DIR__ . '/../models/SupportTicket.php';
            $ticketModel = new SupportTicket();
            $ticket = $ticketModel->findById($ownerId, $ticketId);
            if (!$ticket || (int)$ticket['customer_id'] !== (int)$customer['id']) {
                $this->json(['error' => 'Ticket not found'], 404);
            }

            require_once __DIR__ . '/../models/SupportMessage.php';
            $msgModel = new SupportMessage();
            $messages = $msgModel->listByTicket($ownerId, $ticketId);
            $this->json(['messages' => $messages]);
        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to load messages', 'detail' => $e->getMessage()], 500);
        }
    }

    public function addTicketMessage(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error' => 'Method Not Allowed'], 405); }
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
                $this->json(['error' => 'Unauthorized'], 401);
            }

            $ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
            $message  = trim($_POST['message'] ?? '');
            if ($ticketId <= 0 || $message === '') {
                $this->json(['error' => 'Invalid input'], 400);
            }

            $userEmail = $_SESSION['user']['email'] ?? '';
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if (!$userEmail || $ownerId <= 0) {
                $this->json(['error' => 'Customer context missing'], 400);
            }

            $customer = $this->customers->getByEmail($userEmail);
            if (!$customer || !isset($customer['id'])) {
                $this->json(['error' => 'Customer not found'], 404);
            }

            require_once __DIR__ . '/../models/SupportTicket.php';
            $ticketModel = new SupportTicket();
            $ticket = $ticketModel->findById($ownerId, $ticketId);
            if (!$ticket || (int)$ticket['customer_id'] !== (int)$customer['id']) {
                $this->json(['error' => 'Ticket not found'], 404);
            }

            require_once __DIR__ . '/../models/SupportMessage.php';
            $msgModel = new SupportMessage();
            $senderUserId = (int)($_SESSION['user']['id'] ?? 0);
            $id = $msgModel->create($ownerId, $ticketId, 'customer', $senderUserId, $message);

            $this->json(['success' => true, 'id' => $id]);
        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to add message', 'detail' => $e->getMessage()], 500);
        }
    }

    public function printQuotation(int $id): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
            header('Location: /?action=auth');
            exit;
        }
        $userEmail = $_SESSION['user']['email'] ?? '';
        $customer = $this->customers->getByEmail($userEmail);
        if (!$customer) {
            echo 'Customer not found';
            exit;
        }
        require_once __DIR__ . '/../models/Quotation.php';
        $quotationModel = new Quotation();
        $quotationModel->ensureSchema();
        $row = $quotationModel->findById($id);
        if (!$row) {
            echo 'Quotation not found';
            exit;
        }
        if (strtolower(trim($row['customer'] ?? '')) !== strtolower(trim($customer['company'] ?? ''))) {
            echo 'Access denied';
            exit;
        }
        $items = [];
        if (!empty($row['items_json'])) {
            $items = json_decode($row['items_json'], true) ?: [];
        }
        $terms = [];
        if (!empty($row['terms_json'])) {
            $terms = json_decode($row['terms_json'], true) ?: [];
        }
        require_once __DIR__ . '/../models/StoreSetting.php';
        require_once __DIR__ . '/../models/BankAccount.php';
        $store = new StoreSetting();
        $settings = $store->getByKeys(['basic_company','basic_city','basic_state','basic_gstin']);
        $bank = null;
        if (!empty($row['bank_account_id'])) {
            $ba = new BankAccount();
            $bank = $ba->findById((int)$row['bank_account_id']);
        }
        // For customers, always render the HTML print view and let the browser handle
        // "Print" / "Save as PDF". This avoids Dompdf timeouts on shared hosting.
        $forPdf = false;
        include __DIR__ . '/../views/quotations/print.php';
    }

    public function printInvoice(int $id): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
            header('Location: /?action=auth');
            exit;
        }
        $userEmail = $_SESSION['user']['email'] ?? '';
        $customer = $this->customers->getByEmail($userEmail);
        if (!$customer) {
            echo 'Customer not found';
            exit;
        }
        require_once __DIR__ . '/../models/Invoice.php';
        $invoiceModel = new Invoice();
        $pdo = (new Database())->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE owner_id = ? AND id = ? LIMIT 1");
        $stmt->execute([$ownerId, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            echo 'Invoice not found';
            exit;
        }
        if (strtolower(trim($row['customer'] ?? '')) !== strtolower(trim($customer['company'] ?? ''))) {
            echo 'Access denied';
            exit;
        }
        $items = [];
        if (!empty($row['items_json'])) {
            $items = json_decode($row['items_json'], true) ?: [];
        }
        $terms = [];
        if (!empty($row['terms_json'])) {
            $terms = json_decode($row['terms_json'], true) ?: [];
        }
        require_once __DIR__ . '/../models/StoreSetting.php';
        require_once __DIR__ . '/../models/BankAccount.php';
        $store = new StoreSetting();
        $settings = $store->getByKeys(['basic_company','basic_city','basic_state','basic_gstin']);
        $bank = null;
        if (!empty($row['bank_account_id'])) {
            $ba = new BankAccount();
            $bank = $ba->findById((int)$row['bank_account_id']);
        }
        // For customers, avoid Dompdf and always render HTML view; browser handles PDF.
        $forPdf = false;
        include __DIR__ . '/../views/invoices/print.php';
    }

    public function getQuotationDetails(int $id): void {
        // Ensure no output before JSON
        if (ob_get_level()) {
            ob_clean();
        }
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
                $this->json(['error'=>'Unauthorized'], 401);
                return;
            }
            $userEmail = $_SESSION['user']['email'] ?? '';
            $customer = $this->customers->getByEmail($userEmail);
            if (!$customer) {
                $this->json(['error'=>'Customer not found'], 404);
                return;
            }
            require_once __DIR__ . '/../models/Quotation.php';
            $quotationModel = new Quotation();
            $quotationModel->ensureSchema();
            $row = $quotationModel->findById($id);
            if (!$row) {
                $this->json(['error'=>'Quotation not found'], 404);
                return;
            }
            if (strtolower(trim($row['customer'] ?? '')) !== strtolower(trim($customer['company'] ?? ''))) {
                $this->json(['error'=>'Access denied'], 403);
                return;
            }
            $items = [];
            if (!empty($row['items_json'])) {
                $items = json_decode($row['items_json'], true) ?: [];
            }
            $terms = [];
            if (!empty($row['terms_json'])) {
                $terms = json_decode($row['terms_json'], true) ?: [];
            }
            $this->json([
                'quotation' => $row,
                'items' => $items,
                'terms' => $terms
            ]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to load quotation: ' . $e->getMessage()], 500);
        }
    }

    public function getInvoiceDetails(int $id): void {
        // Ensure no output before JSON
        if (ob_get_level()) {
            ob_clean();
        }
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
                $this->json(['error'=>'Unauthorized'], 401);
                return;
            }
            $userEmail = $_SESSION['user']['email'] ?? '';
            $customer = $this->customers->getByEmail($userEmail);
            if (!$customer) {
                $this->json(['error'=>'Customer not found'], 404);
                return;
            }
            require_once __DIR__ . '/../models/Invoice.php';
            require_once __DIR__ . '/../config/database.php';
            $pdo = (new Database())->getConnection();
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM invoices WHERE owner_id = ? AND id = ? LIMIT 1");
            $stmt->execute([$ownerId, $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $this->json(['error'=>'Invoice not found'], 404);
                return;
            }
            if (strtolower(trim($row['customer'] ?? '')) !== strtolower(trim($customer['company'] ?? ''))) {
                $this->json(['error'=>'Access denied'], 403);
                return;
            }
            $items = [];
            if (!empty($row['items_json'])) {
                $items = json_decode($row['items_json'], true) ?: [];
            }
            $terms = [];
            if (!empty($row['terms_json'])) {
                $terms = json_decode($row['terms_json'], true) ?: [];
            }
            $this->json([
                'invoice' => $row,
                'items' => $items,
                'terms' => $terms
            ]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to load invoice: ' . $e->getMessage()], 500);
        }
    }

    public function getOrderDetails(int $id): void {
        // Ensure no output before JSON
        if (ob_get_level()) {
            ob_clean();
        }
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
                $this->json(['error'=>'Unauthorized'], 401);
                return;
            }
            $userEmail = $_SESSION['user']['email'] ?? '';
            $customer = $this->customers->getByEmail($userEmail);
            if (!$customer || !isset($customer['id'])) {
                $this->json(['error'=>'Customer not found'], 404);
                return;
            }
            require_once __DIR__ . '/../config/database.php';
            $pdo = (new Database())->getConnection();
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $stmt = $pdo->prepare('SELECT o.*, c.company AS customer_name FROM orders o LEFT JOIN customers c ON c.id = o.customer_id AND c.owner_id = o.owner_id WHERE o.owner_id = ? AND o.id = ?');
            $stmt->execute([$ownerId, $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $this->json(['error'=>'Order not found'], 404);
                return;
            }
            if ((int)($row['customer_id'] ?? 0) !== (int)$customer['id']) {
                $this->json(['error'=>'Access denied'], 403);
                return;
            }
            $itemsStmt = $pdo->prepare('SELECT id, item_name, qty, done_qty, unit, rate, amount FROM order_items WHERE owner_id = ? AND order_id = ? ORDER BY id ASC');
            $itemsStmt->execute([$ownerId, $id]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
            $termsStmt = $pdo->prepare('SELECT term_text FROM order_terms WHERE owner_id = ? AND order_id = ? ORDER BY display_order ASC');
            $termsStmt->execute([$ownerId, $id]);
            $termsRows = $termsStmt->fetchAll(PDO::FETCH_ASSOC);
            $terms = array_column($termsRows, 'term_text');
            $this->json([
                'order' => $row,
                'items' => $items,
                'terms' => $terms
            ]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to load order: ' . $e->getMessage()], 500);
        }
    }

    public function printOrder(int $id): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
            header('Location: /?action=auth');
            exit;
        }
        $userEmail = $_SESSION['user']['email'] ?? '';
        $customer = $this->customers->getByEmail($userEmail);
        if (!$customer || !isset($customer['id'])) {
            echo 'Customer not found';
            exit;
        }
        require_once __DIR__ . '/../models/Order.php';
        $pdo = (new Database())->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT o.*, c.company AS customer_name FROM orders o LEFT JOIN customers c ON c.id = o.customer_id AND c.owner_id = o.owner_id WHERE o.owner_id = ? AND o.id = ?');
        $stmt->execute([$ownerId, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            echo 'Order not found';
            exit;
        }
        $itemsStmt = $pdo->prepare('SELECT id, item_name, qty, done_qty, unit, rate, amount FROM order_items WHERE owner_id = ? AND order_id = ? ORDER BY id ASC');
        $itemsStmt->execute([$ownerId, $id]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        if ((int)($row['customer_id'] ?? 0) !== (int)$customer['id']) {
            echo 'Access denied';
            exit;
        }
        $termsStmt = $pdo->prepare('SELECT term_text FROM order_terms WHERE owner_id = ? AND order_id = ? ORDER BY display_order ASC');
        $termsStmt->execute([$ownerId, $id]);
        $termsRows = $termsStmt->fetchAll(PDO::FETCH_ASSOC);
        $terms = array_column($termsRows, 'term_text');
        require_once __DIR__ . '/../models/StoreSetting.php';
        $store = new StoreSetting();
        $settings = $store->getByKeys(['basic_company','basic_city','basic_state','basic_gstin']);
        $bank = null;
        // For customers, avoid Dompdf and always render HTML view; browser handles PDF.
        $forPdf = false;
        include __DIR__ . '/../views/orders/print.php';
    }
}
