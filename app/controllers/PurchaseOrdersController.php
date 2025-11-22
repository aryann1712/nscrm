<?php
class PurchaseOrdersController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        $rows = [];
        require __DIR__ . '/../views/purchase_orders/index.php';
    }
}
