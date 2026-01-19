<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/EmailService.php';

class SettingsController {
    private $user;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->user = new User();
    }
    
    private function requireOwner(): void {
        if (empty($_SESSION['user']['id'])) {
            header('Location: /?action=auth');
            exit();
        }
        if ((int)($_SESSION['user']['is_owner'] ?? 0) !== 1) {
            header('Location: /?action=dashboard&error=forbidden');
            exit();
        }
    }
    
    public function index() {
        // Allow any authenticated user to view Settings page
        if (empty($_SESSION['user']['id'])) {
            header('Location: /?action=auth');
            exit();
        }
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $pdo = (new Database())->getConnection();
        // IMPORTANT: Only show company employees (users), NOT customers
        // Customers have type='customer', employees have type IS NULL or type != 'customer'
        $stmt = $pdo->prepare("SELECT id, name, email, phone, is_owner, email_verified, type FROM users WHERE owner_id = ? AND (type IS NULL OR type != 'customer') ORDER BY is_owner DESC, id ASC");
        $stmt->execute([$ownerId]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalUsers = is_array($users) ? count($users) : 0;
        $maxUsers = 10; // Maximum allowed users (employees only)
        
        require_once __DIR__ . '/../views/settings/index.php';
    }
    
    public function create() {
        if (empty($_SESSION['user']['id'])) { header('Location: /?action=auth'); exit(); }
        require_once __DIR__ . '/../views/settings/create.php';
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=settings');
            exit();
        }
        if (empty($_SESSION['user']['id'])) { header('Location: /?action=auth'); exit(); }
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        
        try {
            $user = new User();
            $emailService = new EmailService();
            
            // Validate required fields
            if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['phone']) || empty($_POST['password'])) {
                throw new Exception("All fields are required.");
            }
            
            // Check if email already exists within this tenant (check both employees and customers)
            $pdo = (new Database())->getConnection();
            $chk = $pdo->prepare('SELECT id FROM users WHERE owner_id = ? AND email = ? LIMIT 1');
            $chk->execute([$ownerId, trim($_POST['email'])]);
            $existingUser = $chk->fetch(PDO::FETCH_ASSOC);
            if ($existingUser) {
                throw new Exception("Email address already exists.");
            }
            
            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                // Pass plain password; model will hash
                'password' => (string)$_POST['password'],
                'type' => null // Employee (not a customer)
            ];
            
            // Create sub-user (employee) under current owner; mark email as verified for immediate access
            // Note: createSubUser doesn't set type, so we need to ensure it's not a customer
            $newUserId = $user->createSubUser($data, $ownerId, true);
            
            // Ensure the new user is NOT marked as customer
            if ($newUserId) {
                $pdo->prepare("UPDATE users SET type = NULL WHERE id = ? AND (type = 'customer' OR type IS NULL)")->execute([$newUserId]);
            }
            
            if ($newUserId) {
                $_SESSION['success_message'] = "User created successfully!";
                header('Location: /?action=settings');
                exit();
            } else {
                throw new Exception("Failed to create user.");
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            header('Location: /?action=settings&subaction=create');
            exit();
        }
    }
    
    public function edit() {
        if (empty($_SESSION['user']['id'])) { header('Location: /?action=auth'); exit(); }
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: /?action=settings');
            exit();
        }
        
        $pdo = (new Database())->getConnection();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        // Only allow editing employees, not customers
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND owner_id = ? AND (type IS NULL OR type != 'customer') LIMIT 1");
        $stmt->execute([$id, $ownerId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            header('Location: /?action=settings');
            exit();
        }
        
        require_once __DIR__ . '/../views/settings/edit.php';
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=settings');
            exit();
        }
        if (empty($_SESSION['user']['id'])) { header('Location: /?action=auth'); exit(); }
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        
        try {
            $id = $_POST['id'];
            // Ensure the target user belongs to this owner AND is an employee (not a customer)
            $pdo = (new Database())->getConnection();
            $chk = $pdo->prepare("SELECT id, is_owner FROM users WHERE id = ? AND owner_id = ? AND (type IS NULL OR type != 'customer')");
            $chk->execute([$id, $ownerId]);
            $target = $chk->fetch(PDO::FETCH_ASSOC);
            if (!$target) {
                throw new Exception('User not found or cannot edit customers here');
            }
            $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone']
            ];
            
            // Only update password if provided
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            $this->user->update($id, $data);
            header('Location: /?action=settings');
            exit();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    public function delete() {
        // Only owner (admin) can delete, and cannot delete owner account
        if (empty($_SESSION['user']['id'])) { header('Location: /?action=auth'); exit(); }
        if ((int)($_SESSION['user']['is_owner'] ?? 0) !== 1) { header('Location: /?action=settings'); exit(); }
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /?action=settings'); exit(); }
        try {
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $pdo = (new Database())->getConnection();
            // Only allow deleting employees, not customers (customers should be deleted from Customers section)
            $chk = $pdo->prepare("SELECT id, is_owner FROM users WHERE id = ? AND owner_id = ? AND (type IS NULL OR type != 'customer')");
            $chk->execute([$id, $ownerId]);
            $target = $chk->fetch(PDO::FETCH_ASSOC);
            if (!$target) { throw new Exception('User not found or cannot delete customers here'); }
            if ((int)($target['is_owner'] ?? 0) === 1) { throw new Exception('Cannot delete the owner account'); }
            $this->user->delete((int)$id);
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Delete failed: ' . $e->getMessage();
        }
        header('Location: /?action=settings');
        exit();
    }
    
    public function verifyEmail() {
        $email = $_GET['email'] ?? null;
        $pin = $_GET['pin'] ?? null;
        
        if (!$email || !$pin) {
            $_SESSION['error_message'] = "Invalid verification link.";
            header('Location: /?action=settings');
            exit();
        }
        
        try {
            $user = new User();
            $emailService = new EmailService();
            
            $verified = $user->verifyEmail($email, $pin);
            
            if ($verified) {
                // Send welcome email
                $userData = $user->getByEmail($email);
                $emailService->sendWelcomeEmail($email, $userData['name']);
                
                $_SESSION['success_message'] = "Email verified successfully! Welcome email sent.";
            } else {
                $_SESSION['error_message'] = "Invalid or expired verification code.";
            }
            
            header('Location: /?action=settings');
            exit();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            header('Location: /?action=settings');
            exit();
        }
    }
    
    public function resendVerification() {
        $email = $_POST['email'] ?? null;
        
        if (!$email) {
            $_SESSION['error_message'] = "Email address is required.";
            header('Location: /?action=settings');
            exit();
        }
        
        try {
            $user = new User();
            $emailService = new EmailService();
            
            $result = $user->resendVerificationPin($email);
            
            if ($result) {
                $emailSent = $emailService->sendVerificationEmail(
                    $email,
                    $user->getByEmail($email)['name'],
                    $result['pin'],
                    $result['expires_at']
                );
                
                if ($emailSent) {
                    $_SESSION['success_message'] = "New verification code sent to {$email}";
                } else {
                    $_SESSION['warning_message'] = "Verification code generated but email could not be sent.";
                }
            } else {
                $_SESSION['error_message'] = "Email address not found.";
            }
            
            header('Location: /?action=settings');
            exit();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            header('Location: /?action=settings');
            exit();
        }
    }

    public function salesConfiguration() {
        require_once __DIR__ . '/../views/settings/sales_configuration.php';
    }

    // AJAX: GET /?action=settings&subaction=getRights&user_id=123
    public function getRights(): void {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $this->requireOwner();
        header('Content-Type: application/json');
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId <= 0 || $ownerId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user']);
            return;
        }
        try {
            $pdo = (new Database())->getConnection();
            if (!$pdo) { throw new Exception('DB connection failed'); }
            // Ensure target user belongs to this owner
            $chk = $pdo->prepare('SELECT id FROM users WHERE id = ? AND owner_id = ? LIMIT 1');
            $chk->execute([$userId, $ownerId]);
            if (!$chk->fetch(PDO::FETCH_ASSOC)) {
                echo json_encode(['success' => false, 'error' => 'User not found in this workspace']);
                return;
            }
            $key = 'user_rights:' . $userId;
            $stmt = $pdo->prepare('SELECT svalue FROM store_settings WHERE owner_id = ? AND skey = ? LIMIT 1');
            $stmt->execute([$ownerId, $key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $rights = [];
            if ($row && isset($row['svalue'])) {
                $decoded = json_decode($row['svalue'], true);
                if (is_array($decoded)) { $rights = $decoded; }
            }
            echo json_encode(['success' => true, 'rights' => $rights]);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to load rights', 'detail' => $e->getMessage()]);
        }
    }

    public function saveRights() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=settings');
            exit();
        }
        $this->requireOwner();
        $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            $_SESSION['error_message'] = 'Invalid user.';
            header('Location: /?action=settings');
            exit();
        }
        try {
            $pdo = (new Database())->getConnection();
            // Ensure target user belongs to this owner
            $chk = $pdo->prepare('SELECT id FROM users WHERE id = ? AND owner_id = ? LIMIT 1');
            $chk->execute([$userId, $ownerId]);
            if (!$chk->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('User not found in this workspace');
            }
            // Collect rights payload
            $rights = $_POST['rights'] ?? [];
            if (!is_array($rights)) { $rights = []; }
            $json = json_encode($rights, JSON_UNESCAPED_UNICODE);
            $key = 'user_rights:' . $userId;
            // Upsert into store_settings (unique key on owner_id, skey)
            $stmt = $pdo->prepare('INSERT INTO store_settings (owner_id, created_by_user_id, skey, svalue) VALUES (?,?,?,?)
                                   ON DUPLICATE KEY UPDATE svalue = VALUES(svalue), updated_at = CURRENT_TIMESTAMP');
            $stmt->execute([$ownerId, (int)($_SESSION['user']['id'] ?? 0), $key, $json]);
            $_SESSION['success_message'] = 'Rights saved.';
        } catch (Throwable $e) {
            $_SESSION['error_message'] = 'Failed to save rights: ' . $e->getMessage();
        }
        header('Location: /?action=settings');
        exit();
    }
}
