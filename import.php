<?php
require 'config/database.php';
require 'vendor/autoload.php';
require 'functions.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!hasPermission($pdo, $_SESSION['role_id'], 'edit')) {

    die("You do not have permission to perform this action.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    if ($file['type'] !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
        $message = "Invalid file format. Please upload an Excel file.";
        $alertClass = "alert-danger";
    } else {
        try {
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $success = 0;
            $failed = 0;
            $skippedEmails = [];

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header row
                [$name, $email, $phone, $status] = $row;

                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($status, ['New', 'In Progress', 'Closed'])) {
                    $failed++;
                    continue;
                }

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE email = ?");
                $stmt->execute([$email]);
                $emailExists = $stmt->fetchColumn();
            
                if ($emailExists > 0) {
                    $skippedEmails[] = $email;
                    $failed++;
                    continue;
                }else{
                    $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, status) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $phone, $status]);
                    $success++;
                }

            }

            if ($failed > 0) {
                    $message = "Already existing the mentioned mails in the lead, email: " . implode(', ', $skippedEmails);
                    $alertClass = "alert-danger";
            }else{

                $message = "Leads are imported successfully.";
                $alertClass = "alert-success";
            }

        } catch (Exception $e) {
            $message = "Error processing file: " . $e->getMessage();
            $alertClass = "alert-danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Leads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3>Import Excel File</h3>
            </div>
            <div class="card-body">
                <?php if (isset($message)): ?>
                    <div class="alert <?= $alertClass; ?>"><?= $message; ?></div>
                <?php endif; ?>

                <form action="import.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="file" class="form-label">Upload Excel File:</label>
                        <input type="file" name="file" id="file" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Import</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
