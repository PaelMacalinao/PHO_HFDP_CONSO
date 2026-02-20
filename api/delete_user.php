<?php
/**
 * PHO CONSO HFDP - Delete User API
 * Allows admins to delete staff accounts only.
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

// Only admins can delete users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access Denied: Only administrators can delete users.';
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../user_management.php');
    exit;
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($user_id <= 0) {
    $_SESSION['error'] = 'Invalid user ID.';
    header('Location: ../user_management.php');
    exit;
}

// Prevent admin from deleting themselves
if ($user_id === (int)($_SESSION['user_id'] ?? 0)) {
    $_SESSION['error'] = 'You cannot delete your own account.';
    header('Location: ../user_management.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Only allow deleting users with 'staff' role for safety
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'staff'");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = 'Staff account deleted successfully.';
    } else {
        $_SESSION['error'] = 'Could not delete user. Only staff accounts can be deleted.';
    }

    $stmt->close();
    $db->close();
} catch (Throwable $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

header('Location: ../user_management.php');
exit;
