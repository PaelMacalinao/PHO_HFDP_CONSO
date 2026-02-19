<?php
/**
 * API Endpoint: Create New Record
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
$db = new Database();
$conn = $db->getConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request. Send JSON with required fields.']);
    exit;
}

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

if (!is_numeric($input['costing'])) {
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
$costing = floatval($input['costing']);
$fund_source = $db->escape($input['fund_source']);
$presence_in_existing_plans = $db->escape($input['presence_in_existing_plans']);
$remarks = isset($input['remarks']) ? $db->escape($input['remarks']) : null;

$sql = "INSERT INTO hfdp_records (
    year, cluster, concerned_office_facility, facility_level, category,
    type_of_health_facility, number_of_units, facilities, target, costing,
    fund_source, presence_in_existing_plans, remarks
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error . '. Make sure you ran database/schema.sql to create the table.'
    ]);
    exit;
}
$stmt->bind_param(
    'isssssissdsss',
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

} catch (Throwable $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
