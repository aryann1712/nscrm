<?php
require_once __DIR__ . '/../models/InventoryStore.php';
require_once __DIR__ . '/../config/database.php';

class InventoryStoresController {
    private InventoryStore $storeModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->storeModel = new InventoryStore();
    }

    private function requireOwner(): void {
        if (empty($_SESSION['user']['id'])) {
            header('Location: /?action=auth');
            exit();
        }
        if ((int)($_SESSION['user']['is_owner'] ?? 0) !== 1) {
            header('Location: /?action=dashboard&error=forbidden');
            exit();
        }
    }

    public function index(): void {
        $this->requireOwner();
        $stores = $this->storeModel->listStoresWithUsers();
        $users  = $this->storeModel->getOwnerUsers();
        require __DIR__ . '/../views/inventory/stores.php';
    }

    public function save(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=inventory_stores');
            exit();
        }
        $this->requireOwner();
        $name = $_POST['name'] ?? '';
        $userIds = isset($_POST['user_ids']) && is_array($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : [];
        try {
            $this->storeModel->createStore($name, $userIds);
            $_SESSION['success_message'] = 'Store saved.';
        } catch (Throwable $e) {
            $_SESSION['error_message'] = 'Failed to save store: ' . $e->getMessage();
        }
        header('Location: /?action=inventory_stores');
        exit();
    }
}
