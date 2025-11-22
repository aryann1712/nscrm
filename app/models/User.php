<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // Check if connection was successful
        if ($this->conn === null) {
            throw new Exception("Database connection failed. Please check your database configuration.");
        }
        // Ensure verification columns exist
        $this->ensureVerificationColumns();
    }

    private function ensureVerificationColumns() {
        $pdo = $this->conn;
        $columns = [
            'email_verified' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'verification_pin' => 'VARCHAR(6) NULL',
            'pin_expires_at' => 'TIMESTAMP NULL',
            'phone' => 'VARCHAR(20) NULL',
            'password' => 'VARCHAR(255) NULL',
            'company_name' => 'VARCHAR(150) NULL',
            'is_owner' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'owner_id' => 'INT NULL',
            'last_login' => 'DATETIME NULL'
        ];
        
        foreach ($columns as $column => $definition) {
            try {
                $pdo->exec("ALTER TABLE " . $this->table . " ADD COLUMN $column $definition");
            } catch (PDOException $e) {
                // Column already exists, ignore error
            }
        }
    }

    public function updateLastLogin(int $userId): void {
      if ($this->conn === null) { throw new Exception("Database connection not available"); }
      $stmt = $this->conn->prepare("UPDATE {$this->table} SET last_login = NOW() WHERE id = ?");
      $stmt->execute([$userId]);
    }

    public function getRecentByOwner(int $ownerId, int $limit = 5): array {
      if ($this->conn === null) { throw new Exception("Database connection not available"); }
      $limit = max(1, min($limit, 20));
      $stmt = $this->conn->prepare("SELECT id, name, email, last_login FROM {$this->table} WHERE owner_id = ? ORDER BY COALESCE(last_login, NOW()) DESC, id DESC LIMIT {$limit}");
      $stmt->execute([$ownerId]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function countByOwner(int $ownerId): int {
      if ($this->conn === null) { throw new Exception("Database connection not available"); }
      $stmt = $this->conn->prepare("SELECT COUNT(*) AS c FROM {$this->table} WHERE owner_id = ?");
      $stmt->execute([$ownerId]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      return (int)($row['c'] ?? 0);
    }

    public function getByOwner(int $ownerId): array {
      if ($this->conn === null) { throw new Exception("Database connection not available"); }
      // Include both the owner and their sub-users (all rows with owner_id = :ownerId)
      $stmt = $this->conn->prepare("SELECT id, name, email, is_owner, owner_id FROM {$this->table} WHERE owner_id = ? ORDER BY CASE WHEN is_owner=1 THEN 0 ELSE 1 END, name ASC, id ASC");
      $stmt->execute([$ownerId]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getAll() {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get($id) {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getByEmail($email) {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByPhone($phone) {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE phone = ?");
        $stmt->execute([$phone]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByEmailOrPhone($login)
    {
        // Determine if input looks like email; otherwise treat as phone
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return $this->getByEmail($login);
        }
        return $this->getByPhone($login);
    }

    public function verifyPasswordLogin($login, $password)
    {
        $user = $this->getByEmailOrPhone($login);
        if (!$user) { return false; }
        $stored = (string)($user['password'] ?? '');
        // If stored is a bcrypt/argon hash, use password_verify. Otherwise, fallback to plain compare for legacy records.
        if (preg_match('/^\$2y\$|^\$argon2/i', $stored)) {
            return password_verify($password, $stored) ? $user : false;
        }
        return hash_equals($stored, $password) ? $user : false;
    }

    public function verifyPinLogin($login, $pin)
    {
        // Expect a 4-digit pin
        if (!preg_match('/^\d{4}$/', $pin)) {
            return false;
        }
        $user = $this->getByEmailOrPhone($login);
        if (!$user) {
            return false;
        }
        // Compare against stored password field as plain 4-digit pin (existing schema)
        return isset($user['password']) && $user['password'] === $pin ? $user : false;
    }

    public function createWithPassword(array $data)
    {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        $hashed = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (name, email, phone, password, company_name, email_verified) VALUES (?, ?, ?, ?, ?, 0)");
        $ok = $stmt->execute([
            $data['name'], $data['email'], $data['phone'], $hashed, ($data['company_name'] ?? null)
        ]);
        if (!$ok) { return false; }
        $newId = (int)$this->conn->lastInsertId();
        // If owner_id provided in data and it's a valid existing owner, link to that owner as sub-user
        $providedOwnerId = isset($data['owner_id']) ? (int)$data['owner_id'] : 0;
        if ($providedOwnerId > 0 && $providedOwnerId !== $newId) {
            try {
                $u = $this->conn->prepare("UPDATE {$this->table} SET owner_id = ?, is_owner = 0 WHERE id = ?");
                $u->execute([$providedOwnerId, $newId]);
            } catch (Throwable $e) { /* ignore */ }
        } else {
            // Default: Set owner_id to self and mark as owner
            try {
                $u = $this->conn->prepare("UPDATE {$this->table} SET owner_id = ?, is_owner = 1 WHERE id = ? AND (owner_id IS NULL OR owner_id = 0)");
                $u->execute([$newId, $newId]);
            } catch (Throwable $e) { /* ignore */ }
        }
        return $newId;
    }

    public function setVerificationPin(int $userId, string $pin, string $expiresAt): bool
    {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET verification_pin = ?, pin_expires_at = ? WHERE id = ?");
        return $stmt->execute([$pin, $expiresAt, $userId]);
    }

    public function verifyEmail(string $email, string $pin): bool
    {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        $email = trim($email);
        $pin = trim($pin);
        // Accept only 6 numeric digits
        if ($email === '' || !preg_match('/^\d{6}$/', $pin)) {
            return false;
        }
        $user = $this->getByEmail($email);
        if (!$user) { return false; }
        // If already verified, consider it a success to avoid blocking the user
        if ((int)($user['email_verified'] ?? 0) === 1) { return true; }
        $storedPin = (string)($user['verification_pin'] ?? '');
        $expiresAt = (string)($user['pin_expires_at'] ?? '');
        if ($storedPin === '' || $storedPin !== $pin) { return false; }
        if ($expiresAt !== '' && strtotime($expiresAt) < time()) { return false; }
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET email_verified = 1, verification_pin = NULL, pin_expires_at = NULL WHERE email = ?");
        return $stmt->execute([$email]);
    }

    public function create($data) {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        
        // Generate verification PIN
        $verificationPin = $this->generateVerificationPin();
        $pinExpiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (name, email, phone, password, verification_pin, pin_expires_at, email_verified, company_name) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
        $result = $stmt->execute([
            $data['name'], 
            $data['email'], 
            $data['phone'], 
            $data['password'],
            $verificationPin,
            $pinExpiresAt,
            ($data['company_name'] ?? null)
        ]);
        
        if ($result) {
            $userId = $this->conn->lastInsertId();
            try {
                $u = $this->conn->prepare("UPDATE {$this->table} SET owner_id = ?, is_owner = 1 WHERE id = ? AND (owner_id IS NULL OR owner_id = 0)");
                $u->execute([$userId, $userId]);
            } catch (Throwable $e) { /* ignore */ }
            return [
                'id' => $userId,
                'pin' => $verificationPin,
                'expires_at' => $pinExpiresAt
            ];
        }
        
        return false;
    }

    public function resendVerificationPin(string $email): array|false
    {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        $email = trim($email);
        if ($email === '') { return false; }
        $user = $this->getByEmail($email);
        if (!$user) { return false; }
        // Generate new pin
        $pin = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $ok = $this->setVerificationPin((int)$user['id'], $pin, $expiresAt);
        if (!$ok) { return false; }
        return [
            'pin' => $pin,
            'expires_at' => $expiresAt,
        ];
    }

    public function createSubUser(array $data, int $ownerId, bool $markEmailVerified = true): int|false
    {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        $owner = $this->get($ownerId);
        if (!$owner || (int)($owner['id'] ?? 0) !== $ownerId) {
            throw new InvalidArgumentException('Owner not found');
        }
        $hashed = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (name, email, phone, password, company_name, email_verified, owner_id, is_owner) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
        $ok = $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $hashed,
            $owner['company_name'] ?? null,
            $markEmailVerified ? 1 : 0,
            $ownerId,
        ]);
        if (!$ok) { return false; }
        return (int)$this->conn->lastInsertId();
    }
    
    private function generateVerificationPin() {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function update($id, $data) {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $stmt = $this->conn->prepare("UPDATE {$this->table} SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
            return $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['password'], $id]);
        } else {
            $stmt = $this->conn->prepare("UPDATE {$this->table} SET name = ?, email = ?, phone = ? WHERE id = ?");
            return $stmt->execute([$data['name'], $data['email'], $data['phone'], $id]);
        }
    }

    public function delete($id) {
        if ($this->conn === null) {
            throw new Exception("Database connection not available");
        }
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}