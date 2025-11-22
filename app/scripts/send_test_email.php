<?php
// Load Composer autoload (required for PHPMailer)
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/services/EmailService.php';

$to = $argv[1] ?? null;
if (!$to) {
    fwrite(STDERR, "Usage: php scripts/send_test_email.php you@example.com\n");
    exit(1);
}

try {
    $svc = new EmailService();
    $subject = 'SMTP Test from NS Technology App';
    $html = '<p>This is a <strong>test email</strong> sent at ' . date('Y-m-d H:i:s') . '</p>';
    $ok = $svc->sendHtmlEmail($to, $to, $subject, $html);
    if ($ok) {
        echo "OK: Message accepted by mailer to {$to}\n";
        exit(0);
    }
    echo "FAIL: Mailer returned false\n";
    exit(2);
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    exit(1);
}
