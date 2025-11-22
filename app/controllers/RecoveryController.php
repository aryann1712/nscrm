<?php
class RecoveryController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        // In future, load from model. For now, static scaffold.
        $rows = [];
        require __DIR__ . '/../views/recovery/index.php';
    }
}
