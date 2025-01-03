<?php
require 'config/database.php';
require 'functions.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if the user has permission to edit
if (!hasPermission($pdo, $_SESSION['role_id'], 'edit')) {

    die("You do not have permission to perform this action.");
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    if (!in_array($status, ['New', 'In Progress', 'Closed'])) {
        die("Invalid status value.");
    }

    $stmt = $pdo->prepare("UPDATE leads SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    header("Location: manage.php?" . http_build_query($_GET));
    exit;
}
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
        <h1>Manage Leads</h1>
        <div class="d-flex">
            <!-- Search & Filter Form -->
            <form method="get" class="form-inline mb-3">
                <input type="text" name="search" placeholder="Search by name or email" class="form-control mr-2" value="<?= htmlspecialchars($search); ?>">
                <select name="status" class="form-control mr-2">
                    <option value="">All Statuses</option>
                    <option value="New" <?= $filter_status === 'New' ? 'selected' : ''; ?>>New</option>
                    <option value="In Progress" <?= $filter_status === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Closed" <?= $filter_status === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="manage.php" class="btn btn-secondary ml-2">Reset</a>
            </form>
            <!-- <div class="float-end"> -->
                <!-- Export Form -->
                <form method="get" action="export.php" class="mb-3 ml-5">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status); ?>">
                    <button type="submit" class="btn btn-success">Export to Excel</button>
                </form>

                <a href="create.php" class="btn btn-info mb-3">Add Lead</a>
                <a href="logout.php" class="btn btn-danger mb-3">Logout</a>
            <!-- </div> -->
        </div>

        <!-- Leads Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($leads) > 0): ?>
                    <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td><?= htmlspecialchars($lead['id']); ?></td>
                        <td><?= htmlspecialchars($lead['name']); ?></td>
                        <td><?= htmlspecialchars($lead['email']); ?></td>
                        <td><?= htmlspecialchars($lead['phone']); ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $lead['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="form-control">
                                    <option value="New" <?= $lead['status'] === 'New' ? 'selected' : ''; ?>>New</option>
                                    <option value="In Progress" <?= $lead['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Closed" <?= $lead['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td>
                            <a href="edit.php?id=<?= $lead['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete.php?id=<?= $lead['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this lead?');">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No leads found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="manage.php?page=<?= $i; ?>&<?= http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="<?= $page === $i ? 'active' : ''; ?>">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Optional: Add Bootstrap JS (Optional) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
