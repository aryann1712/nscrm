<?php
class SupportController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        require __DIR__ . '/../views/support/index.php';
    }
}
