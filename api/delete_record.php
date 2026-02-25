<?php
/**
 * API Endpoint: Delete Record
 */
require_once __DIR__ . '/../includes/api_auth.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Bulk delete: ids[]
if (isset($input['ids']) && is_array($input['ids']) && count($input['ids']) > 0) {
    $ids = array_map('intval', $input['ids']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $sql = "DELETE FROM hfdp_records WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Error preparing bulk delete: ' . $conn->error
        ]);
        $db->close();
        exit;
    }
    $stmt->bind_param($types, ...$ids);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Deleted ' . $stmt->affected_rows . ' record(s) successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting records: ' . $stmt->error
        ]);
    }

    $stmt->close();
    $db->close();
    exit;
}

// Single delete: id
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
