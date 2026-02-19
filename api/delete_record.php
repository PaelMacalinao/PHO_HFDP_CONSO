<?php
/**
 * API Endpoint: Delete Record
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || empty($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Record ID is required']);
    exit;
}

$id = intval($input['id']);

$sql = "DELETE FROM hfdp_records WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Record deleted successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting record: ' . $stmt->error
    ]);
}

$stmt->close();
$db->close();
