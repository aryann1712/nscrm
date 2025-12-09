<?php
require_once __DIR__ . '/../models/SupportTicket.php';

class SupportController {
    private SupportTicket $tickets;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->tickets = new SupportTicket();
    }

    private function json($data, int $code = 200): void {
        if (ob_get_level()) {
            ob_clean();
        }
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function index(): void {
        require __DIR__ . '/../views/support/index.php';
    }

    public function listTickets(): void {
        try {
            if (empty($_SESSION['user'])) {
                $this->json(['error' => 'Unauthorized'], 401);
            }
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) {
                $this->json(['error' => 'Owner context missing'], 400);
            }
            $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
            $tickets = $this->tickets->listByOwner($ownerId, $status);
            $this->json(['tickets' => $tickets]);
        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to load tickets', 'detail' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(): void {
        try {
            if (empty($_SESSION['user'])) {
                $this->json(['error' => 'Unauthorized'], 401);
            }
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) {
                $this->json(['error' => 'Owner context missing'], 400);
            }

            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $status = isset($_POST['status']) ? trim((string)$_POST['status']) : '';
            if ($id <= 0 || $status === '') {
                $this->json(['error' => 'Invalid input'], 400);
            }

            $ok = $this->tickets->updateStatus($ownerId, $id, $status);
            if (!$ok) {
                $this->json(['error' => 'Failed to update status'], 400);
            }

            $ticket = $this->tickets->findById($ownerId, $id);
            $this->json(['success' => true, 'ticket' => $ticket]);
        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to update status', 'detail' => $e->getMessage()], 500);
        }
    }

    public function listMessages(): void {
        try {
            if (empty($_SESSION['user'])) {
                $this->json(['error' => 'Unauthorized'], 401);
            }
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) {
                $this->json(['error' => 'Owner context missing'], 400);
            }

            $ticketId = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
            if ($ticketId <= 0) {
                $this->json(['error' => 'Invalid ticket_id'], 400);
            }

            require_once __DIR__ . '/../models/SupportMessage.php';
            $msgModel = new SupportMessage();
            $messages = $msgModel->listByTicket($ownerId, $ticketId);
            $this->json(['messages' => $messages]);
        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to load messages', 'detail' => $e->getMessage()], 500);
        }
    }

    public function addMessage(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error' => 'Method Not Allowed'], 405); }
        try {
            if (empty($_SESSION['user'])) {
                $this->json(['error' => 'Unauthorized'], 401);
            }
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerId <= 0) {
                $this->json(['error' => 'Owner context missing'], 400);
            }

            $ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
            $message  = trim($_POST['message'] ?? '');
            if ($ticketId <= 0 || $message === '') {
                $this->json(['error' => 'Invalid input'], 400);
            }

            // Ensure ticket belongs to this owner
            $ticket = $this->tickets->findById($ownerId, $ticketId);
            if (!$ticket) {
                $this->json(['error' => 'Ticket not found'], 404);
            }

            require_once __DIR__ . '/../models/SupportMessage.php';
            $msgModel = new SupportMessage();
            $senderUserId = (int)($_SESSION['user']['id'] ?? 0);
            $id = $msgModel->create($ownerId, $ticketId, 'owner', $senderUserId, $message);

            $this->json(['success' => true, 'id' => $id]);
        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to add message', 'detail' => $e->getMessage()], 500);
        }
    }
}
