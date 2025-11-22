<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Customer.php';

class OrdersController {
    private Order $orders;

    public function __construct() {
        $this->orders = new Order();
    }

    public function index(): void {
        // Default to list view
        $this->list();
    }

    public function list(): void {
        // HTML view listing orders
        $filters = [
            'status' => $_GET['status'] ?? null,
            'q' => $_GET['q'] ?? null,
        ];
        $orders = $this->orders->getAll($filters);
        require __DIR__ . '/../views/orders/index.php';
    }

    public function create(): void {
        // Create form
        $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        $customer = null;
        if ($customerId) {
            $customerModel = new Customer();
            $customer = $customerModel->get($customerId);
        }
        require __DIR__ . '/../views/orders/create.php';
    }

    public function edit(int $id = 0): void {
        // Placeholder edit route; reuses create view for now
        // Future: load order, items, and order_terms to prefill
        $this->create();
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?action=orders'); exit(); }
        try {
            $data = [
                'customer_id' => (int)($_POST['customer_id'] ?? 0),
                'contact_name' => trim($_POST['contact_name'] ?? ''),
                'order_no' => trim($_POST['order_no'] ?? ''),
                'customer_po' => trim($_POST['customer_po'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
                'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                'status' => trim($_POST['status'] ?? 'Pending'),
                'items' => $_POST['items'] ?? [],
                // Order-local Terms & Conditions captured as tnc[] checkboxes
                'terms' => isset($_POST['tnc']) && is_array($_POST['tnc']) ? array_values(array_filter(array_map('trim', $_POST['tnc']))) : [],
            ];
            $id = $this->orders->create($data);
            header('Location: /?action=orders');
            exit();
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Failed to save order: ' . htmlspecialchars($e->getMessage());
        }
    }

    public function updateStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($id <= 0 || $status === '') { http_response_code(400); echo 'Bad Request'; return; }
        try {
            $ok = $this->orders->updateStatus($id, $status);
            header('Content-Type: application/json');
            echo json_encode(['success' => $ok]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function showJson(): void {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Bad Request']); return; }
        try {
            $o = $this->orders->getWithDetails($id);
            if (!$o) { http_response_code(404); echo json_encode(['error'=>'Not found']); return; }
            echo json_encode($o);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error'=>$e->getMessage()]);
        }
    }
}
