<?php
// Include PHPMailer manually
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';
require_once __DIR__ . '/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $config;
    private $lastError = '';

    public function __construct() {
        // Load config: prefer mail1.php
        $configPath1 = __DIR__ . '/../config/mail1.php';
        $configPath2 = __DIR__ . '/../config/mail.php';
        if (is_file($configPath1)) {
            $this->config = require $configPath1;
        } elseif (is_file($configPath2)) {
            $this->config = require $configPath2;
        } else {
            $this->config = [
                'host' => 'localhost',
                'port' => 1025,
                'encryption' => 'tls',
                'username' => '',
                'password' => '',
                'from_email' => 'noreply@example.com',
                'from_name' => 'App Mailer',
            ];
        }
    }

    public function getLastError(): string { return $this->lastError; }

    public function sendHtmlEmail(string $toEmail, string $toName, string $subject, string $html): bool {
        $this->lastError = '';

        // Validate minimal config
        foreach (['host','port','username','password','from_email'] as $key) {
            if (!isset($this->config[$key]) || $this->config[$key] === '') {
                $this->lastError = "Missing SMTP config: $key";
                error_log('[EmailService] ' . $this->lastError);
                break;
            }
        }

        // Use PHPMailer SMTP
        try {
            $mailer = new PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = (string)($this->config['host'] ?? 'localhost');
            $mailer->Port = (int)($this->config['port'] ?? 587);
            $mailer->SMTPAuth = true;
            $mailer->Username = (string)($this->config['username'] ?? '');
            $mailer->Password = (string)($this->config['password'] ?? '');
            $enc = strtolower((string)($this->config['encryption'] ?? 'tls'));
            $mailer->SMTPSecure = ($enc === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->SMTPAutoTLS = true; // opportunistic TLS
            $mailer->SMTPKeepAlive = false;
            if (!empty($this->config['debug'])) {
                $mailer->SMTPDebug = (int)$this->config['debug'];
                // Route debug to error_log to avoid sending content to browser
                $mailer->Debugoutput = static function ($str, $level) {
                    error_log("SMTP[$level]: $str");
                };
            }
            if (!empty($this->config['allow_self_signed'])) {
                $mailer->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }

            $fromEmail = (string)($this->config['from_email'] ?? 'noreply@example.com');
            $fromName  = (string)($this->config['from_name'] ?? 'App Mailer');
            $mailer->setFrom($fromEmail, $fromName);
            $mailer->Sender = $fromEmail; // envelope sender
            // Use mail host for HELO/Message-ID source
            $mailer->Hostname = preg_replace('/^https?:\/\//i', '', (string)($this->config['host'] ?? 'localhost'));
            // Ensure UTF-8 content
            $mailer->CharSet = 'UTF-8';
            $mailer->Encoding = 'base64';
            $mailer->addAddress($toEmail, $toName ?: $toEmail);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $html;
            $mailer->AltBody = strip_tags($html);
            $mailer->send();
            return true;

        } catch (Exception $e) {
            $this->lastError = 'SMTP send failed: ' . $e->getMessage();
            error_log('[EmailService] ' . $this->lastError);
        }

        // Fallback to mail()
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . (($this->config['from_name'] ?? 'App Mailer') . ' <' . ($this->config['from_email'] ?? 'noreply@example.com') . '>'),
            'Reply-To: ' . ($this->config['from_email'] ?? 'noreply@example.com'),
            'X-Mailer: PHP/' . phpversion()
        ];
        $ok = @mail($toEmail, $subject, $html, implode("\r\n", $headers));
        if (!$ok) {
            $this->lastError = 'mail() transport failed to hand off message';
            error_log('[EmailService] ' . $this->lastError);
        }
        return $ok;
    }

    public function sendVerificationEmail($toEmail, $toName, $verificationPin, $expiresAt) {
        $subject = 'Verify Your Email - NS Technology';
        $message = $this->getVerificationEmailTemplate($toName, $verificationPin, $expiresAt);
        return $this->sendHtmlEmail($toEmail, $toName, $subject, $message);
    }

    private function getVerificationEmailTemplate($name, $pin, $expiresAt) {
        $expiresTime = date('F j, Y \a\t g:i A', strtotime($expiresAt));

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Email Verification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .pin-box { background: #fff; border: 2px solid #1e3c72; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
                .pin { font-size: 32px; font-weight: bold; color: #1e3c72; letter-spacing: 5px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 15px; margin: 20px 0; color: #856404; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎯 Email Verification Required</h1>
                    <p>Welcome to NS Technology!</p>
                </div>
                <div class='content'>
                    <p>Hello <strong>$name</strong>,</p>
                    <p>Thank you for registering with NS Technology. To complete your account setup, please verify your email address using the verification code below:</p>
                    <div class='pin-box'>
                        <div class='pin'>$pin</div>
                        <p><strong>Verification Code</strong></p>
                    </div>
                    <div class='warning'>
                        <strong>⚠️ Important:</strong> This code will expire on <strong>$expiresTime</strong>
                    </div>
                    <p>If you didn't request this verification, please ignore this email or contact our support team.</p>
                    <p>Best regards,<br><strong>The NS Technology Team</strong></p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " NS Technology. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    public function sendWelcomeEmail($toEmail, $toName) {
        $subject = 'Welcome to NS Technology!';
        $message = $this->getWelcomeEmailTemplate($toName);
        return $this->sendHtmlEmail($toEmail, $toName, $subject, $message);
    }

    private function getWelcomeEmailTemplate($name) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Welcome to NS Technology</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .success { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; padding: 15px; margin: 20px 0; color: #155724; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Welcome to NS Technology!</h1>
                    <p>Your email has been verified successfully!</p>
                </div>
                <div class='content'>
                    <p>Hello <strong>$name</strong>,</p>
                    <div class='success'>
                        <strong>✅ Congratulations!</strong> Your email address has been verified and your account is now fully activated.
                    </div>
                    <p>You can now:</p>
                    <ul>
                        <li>Access all features of your NS Technology account</li>
                        <li>Manage your profile and settings</li>
                        <li>Use all system functionalities</li>
                    </ul>
                    <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                    <p>Welcome aboard!<br><strong>The NS Technology Team</strong></p>
                </div>
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " NS Technology. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
