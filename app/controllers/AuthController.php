    <?php
    require_once __DIR__ . '/../models/User.php';

    class AuthController {
        public function showLogin() {
            // If already logged in, go to dashboard
            if (isset($_SESSION['user'])) {
                if (($_SESSION['user']['type'] ?? null) === 'customer') {
                    header('Location: /?action=customer_dashboard');
                } else {
                    header('Location: /?action=dashboard');
                }
                exit;
            }
            require __DIR__ . '/../views/auth/login.php';
        }

        public function login() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /?action=auth');
                exit;
            }

            $login = trim($_POST['login'] ?? ''); // email or phone
            $password   = (string)($_POST['password'] ?? '');

            if ($login === '' || $password === '') {
                header('Location: /?action=auth&error=empty');
                exit;
            }

            try {
                $userModel = new User();
                $user = $userModel->verifyPasswordLogin($login, $password);
                if ($user) {
                    // If email is not verified, do NOT create a session; redirect to verification page and resend code
                    if (empty($user['email_verified'])) {
                        // Generate a fresh verification PIN and email it
                        try {
                            $otp = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
                            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                            $userModel->setVerificationPin((int)$user['id'], $otp, $expiresAt);
                            require_once __DIR__ . '/../services/EmailService.php';
                            $mailer = new EmailService();
                            $mailer->sendVerificationEmail($user['email'], $user['name'] ?? '', $otp, $expiresAt);
                        } catch (Throwable $te) {
                            error_log('[AuthController] resend OTP on login failed: ' . $te->getMessage());
                        }
                        header('Location: /?action=auth&subaction=verify&email=' . urlencode($user['email']) . '&resent=1');
                        exit;
                    }
                    // Email already verified; create session and proceed
                    // Determine user type: use explicit 'type' if set. If not set, treat non-owners as staff/admin, not customers.
                    $userType = $user['type'] ?? null;
                    if ($userType === null) {
                        $userType = ((int)($user['is_owner'] ?? 0) === 1) ? 'owner' : 'staff';
                    }
                    
                    $_SESSION['user'] = [
                        'id' => $user['id'] ?? null,
                        'name' => $user['name'] ?? null,
                        'email' => $user['email'] ?? null,
                        'phone' => $user['phone'] ?? null,
                        'owner_id' => $user['owner_id'] ?? ($user['id'] ?? null),
                        'is_owner' => (int)($user['is_owner'] ?? 0),
                        'company_name' => $user['company_name'] ?? null,
                        'type' => $userType, // Use determined type
                    ];
                    try { $userModel->updateLastLogin((int)$user['id']); } catch (Throwable $te) { /* ignore */ }
                    // If customer type, go to their dashboard
                    if (isset($_SESSION['user']['type']) && $_SESSION['user']['type'] === 'customer') {
                        header('Location: /?action=customer_dashboard');
                    } else {
                        // Owner/admin fallback as before
                        header('Location: /?action=dashboard');
                    }
                    exit;
                }
            } catch (Exception $e) {
                error_log('[AuthController] Login error: '. $e->getMessage());
                header('Location: /?action=auth&error=server');
                exit;
            }

            // Invalid credentials
            header('Location: /?action=auth&error=invalid');
            exit;
        }

        public function logout() {
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

        // Registration (Sign Up)
        public function showRegister() {
            // Do not allow signup while already authenticated
            if (isset($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
                header('Location: /?action=dashboard');
                exit;
            }
            require __DIR__ . '/../views/auth/register.php';
        }

        public function registerSubmit() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /?action=auth&subaction=register');
                exit;
            }
            // Prevent creating a new owner while logged in (must logout first)
            if (isset($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
                header('Location: /?action=dashboard');
                exit;
            }
            $name = trim($_POST['name'] ?? '');
            $companyName = trim($_POST['company_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            $confirm = (string)($_POST['confirm_password'] ?? '');

            if ($name === '' || $companyName === '' || $email === '' || $phone === '' || $password === '' || $confirm === '') {
                header('Location: /?action=auth&subaction=register&error=empty');
                exit;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header('Location: /?action=auth&subaction=register&error=email');
                exit;
            }
            if ($password !== $confirm) {
                header('Location: /?action=auth&subaction=register&error=match');
                exit;
            }
            try {
                $userModel = new User();
                // Ensure unique email/phone
                if ($userModel->getByEmail($email) || $userModel->getByPhone($phone)) {
                    header('Location: /?action=auth&subaction=register&error=exists');
                    exit;
                }
                $userId = $userModel->createWithPassword([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $password,
                    'company_name' => $companyName,
                ]);
                if (!$userId) {
                    header('Location: /?action=auth&subaction=register&error=server');
                    exit;
                }
                // Create OTP and email it
                $otp = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $userModel->setVerificationPin((int)$userId, $otp, $expiresAt);

                require_once __DIR__ . '/../services/EmailService.php';
                $mailer = new EmailService();
                $mailer->sendVerificationEmail($email, $name, $otp, $expiresAt);

                header('Location: /?action=auth&subaction=verify&email=' . urlencode($email));
                exit;
            } catch (Exception $e) {
                error_log('[AuthController] Register error: '. $e->getMessage());
                header('Location: /?action=auth&subaction=register&error=server');
                exit;
            }
        }

        public function showVerify() {
            $email = $_GET['email'] ?? '';
            require __DIR__ . '/../views/auth/verify.php';
        }

        public function verifySubmit() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /?action=auth');
                exit;
            }
            $email = trim($_POST['email'] ?? '');
            $code  = trim($_POST['code'] ?? '');
            if ($email === '' || $code === '') {
                header('Location: /?action=auth&subaction=verify&email=' . urlencode($email) . '&error=empty');
                exit;
            }
            try {
                $userModel = new User();
                if ($userModel->verifyEmail($email, $code)) {
                    require_once __DIR__ . '/../services/EmailService.php';
                    $mailer = new EmailService();
                    $user = $userModel->getByEmail($email);
                    if ($user) {
                        $mailer->sendWelcomeEmail($email, $user['name'] ?? '');
                        // Auto-login after verification
                        $_SESSION['user'] = [
                            'id' => $user['id'] ?? null,
                            'name' => $user['name'] ?? null,
                            'email' => $user['email'] ?? null,
                            'phone' => $user['phone'] ?? null,
                            'owner_id' => $user['owner_id'] ?? ($user['id'] ?? null),
                            'is_owner' => (int)($user['is_owner'] ?? 0),
                            'company_name' => $user['company_name'] ?? null,
                        ];
                        try { $userModel->updateLastLogin((int)$user['id']); } catch (Throwable $te) { /* ignore */ }
                    }
                    header('Location: /?action=dashboard');
                    exit;
                }
                header('Location: /?action=auth&subaction=verify&email=' . urlencode($email) . '&error=invalid');
                exit;
            } catch (Exception $e) {
                error_log('[AuthController] Verify error: '. $e->getMessage());
                header('Location: /?action=auth&subaction=verify&email=' . urlencode($email) . '&error=server');
                exit;
            }
        }

        public function resendOtp() {
            $email = trim($_POST['email'] ?? $_GET['email'] ?? '');
            if ($email === '') {
                header('Location: /?action=auth');
                exit;
            }
            try {
                $userModel = new User();
                $user = $userModel->getByEmail($email);
                if (!$user) {
                    header('Location: /?action=auth&subaction=verify&email=' . urlencode($email) . '&error=invalid');
                    exit;
                }
                // Generate fresh OTP
                $otp = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $userModel->setVerificationPin((int)$user['id'], $otp, $expiresAt);

                require_once __DIR__ . '/../services/EmailService.php';
                $mailer = new EmailService();
                $mailer->sendVerificationEmail($email, $user['name'] ?? '', $otp, $expiresAt);

                header('Location: /?action=auth&subaction=verify&email=' . urlencode($email) . '&resent=1');
                exit;
            } catch (Exception $e) {
                error_log('[AuthController] Resend OTP error: '. $e->getMessage());
                header('Location: /?action=auth&subaction=verify&email=' . urlencode($email) . '&error=server');
                exit;
            }
        }
    }

