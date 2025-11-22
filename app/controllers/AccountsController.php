<?php
class AccountsController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        // For now, static groups/ledgers scaffold
        $groups = [
            'Current Assets','Fixed Assets','Equity','Long Term Liabilities','Short Term Liabilities',
            'Direct Income','Indirect Income','Sales','Direct Expense','Indirect Expense','Purchase'
        ];
        $quick = [
            'Balance Sheet','Profit & Loss','Trial Balance','GST Ledgers','Forex/Letter','Stock Value','Purchase Orders','Credit Note','Debit Note'
        ];
        require __DIR__ . '/../views/accounts/index.php';
    }
}
