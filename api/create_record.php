<?php
/**
 * API Endpoint: Create New Record
 */
header('Content-Type: application/json');
require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['year', 'cluster', 'concerned_office_facility', 'facility_level', 'category', 'fund_source'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
        exit;
    }
}

// Prepare data
$year = intval($input['year']);
$cluster = $db->escape($input['cluster']);
$concerned_office_facility = $db->escape($input['concerned_office_facility']);
$facility_level = $db->escape($input['facility_level']);
$category = $db->escape($input['category']);
$type_of_health_facility = isset($input['type_of_health_facility']) ? $db->escape($input['type_of_health_facility']) : null;
$number_of_units = isset($input['number_of_units']) ? intval($input['number_of_units']) : 0;
$facilities = isset($input['facilities']) ? $db->escape($input['facilities']) : null;
$target = isset($input['target']) ? $db->escape($input['target']) : null;
$costing = isset($input['costing']) ? floatval($input['costing']) : 0.00;
$fund_source = $db->escape($input['fund_source']);
$presence_in_existing_plans = isset($input['presence_in_existing_plans']) ? $db->escape($input['presence_in_existing_plans']) : null;
$remarks = isset($input['remarks']) ? $db->escape($input['remarks']) : null;

$sql = "INSERT INTO hfdp_records (
    year, cluster, concerned_office_facility, facility_level, category,
    type_of_health_facility, number_of_units, facilities, target, costing,
    fund_source, presence_in_existing_plans, remarks
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'isssssisdsiss',
    $year, $cluster, $concerned_office_facility, $facility_level, $category,
    $type_of_health_facility, $number_of_units, $facilities, $target, $costing,
    $fund_source, $presence_in_existing_plans, $remarks
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Record created successfully',
        'id' => $db->getLastInsertId()
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error creating record: ' . $stmt->error
    ]);
}

$stmt->close();
$db->close();
