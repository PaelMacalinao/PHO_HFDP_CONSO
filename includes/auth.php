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
