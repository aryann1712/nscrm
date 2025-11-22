<?php
require_once __DIR__ . '/../app/config/database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    if ($pdo === null) {
        throw new Exception('DB connection failed. Check app/config/database.php');
    }

    // Ensure users table exists (minimal schema compatible with the app)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE,
        phone VARCHAR(20) UNIQUE,
        password VARCHAR(255) NULL,
        email_verified TINYINT(1) NOT NULL DEFAULT 0,
        verification_pin VARCHAR(6) NULL,
        pin_expires_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $name = 'Demo User';
    $email = 'demo@example.com';
    $phone = '9999999999';
    $pin = '1234'; // 4-digit PIN stored in password field as per current flow

    // Check by email or phone
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1');
    $stmt->execute([$email, $phone]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update PIN to be sure
        $upd = $pdo->prepare('UPDATE users SET name = ?, password = ? WHERE id = ?');
        $upd->execute([$name, $pin, $existing['id']]);
        echo "Updated existing user (ID {$existing['id']}) with PIN {$pin}\n";
    } else {
        $ins = $pdo->prepare('INSERT INTO users (name, email, phone, password, email_verified) VALUES (?, ?, ?, ?, 1)');
        $ins->execute([$name, $email, $phone, $pin]);
        $id = $pdo->lastInsertId();
        echo "Inserted demo user (ID {$id}) with PIN {$pin}\n";
    }

    echo "Login with:\n - Email: {$email}\n - or Phone: {$phone}\n - PIN: {$pin}\n";
    exit(0);
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Seed error: ' . $e->getMessage() . "\n";
    exit(1);
}
