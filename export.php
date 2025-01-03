<?php
require 'config/database.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

$sql = "SELECT name, email, phone, status, date_added FROM leads WHERE 1";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE :search OR email LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($filter_status) {
    $sql .= " AND status = :status";
    $params[':status'] = $filter_status;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$headers = ['Name', 'Email', 'Phone', 'Status', 'Date Added'];
$sheet->fromArray($headers, NULL, 'A1');

$sheet->fromArray($leads, NULL, 'A2');

$filename = 'leads_export.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
