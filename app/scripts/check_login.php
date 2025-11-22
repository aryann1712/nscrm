<?php
require_once __DIR__ . '/../app/models/User.php';

$login = $argv[1] ?? 'demo@example.com';
$pin   = $argv[2] ?? '1234';

try {
    $u = new User();
    $res = $u->verifyPinLogin($login, $pin);
    if ($res) {
        echo "OK: Authenticated as ID {$res['id']}, email={$res['email']}, phone={$res['phone']}\n";
        exit(0);
    }
    echo "FAIL: Invalid credentials for {$login} / {$pin}\n";
    exit(2);
} catch (Throwable $e) {
    echo "ERROR: ".$e->getMessage()."\n";
    exit(1);
}
