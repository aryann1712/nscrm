<?php
require_once __DIR__ . '/../models/Chat.php';
require_once __DIR__ . '/../models/User.php';

class ChatController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function requireAuth(): void {
        if (empty($_SESSION['user'])) {
            header('Location: /?action=auth');
            exit;
        }
    }

    public function index(): void {
        $this->requireAuth();

        $ownerId = (int)($_SESSION['user']['owner_id'] ?? ($_SESSION['user']['id'] ?? 0));
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) {
            header('Location: /?action=dashboard');
            exit;
        }

        $mode = ($_GET['type'] ?? 'global') === 'dm' ? 'dm' : 'global';
        $otherId = $mode === 'dm' ? (int)($_GET['user_id'] ?? 0) : 0;

        $chatModel = new Chat();
        $userModel = new User();

        // Load colleagues in same company for DM list
        try {
            $users = $userModel->getByOwner($ownerId);
        } catch (Throwable $e) {
            $users = [];
        }

        $messages = [];
        $activeUser = null;

        if ($mode === 'dm' && $otherId > 0) {
            // Ensure other user belongs to same owner
            $activeUser = null;
            foreach ($users as $u) {
                if ((int)($u['id'] ?? 0) === $otherId) {
                    $activeUser = $u;
                    break;
                }
            }
            if ($activeUser === null) {
                // Invalid user for DM, fallback to global
                $mode = 'global';
            } else {
                $messages = $chatModel->listDm($ownerId, $userId, $otherId, 200);
            }
        }

        if ($mode === 'global') {
            $messages = $chatModel->listGlobal($ownerId, 200);
        }

        $currentUserId = $userId;

        require __DIR__ . '/../views/chat/index.php';
    }

    public function send(): void {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=chat');
            exit;
        }

        $ownerId = (int)($_SESSION['user']['owner_id'] ?? ($_SESSION['user']['id'] ?? 0));
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) {
            header('Location: /?action=dashboard');
            exit;
        }

        $mode = ($_POST['type'] ?? 'global') === 'dm' ? 'dm' : 'global';
        $otherId = $mode === 'dm' ? (int)($_POST['user_id'] ?? 0) : 0;
        $message = trim((string)($_POST['message'] ?? ''));
        if ($message === '') {
            // Nothing to send
            $redirect = ($mode === 'dm' && $otherId > 0)
                ? '/?action=chat&type=dm&user_id=' . $otherId
                : '/?action=chat';
            header('Location: ' . $redirect);
            exit;
        }

        $recipientId = null;
        if ($mode === 'dm' && $otherId > 0) {
            $recipientId = $otherId;
        }

        try {
            $chatModel = new Chat();
            $chatModel->addMessage($ownerId, $userId, $recipientId, $message);
        } catch (Throwable $e) {
            // swallow for now
        }

        $redirect = ($mode === 'dm' && $otherId > 0)
            ? '/?action=chat&type=dm&user_id=' . $otherId
            : '/?action=chat';
        header('Location: ' . $redirect);
        exit;
    }
}
