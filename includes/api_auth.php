<?php
/**
 * Require login for API requests. Call this at the top of each API file.
 * Sends JSON 401 and exits if not logged in.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    header('Content-Type: application/json');
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit;
}
