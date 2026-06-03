<?php
/**
 * Export filtered records to Excel (.xlsx) with column auto-size, wrap text, and COSTING sum row.
 * Uses the same filter rules as get_records.php (including staff facility scope).
 */
require_once __DIR__ . '/../includes/api_auth.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_readable($autoload)) {
    header('Content-Type: text/plain; charset=UTF-8');
    http_response_code(503);
    echo 'Excel export requires dependencies. From the project folder run: php composer.phar install';
    exit;
}
require_once $autoload;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$db = new Database();
$conn = $db->getConnection();

$year = isset($_GET['year']) ? intval($_GET['year']) : null;
$cluster = isset($_GET['cluster']) ? trim((string) $_GET['cluster']) : null;
$facility_level = isset($_GET['facility_level']) ? trim((string) $_GET['facility_level']) : null;
$category = isset($_GET['category']) ? trim((string) $_GET['category']) : null;
$type_of_health_facility = isset($_GET['type_of_health_facility']) ? trim((string) $_GET['type_of_health_facility']) : null;
$fund_source = isset($_GET['fund_source']) ? trim((string) $_GET['fund_source']) : null;
$presence_plans = isset($_GET['presence_plans']) ? trim((string) $_GET['presence_plans']) : null;

$where = [];
$params = [];
$types = '';

if (isStaff()) {
    $assignedFacility = getAssignedFacility();
    if ($assignedFacility) {
        $where[] = 'concerned_office_facility = ?';
        $params[] = $assignedFacility;
        $types .= 's';
    }
}

if ($year !== null && $year > 0) {
    $where[] = 'year = ?';
    $params[] = $year;
    $types .= 'i';
}
if ($cluster !== null && $cluster !== '') {
    $where[] = 'cluster = ?';
    $params[] = $cluster;
    $types .= 's';
}
if ($facility_level !== null && $facility_level !== '') {
    $where[] = 'facility_level = ?';
    $params[] = $facility_level;
    $types .= 's';
}
if ($category !== null && $category !== '') {
    $where[] = 'category = ?';
    $params[] = $category;
    $types .= 's';
}
if ($type_of_health_facility !== null && $type_of_health_facility !== '') {
    $where[] = 'type_of_health_facility = ?';
    $params[] = $type_of_health_facility;
    $types .= 's';
}
if ($fund_source !== null && $fund_source !== '') {
    $where[] = 'fund_source LIKE ?';
    $params[] = '%' . $fund_source . '%';
    $types .= 's';
}
if ($presence_plans !== null && $presence_plans !== '') {
    $where[] = 'presence_in_existing_plans = ?';
    $params[] = $presence_plans;
    $types .= 's';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT id, year, cluster, concerned_office_facility, municipality, barangay_name, facility_level, category, type_of_health_facility, number_of_units, facilities, costing, fund_source, presence_in_existing_plans
        FROM hfdp_records $whereClause ORDER BY year ASC, concerned_office_facility ASC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();
$db->close();

$headers = [
    'YEAR',
    'CLUSTER',
    'CONCERNED OFFICE / FACILITY',
    'MUNICIPALITY',
    'BARANGAY',
    'BHS/PCF/HOSP',
    'INFRA/EQUIP/HR',
    'TYPE OF HEALTH FACILITY / REQUESTED ITEM',
    'NUMBER OF UNITS',
    'TARGET (SPECIFIC DESCRIPTION)',
    'COSTING',
    'FUND SOURCE',
    'PRESENCE IN EXISTING PLANS',
];

$colCount = count($headers);
$costingColIndex = 11; // 1-based column K
$costingColLetter = Coordinate::stringFromColumnIndex($costingColIndex);
$lastColLetter = Coordinate::stringFromColumnIndex($colCount);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('CONSO');

// Header row
for ($c = 1; $c <= $colCount; $c++) {
    $sheet->setCellValueByColumnAndRow($c, 1, $headers[$c - 1]);
}

$dataStartRow = 2;
$rowNum = $dataStartRow;
foreach ($rows as $r) {
    $sheet->setCellValueByColumnAndRow(1, $rowNum, (int) $r['year']);
    $sheet->setCellValueByColumnAndRow(2, $rowNum, (string) ($r['cluster'] ?? ''));
    $sheet->setCellValueByColumnAndRow(3, $rowNum, (string) ($r['concerned_office_facility'] ?? ''));
    $sheet->setCellValueByColumnAndRow(4, $rowNum, (string) ($r['municipality'] ?? ''));
    $sheet->setCellValueByColumnAndRow(5, $rowNum, (string) ($r['barangay_name'] ?? ''));
    $sheet->setCellValueByColumnAndRow(6, $rowNum, (string) ($r['facility_level'] ?? ''));
    $sheet->setCellValueByColumnAndRow(7, $rowNum, (string) ($r['category'] ?? ''));
    $sheet->setCellValueByColumnAndRow(8, $rowNum, (string) ($r['type_of_health_facility'] ?? ''));
    $sheet->setCellValueByColumnAndRow(9, $rowNum, (int) ($r['number_of_units'] ?? 0));
    $sheet->setCellValueByColumnAndRow(10, $rowNum, (string) ($r['facilities'] ?? ''));
    $sheet->setCellValueByColumnAndRow(11, $rowNum, (float) $r['costing']);
    $sheet->setCellValueByColumnAndRow(12, $rowNum, (string) ($r['fund_source'] ?? ''));
    $sheet->setCellValueByColumnAndRow(13, $rowNum, (string) ($r['presence_in_existing_plans'] ?? ''));
    $rowNum++;
}

$lastDataRow = $rowNum - 1;

// Header style (bold, wrap, light fill, borders)
$headerRange = 'A1:' . $lastColLetter . '1';
$sheet->getStyle($headerRange)->getFont()->setBold(true);
$sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle($headerRange)->getAlignment()->setWrapText(true);
$sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
$sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

if ($lastDataRow >= $dataStartRow) {
    $dataRange = 'A' . $dataStartRow . ':' . $lastColLetter . $lastDataRow;
    $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
    $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Long-text columns: wrap so Excel shows full content (row height expands on open)
    foreach ([3, 4, 5, 8, 10] as $wrapCol) {
        $colL = Coordinate::stringFromColumnIndex($wrapCol);
        $sheet->getStyle($colL . $dataStartRow . ':' . $colL . $lastDataRow)->getAlignment()->setWrapText(true);
    }

    // Costing: numeric display with thousands separator (like sample sheet)
    $costingBodyRange = $costingColLetter . $dataStartRow . ':' . $costingColLetter . $lastDataRow;
    $sheet->getStyle($costingBodyRange)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle($costingBodyRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    // SUM row
    $sumRow = $lastDataRow + 1;
    $sheet->setCellValue('A' . $sumRow, 'TOTAL');
    $sheet->setCellValue($costingColLetter . $sumRow, '=SUM(' . $costingColLetter . $dataStartRow . ':' . $costingColLetter . $lastDataRow . ')');
    $sheet->getStyle($costingColLetter . $sumRow)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle($costingColLetter . $sumRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('A' . $sumRow . ':' . $lastColLetter . $sumRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $sumRow . ':' . $lastColLetter . $sumRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle('A' . $sumRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $filterEndRow = $lastDataRow;
    $sheet->setAutoFilter('A1:' . $lastColLetter . $filterEndRow);
} else {
    $sheet->setCellValue('A2', 'No records match the current filters.');
    $sheet->mergeCells('A2:' . $lastColLetter . '2');
}

$sheet->freezePane('A2');

// Auto-size all columns so content is readable without manual drag
for ($i = 1; $i <= $colCount; $i++) {
    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
}

// Cap very wide auto columns (TARGET) so sheet stays usable; wrap still shows full text
$targetCol = Coordinate::stringFromColumnIndex(10);
$sheet->getColumnDimension($targetCol)->setAutoSize(false);
$sheet->getColumnDimension($targetCol)->setWidth(52);

$filename = 'PHO_CONSO_HFDP_Export_' . date('Y-m-d_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
