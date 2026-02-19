<?php
/**
 * Authentication helper
 * Start session and provide requireLogin() for protected pages.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require user to be logged in. Redirect to login.php if not.
 */
function requireLogin() {
    if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Check if current user is logged in.
 */
function isLoggedIn() {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['username']);
}

/**
 * Redirect to dashboard if already logged in (for login page).
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Check if current user is an admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if current user is staff
 */
function isStaff() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
}

/**
 * Get assigned facility for current staff user
 */
function getAssignedFacility() {
    return $_SESSION['assigned_facility'] ?? null;
}
