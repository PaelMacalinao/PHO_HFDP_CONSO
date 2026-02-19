<?php
/**
 * Export filtered records to CSV (Excel-compatible).
 * Uses the same filter parameters as get_records.php.
 */
require_once __DIR__ . '/../includes/api_auth.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Same filter params as get_records.php
$year = isset($_GET['year']) ? intval($_GET['year']) : null;
$cluster = isset($_GET['cluster']) ? $db->escape($_GET['cluster']) : null;
$facility_level = isset($_GET['facility_level']) ? $db->escape($_GET['facility_level']) : null;
$category = isset($_GET['category']) ? $db->escape($_GET['category']) : null;
$type_of_health_facility = isset($_GET['type_of_health_facility']) ? $db->escape($_GET['type_of_health_facility']) : null;
$target = isset($_GET['target']) ? $db->escape($_GET['target']) : null;
$fund_source = isset($_GET['fund_source']) ? $db->escape($_GET['fund_source']) : null;
$presence_plans = isset($_GET['presence_plans']) ? $db->escape($_GET['presence_plans']) : null;

$where = [];
$params = [];
$types = '';

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

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$sql = "SELECT id, year, cluster, concerned_office_facility, facility_level, category, type_of_health_facility, number_of_units, facilities, target, costing, fund_source, presence_in_existing_plans, remarks FROM hfdp_records $whereClause ORDER BY year ASC, concerned_office_facility ASC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// CSV headers (CONSO HFDP format)
$headers = [
    'Year',
    'Cluster',
    'Concerned Office / Facility',
    'BHS/PCF/HOSP (Facility Level)',
    'INFRA/EQUIP/HR (Category)',
    'Type of Health Facility',
    'Number of Units',
    'Facilities (Specific Item Description)',
    'Target',
    'Costing',
    'Fund Source',
    'Presence in Existing Plans',
    'Remarks'
];

$filename = 'PHO_CONSO_HFDP_Export_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');
fprintf($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

fputcsv($out, $headers);

while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $row['year'],
        $row['cluster'],
        $row['concerned_office_facility'],
        $row['facility_level'],
        $row['category'],
        $row['type_of_health_facility'],
        $row['number_of_units'],
        $row['facilities'],
        $row['target'],
        $row['costing'],
        $row['fund_source'],
        $row['presence_in_existing_plans'],
        $row['remarks']
    ]);
}

fclose($out);
$stmt->close();
$db->close();
