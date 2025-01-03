<?php
require 'config/database.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    if ($file['type'] !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
        die("Invalid file format. Please upload an Excel file.");
    }

    try {
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $success = 0;
        $failed = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;
            [$name, $email, $phone, $status] = $row;

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($status, ['New', 'In Progress', 'Closed'])) {
                $failed++;
                continue;
            }

            $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $status]);
            $success++;
        }

        echo "Import Summary: $success records imported, $failed failed.";
    } catch (Exception $e) {
        die("Error processing file: " . $e->getMessage());
    }
} else {
    echo '
    <form action="import.php" method="post" enctype="multipart/form-data">
        <label for="file">Upload Excel File:</label>
        <input type="file" name="file" id="file" required>
        <button type="submit">Import</button>
    </form>
    ';
}
