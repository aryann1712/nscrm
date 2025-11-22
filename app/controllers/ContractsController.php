<?php
class ContractsController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        // Static scaffold to match screenshot layout
        $stats = [
            'this_month' => ['count' => 0, 'amount' => 0],
            'next_month' => ['count' => 0, 'amount' => 0],
            'twelve_months' => ['count' => 0, 'amount' => 0],
        ];
        require __DIR__ . '/../views/contracts/index.php';
    }
}
