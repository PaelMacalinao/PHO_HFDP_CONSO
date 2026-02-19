<?php
/**
 * API Endpoint: Update Record
 */
require_once __DIR__ . '/../includes/api_auth.php';
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

// Validate required fields (remarks is optional)
$requiredKeys = [
    'year',
    'cluster',
    'concerned_office_facility',
    'facility_level',
    'category',
    'type_of_health_facility',
    'number_of_units',
    'target',
    'costing',
    'fund_source',
    'presence_in_existing_plans'
];

foreach ($requiredKeys as $key) {
    if (!array_key_exists($key, $input)) {
        echo json_encode(['success' => false, 'message' => "Field '$key' is required"]);
        exit;
    }
}

if (intval($input['year']) <= 0) {
    echo json_encode(['success' => false, 'message' => "Field 'year' is required"]);
    exit;
}

$requiredStrings = [
    'cluster',
    'concerned_office_facility',
    'facility_level',
    'category',
    'type_of_health_facility',
    'target',
    'fund_source',
    'presence_in_existing_plans'
];

foreach ($requiredStrings as $key) {
    if (trim((string)$input[$key]) === '') {
        echo json_encode(['success' => false, 'message' => "Field '$key' is required"]);
        exit;
    }
}

if (!is_numeric($input['number_of_units'])) {
    echo json_encode(['success' => false, 'message' => "Field 'number_of_units' is required"]);
    exit;
}

if (!is_numeric(preg_replace('/[^\d.]/', '', $input['costing']))) {
    echo json_encode(['success' => false, 'message' => "Field 'costing' is required"]);
    exit;
}

// Prepare data
$year = intval($input['year']);
$cluster = $db->escape($input['cluster']);
$concerned_office_facility = $db->escape($input['concerned_office_facility']);
$facility_level = $db->escape($input['facility_level']);
$category = $db->escape($input['category']);
$type_of_health_facility = $db->escape($input['type_of_health_facility']);
$number_of_units = intval($input['number_of_units']);
$facilities = $concerned_office_facility;
$target = $db->escape($input['target']);
$costing = floatval(preg_replace('/[^\d.]/', '', $input['costing']));
$fund_source = $db->escape($input['fund_source']);
$presence_in_existing_plans = $db->escape($input['presence_in_existing_plans']);
$remarks = isset($input['remarks']) ? $db->escape($input['remarks']) : null;

$sql = "UPDATE hfdp_records SET
    year = ?, cluster = ?, concerned_office_facility = ?, facility_level = ?, category = ?,
    type_of_health_facility = ?, number_of_units = ?, facilities = ?, target = ?, costing = ?,
    fund_source = ?, presence_in_existing_plans = ?, remarks = ?
    WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'isssssisdsissi',
    $year, $cluster, $concerned_office_facility, $facility_level, $category,
    $type_of_health_facility, $number_of_units, $facilities, $target, $costing,
    $fund_source, $presence_in_existing_plans, $remarks, $id
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Record updated successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating record: ' . $stmt->error
    ]);
}

$stmt->close();
$db->close();
