<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    public function index() {
        try {
            $user = new User();
            $users = $user->getAll();
            require __DIR__ . '/../views/users/index.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function dashboard() {
        try {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            $ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
            $user = new User();
            $totalUsers = $ownerId > 0 ? $user->countByOwner($ownerId) : 0;
            $recentUsers = $ownerId > 0 ? $user->getRecentByOwner($ownerId, 1) : [];
            require __DIR__ . '/../views/dashboard.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function show($id) {
        try {
            $user = new User();
            $userData = $user->get($id);
            require __DIR__ . '/../views/users/show.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function create() {
        require __DIR__ . '/../views/users/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $user = new User();
                if ($user->create($_POST)) {
                    header("Location: /?action=index");
                    exit();
                } else {
                    // Handle error - redirect back to create form
                    header("Location: /?action=create");
                    exit();
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            // If not POST, redirect to index
            header("Location: /?action=index");
            exit();
        }
    }

    public function edit($id) {
        try {
            $user = new User();
            $userData = $user->get($id);
            require __DIR__ . '/../views/users/edit.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function update($id) {
        try {
            $user = new User();
            if ($user->update($id, $_POST)) {
                header("Location: /?action=index");
                exit();
            } else {
                // Handle error - redirect back to edit form
                header("Location: /?action=edit&id=" . $id);
                exit();
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function delete($id) {
        try {
            $user = new User();
            if ($user->delete($id)) {
                header("Location: /?action=index");
                exit();
            } else {
                // Handle error - redirect back to index
                header("Location: /?action=index");
                exit();
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}