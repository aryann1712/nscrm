<?php
class TasksController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index(): void {
        // For now, use mock data to render the UI. Later we can hook to DB.
        $inbox = [
            [
                'title' => 'Meeting at Crimson with Mr Ashok',
                'id' => 'T1020553',
                'time' => '10:00 AM',
                'due' => '2025-09-24',
                'assignee' => 'Mr. Naveen Kumar',
                'priority' => 'normal',
            ],
        ];
        $outbox = [
            [
                'title' => 'Wire Laying Work in Sector 18',
                'id' => 'T1020561',
                'time' => null,
                'due' => '2025-09-24',
                'assignee' => 'Gaurdi Pandey',
                'priority' => 'normal',
            ],
            [
                'title' => 'Camera Installation in RWA 23',
                'id' => 'T1020582',
                'time' => null,
                'due' => '2025-09-24',
                'assignee' => 'Gaurdi Pandey',
                'priority' => 'high',
            ],
        ];
        require __DIR__ . '/../views/tasks/index.php';
    }
}
