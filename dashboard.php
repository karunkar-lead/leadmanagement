<?php
require 'config/database.php';
require 'functions.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$total_sql = "SELECT COUNT(*) as total FROM leads WHERE 1";
$params = [];

if ($search) {
    $total_sql .= " AND (name LIKE :search OR email LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($filter_status) {
    $total_sql .= " AND status = :status";
    $params[':status'] = $filter_status;
}

$total_stmt = $pdo->prepare($total_sql);
$total_stmt->execute($params);
$total_records = $total_stmt->fetchColumn();

$total_pages = ceil($total_records / $limit);

$sql = "SELECT * FROM leads WHERE 1";
if ($search) {
    $sql .= " AND (name LIKE :search OR email LIKE :search)";
}

if ($filter_status) {
    $sql .= " AND status = :status";
}

$sql .= " LIMIT :offset, :limit";

$params[':offset'] = $offset;
$params[':limit'] = $limit;

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
if ($filter_status) {
    $stmt->bindValue(':status', $filter_status, PDO::PARAM_STR);
}
$stmt->execute();
$leads = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leads</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }

        .table th {
            background-color: #007bff;
            color: white;
        }

        .btn-primary, .btn-success, .btn-danger {
            margin-right: 10px;
        }

        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #007bff;
        }

        .pagination a:hover {
            text-decoration: underline;
        }

        .pagination .active {
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="alert <?= $alertClass; ?>"><?= $message; ?></div>
        <?php endif; ?>
        <h1>
            <?= $_SESSION['role_id'] == 1 ? 'Admin' : ($_SESSION['role_id'] == 2 ? 'Manager' : 'User'); ?> Dashboard
        </h1>
        
        <div class="d-flex text-end float-end">
            <a href="import.php" class="btn btn-info mb-3 mr-2">Import Leads</a>
            <a href="logout.php" class="btn btn-danger mb-3">Logout</a>
        </div>
        <div class="d-flex">
            
            <h4>Total Leads : <?= count($leads); ?></h4> 
            <a href="manage.php" class="btn btn-success ml-5">Manage Leads</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
