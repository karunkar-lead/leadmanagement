<?php
require 'config/database.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$leads = $pdo->query("SELECT name, email, phone, status, date_added FROM leads")->fetchAll();
$sheet->fromArray(['Name', 'Email', 'Phone', 'Status', 'Date Added'], NULL, 'A1');
$sheet->fromArray($leads, NULL, 'A2');

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="leads.xlsx"');
$writer->save('php://output');
