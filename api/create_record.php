<?php
/**
 * API Endpoint: Create New Record
 */
require_once __DIR__ . '/../includes/api_auth.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
$db = new Database();
$conn = $db->getConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Ensure municipality column exists (for existing installations)
$colCheck = $conn->query("SHOW COLUMNS FROM hfdp_records LIKE 'municipality'");
if ($colCheck && $colCheck->num_rows === 0) {
    $conn->query("ALTER TABLE hfdp_records ADD COLUMN municipality VARCHAR(255) DEFAULT NULL COMMENT 'Municipality derived from assigned facility' AFTER concerned_office_facility");
}
if ($colCheck) $colCheck->free();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request. Send JSON with required fields.']);
    exit;
}

// ── REPEATER FORMAT (items array) ──
if (isset($input['items']) && is_array($input['items']) && count($input['items']) > 0) {

    // Validate shared header fields
    $headerRequired = ['year', 'cluster', 'concerned_office_facility', 'facility_level', 'presence_in_existing_plans'];
    foreach ($headerRequired as $key) {
        if (!isset($input[$key]) || trim((string)$input[$key]) === '') {
            echo json_encode(['success' => false, 'message' => "Field '$key' is required"]);
            exit;
        }
    }

    $year = intval($input['year']);
    if ($year <= 0) {
        echo json_encode(['success' => false, 'message' => "Field 'year' is required"]);
        exit;
    }
    $cluster                   = trim($input['cluster']);
    $concerned_office_facility = trim($input['concerned_office_facility']);
    $municipality              = trim($input['municipality'] ?? '');
    $facility_level            = trim($input['facility_level']);
    $presence_in_existing_plans = trim($input['presence_in_existing_plans']);
    $facilities                = $concerned_office_facility;

    $sql = "INSERT INTO hfdp_records (
        year, cluster, concerned_office_facility, municipality, facility_level, category,
        type_of_health_facility, number_of_units, facilities, costing,
        fund_source, presence_in_existing_plans
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $conn->error . '. Make sure you ran database/schema.sql to create the table.'
        ]);
        exit;
    }

    $insertedCount = 0;
    $errors = [];

    foreach ($input['items'] as $idx => $item) {
        $category               = trim($item['category'] ?? '');
        $type_of_health_facility = trim($item['type_of_health_facility'] ?? '');
        $number_of_units        = intval($item['number_of_units'] ?? 0);
        $costing                = floatval(preg_replace('/[^\d.]/', '', $item['costing'] ?? '0'));
        $fund_source            = trim($item['fund_source'] ?? '');

        if (!$category || !$type_of_health_facility || !$fund_source) {
            $errors[] = "Item #" . ($idx + 1) . ": missing required fields";
            continue;
        }

        $stmt->bind_param(
            'issssssisdss',
            $year, $cluster, $concerned_office_facility, $municipality, $facility_level, $category,
            $type_of_health_facility, $number_of_units, $facilities, $costing,
            $fund_source, $presence_in_existing_plans
        );

        if ($stmt->execute()) {
            $insertedCount++;
        } else {
            $errors[] = "Item #" . ($idx + 1) . ": " . $stmt->error;
        }
    }

    $stmt->close();

    if ($insertedCount > 0) {
        $msg = $insertedCount . ' record(s) created successfully';
        if (count($errors) > 0) $msg .= '. Warnings: ' . implode('; ', $errors);
        echo json_encode(['success' => true, 'message' => $msg, 'count' => $insertedCount]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No records created. ' . implode('; ', $errors)]);
    }

    $db->close();
    exit;
}

// ── LEGACY SINGLE-RECORD FORMAT (backward compatibility) ──
$requiredKeys = [
    'year',
    'cluster',
    'concerned_office_facility',
    'facility_level',
    'category',
    'type_of_health_facility',
    'number_of_units',
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
$cluster = trim($input['cluster']);
$concerned_office_facility = trim($input['concerned_office_facility']);
$municipality = trim($input['municipality'] ?? '');
$facility_level = trim($input['facility_level']);
$category = trim($input['category']);
$type_of_health_facility = trim($input['type_of_health_facility']);
$number_of_units = intval($input['number_of_units']);
$facilities = $concerned_office_facility;
$costing = floatval(preg_replace('/[^\d.]/', '', $input['costing']));
$fund_source = trim($input['fund_source']);
$presence_in_existing_plans = trim($input['presence_in_existing_plans']);

$sql = "INSERT INTO hfdp_records (
    year, cluster, concerned_office_facility, municipality, facility_level, category,
    type_of_health_facility, number_of_units, facilities, costing,
    fund_source, presence_in_existing_plans
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error . '. Make sure you ran database/schema.sql to create the table.'
    ]);
    exit;
}
$stmt->bind_param(
    'issssssisdss',
    $year, $cluster, $concerned_office_facility, $municipality, $facility_level, $category,
    $type_of_health_facility, $number_of_units, $facilities, $costing,
    $fund_source, $presence_in_existing_plans
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
