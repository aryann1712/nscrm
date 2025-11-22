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
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
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

            // Also create user so login works
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            $userModel->createWithPassword([
                'name' => $contact_name ?: $company,
                'email' => $email,
                'phone' => $phone,
                'password' => $pin,
                'company_name' => $company,
                'type' => 'customer', // for session
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
        if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? null) !== 'customer') {
            header('Location: /?action=auth');
            exit;
        }
        $userEmail = $_SESSION['user']['email'] ?? '';
        // Customer info from customers table
        $customer = $this->customers->getByEmail($userEmail);
        $customerInsights = [
            'work_done' => 0,
            'pending_tasks' => 0,
            'last_activity' => '-',
        ];

        // Customer's quotations (by company name)
        require_once __DIR__ . '/../models/Quotation.php';
        $quotationModel = new Quotation();
        $myQuotations = $customer ? $quotationModel->getAllFiltered(['search'=>$customer['company']]) : [];

        // Customer's invoices (by company name)
        require_once __DIR__ . '/../models/Invoice.php';
        $invoiceModel = new Invoice();
        $myInvoices = $customer ? $invoiceModel->listFiltered('all', $customer['company']) : [];

        // Customer's orders (by customer_id)
        require_once __DIR__ . '/../models/Order.php';
        $orderModel = new Order();
        $myOrders = [];
        if ($customer && isset($customer['id'])) {
            $allOrders = $orderModel->getAll([]);
            foreach ($allOrders as $od) {
                if (isset($od['customer_id']) && $od['customer_id'] == $customer['id']) {
                    $myOrders[] = $od;
                }
            }
        }

        // Customer addresses
        $customerAddresses = [];
        if ($customer && isset($customer['id'])) {
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
                'contact_email' => trim($_POST['contact_email'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'website' => trim($_POST['website'] ?? ''),
            ];
            $ok = $this->customers->update($id, $payload);
            
            // Also update user email if changed
            if ($payload['contact_email'] && $payload['contact_email'] !== $userEmail) {
                require_once __DIR__ . '/../models/User.php';
                $userModel = new User();
                $user = $userModel->getByEmail($userEmail);
                if ($user) {
                    $userModel->update($user['id'], ['email' => $payload['contact_email']]);
                    $_SESSION['user']['email'] = $payload['contact_email'];
                }
            }
            
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
            $userModel->update($user['id'], ['password' => $hashed]);
            
            $this->json(['success'=>true]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to change password','detail'=>$e->getMessage()], 500);
        }
    }
}
