<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';

class TasksController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        if (empty($_SESSION['user'])) {
            header('Location: /?action=auth');
            exit;
        }

        $ownerId = (int)($_SESSION['user']['owner_id'] ?? ($_SESSION['user']['id'] ?? 0));
        $userId  = (int)($_SESSION['user']['id'] ?? 0);

        $inbox = [];
        $outbox = [];
        $employees = [];

        if ($ownerId > 0 && $userId > 0) {
            $taskModel = new Task();
            $inboxRows  = $taskModel->listInbox($ownerId, $userId);
            $outboxRows = $taskModel->listOutbox($ownerId, $userId);

            $mapRow = function (array $row): array {
                $dueDate = $row['due_date'] ?? null;              // YYYY-MM-DD for input/date()
                $dueRaw  = $dueDate ? (string)$dueDate : '';

                $timeRaw = $row['due_time'] ?? '';
                $timeDisplay = null;
                if (!empty($timeRaw)) {
                    $ts = strtotime($timeRaw);
                    $timeDisplay = $ts !== false ? date('g:i A', $ts) : (string)$timeRaw;
                }

                return [
                    'id'                 => $row['id'] ?? null,
                    'title'              => $row['title'] ?? '',
                    'description'        => $row['description'] ?? '',
                    'status'             => $row['status'] ?? 'open',
                    'priority'           => $row['priority'] ?? 'medium',
                    'assigned_to_user_id'=> $row['assigned_to_user_id'] ?? null,
                    'assignee'           => $row['assignee_name'] ?? '',
                    'due'                => $dueRaw,
                    'time'               => $timeDisplay,
                    'time_raw'           => $timeRaw,
                ];
            };

            $inbox  = array_map($mapRow, $inboxRows);
            $outbox = array_map($mapRow, $outboxRows);

            // Load employees for assignment dropdown (company-scoped)
            try {
                $userModel = new User();
                $employees = $userModel->getByOwner($ownerId);
            } catch (Throwable $e) {
                $employees = [];
            }
        }

        require __DIR__ . '/../views/tasks/index.php';
    }

    public function create(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=tasks');
            exit;
        }
        if (empty($_SESSION['user'])) {
            header('Location: /?action=auth');
            exit;
        }

        $ownerId = (int)($_SESSION['user']['owner_id'] ?? ($_SESSION['user']['id'] ?? 0));
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0) {
            header('Location: /?action=tasks');
            exit;
        }

        $title    = trim((string)($_POST['title'] ?? ''));
        $dueDate  = trim((string)($_POST['due_date'] ?? '')) ?: null;
        $dueTime  = trim((string)($_POST['due_time'] ?? '')) ?: null;
        $priority = in_array(($_POST['priority'] ?? 'medium'), ['low','medium','high'], true)
            ? $_POST['priority']
            : 'medium';
        $assignedTo = (int)($_POST['assigned_to_user_id'] ?? 0);
        if ($assignedTo <= 0) { $assignedTo = $userId; }

        if ($title === '') {
            // Minimal validation; later we can add flash messages
            header('Location: /?action=tasks');
            exit;
        }

        try {
            $taskModel = new Task();
            $taskModel->create([
                'owner_id' => $ownerId,
                'title' => $title,
                'description' => trim((string)($_POST['description'] ?? '')) ?: null,
                'due_date' => $dueDate,
                'due_time' => $dueTime,
                'status' => 'open',
                'priority' => $priority,
                'created_by_user_id' => $userId,
                'assigned_to_user_id' => $assignedTo,
            ]);
        } catch (Throwable $e) {
            // For now just ignore errors and redirect; could log if needed
        }

        header('Location: /?action=tasks');
        exit;
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=tasks');
            exit;
        }
        if (empty($_SESSION['user'])) {
            header('Location: /?action=auth');
            exit;
        }

        $ownerId = (int)($_SESSION['user']['owner_id'] ?? ($_SESSION['user']['id'] ?? 0));
        $userId  = (int)($_SESSION['user']['id'] ?? 0);
        $isOwner = (int)($_SESSION['user']['is_owner'] ?? 0) === 1;
        $id = (int)($_POST['id'] ?? 0);
        if ($ownerId <= 0 || $userId <= 0 || $id <= 0) {
            header('Location: /?action=tasks');
            exit;
        }

        $taskModel = new Task();
        $task = $taskModel->findById($ownerId, $id);
        if (!$task) {
            header('Location: /?action=tasks');
            exit;
        }

        $fields = [];
        $status = trim((string)($_POST['status'] ?? $task['status'] ?? 'open'));
        if (!in_array($status, ['open','in_progress','done','cancelled'], true)) {
            $status = $task['status'] ?? 'open';
        }

        if ($isOwner) {
            $fields['title'] = trim((string)($_POST['title'] ?? $task['title']));
            $fields['description'] = trim((string)($_POST['description'] ?? ($task['description'] ?? '')));
            if ($fields['description'] === '') { $fields['description'] = null; }
            $fields['due_date'] = trim((string)($_POST['due_date'] ?? ($task['due_date'] ?? '')));
            if ($fields['due_date'] === '') { $fields['due_date'] = null; }
            $fields['due_time'] = trim((string)($_POST['due_time'] ?? ($task['due_time'] ?? '')));
            if ($fields['due_time'] === '') { $fields['due_time'] = null; }
            $priority = trim((string)($_POST['priority'] ?? ($task['priority'] ?? 'medium')));
            $fields['priority'] = in_array($priority, ['low','medium','high'], true) ? $priority : 'medium';
            $assignedTo = (int)($_POST['assigned_to_user_id'] ?? ($task['assigned_to_user_id'] ?? 0));
            $fields['assigned_to_user_id'] = $assignedTo > 0 ? $assignedTo : null;
            $fields['status'] = $status;
        } else {
            // Employee can only change status on tasks they own/are assigned
            if ((int)($task['assigned_to_user_id'] ?? 0) !== $userId && (int)($task['created_by_user_id'] ?? 0) !== $userId) {
                header('Location: /?action=tasks');
                exit;
            }
            $fields['status'] = $status;
        }

        try { $taskModel->update($ownerId, $id, $fields); } catch (Throwable $e) { /* ignore */ }

        header('Location: /?action=tasks');
        exit;
    }
}
