<?php

require_once __DIR__ . '/../config/database.php';

function current_user_is_owner(): bool {
    if (empty($_SESSION['user']['id'])) return false;
    return (int)($_SESSION['user']['is_owner'] ?? 0) === 1;
}

function load_current_user_rights(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    if (empty($_SESSION['user']['id']) || empty($_SESSION['user']['owner_id'])) {
        $cache = [];
        return $cache;
    }
    if (current_user_is_owner()) {
        $cache = ['__owner' => true];
        return $cache;
    }
    $ownerId = (int)$_SESSION['user']['owner_id'];
    $userId = (int)$_SESSION['user']['id'];
    $db = new Database();
    $pdo = $db->getConnection();
    if (!$pdo) {
        $cache = [];
        return $cache;
    }
    $key = 'user_rights:' . $userId;
    $stmt = $pdo->prepare('SELECT svalue FROM store_settings WHERE owner_id = ? AND skey = ? LIMIT 1');
    $stmt->execute([$ownerId, $key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || !isset($row['svalue'])) {
        $cache = [];
        return $cache;
    }
    $data = json_decode($row['svalue'], true);
    if (!is_array($data)) {
        $cache = [];
        return $cache;
    }
    $cache = $data;
    return $cache;
}

function user_can(string $group, string $module, string $requiredLevel): bool {
    if (current_user_is_owner()) return true;
    $rights = load_current_user_rights();
    $levels = ['none' => 0, 'view' => 1, 'edit' => 2, 'full' => 3];
    $requiredLevel = strtolower($requiredLevel);
    $requiredValue = $levels[$requiredLevel] ?? 3;
    $current = 'full';
    if (isset($rights[$group]) && is_array($rights[$group]) && isset($rights[$group][$module])) {
        $current = strtolower((string)$rights[$group][$module]);
    }
    $currentValue = $levels[$current] ?? 3;
    return $currentValue >= $requiredValue;
}

function require_permission(string $group, string $module, string $requiredLevel): void {
    if (!user_can($group, $module, $requiredLevel)) {
        header('Location: /?action=dashboard&error=forbidden');
        exit;
    }
}
