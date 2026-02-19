<?php
/**
 * API Endpoint: Get Records with Filters
 */
require_once __DIR__ . '/../includes/api_auth.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get filter parameters
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$year = isset($_GET['year']) ? intval($_GET['year']) : null;
$cluster = isset($_GET['cluster']) ? $db->escape($_GET['cluster']) : null;
$facility_level = isset($_GET['facility_level']) ? $db->escape($_GET['facility_level']) : null;
$category = isset($_GET['category']) ? $db->escape($_GET['category']) : null;
$type_of_health_facility = isset($_GET['type_of_health_facility']) ? $db->escape($_GET['type_of_health_facility']) : null;
$target = isset($_GET['target']) ? $db->escape($_GET['target']) : null;
$fund_source = isset($_GET['fund_source']) ? $db->escape($_GET['fund_source']) : null;
$presence_plans = isset($_GET['presence_plans']) ? $db->escape($_GET['presence_plans']) : null;

// Build WHERE clause
$where = [];
$params = [];
$types = '';

// If ID is provided, fetch only that record
if ($id !== null && $id > 0) {
    $where[] = "id = ?";
    $params[] = $id;
    $types .= 'i';
} else {
    // Apply other filters only if not fetching by ID
    if ($year !== null && $year > 0) {
        $where[] = "year = ?";
        $params[] = $year;
        $types .= 'i';
    }

    if ($cluster !== null && $cluster !== '') {
        $where[] = "cluster = ?";
        $params[] = $cluster;
        $types .= 's';
    }

    if ($facility_level !== null && $facility_level !== '') {
        $where[] = "facility_level = ?";
        $params[] = $facility_level;
        $types .= 's';
    }

    if ($category !== null && $category !== '') {
        $where[] = "category = ?";
        $params[] = $category;
        $types .= 's';
    }

    if ($type_of_health_facility !== null && $type_of_health_facility !== '') {
        $where[] = "type_of_health_facility = ?";
        $params[] = $type_of_health_facility;
        $types .= 's';
    }

    if ($target !== null && $target !== '') {
        $where[] = "target = ?";
        $params[] = $target;
        $types .= 's';
    }

    if ($fund_source !== null && $fund_source !== '') {
        $where[] = "fund_source LIKE ?";
        $params[] = '%' . $fund_source . '%';
        $types .= 's';
    }

    if ($presence_plans !== null && $presence_plans !== '') {
        $where[] = "presence_in_existing_plans = ?";
        $params[] = $presence_plans;
        $types .= 's';
    }
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT * FROM hfdp_records $whereClause ORDER BY year ASC, concerned_office_facility ASC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $records,
    'count' => count($records)
]);

$stmt->close();
$db->close();
