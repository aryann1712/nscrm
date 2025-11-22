<?php
class StoreController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        $step = $_GET['step'] ?? 'basic';
        $allowed = ['basic','products','purchases','header','offer','catalog','about','team','faqs','contact'];
        if (!in_array($step, $allowed, true)) { $step = 'basic'; }
        $content = __DIR__ . '/../views/store/index.php';
        if (is_file($content)) {
            // Make $step available to the view
            $storeStep = $step;
            include $content;
        } else {
            echo 'Store view missing';
        }
    }
}
