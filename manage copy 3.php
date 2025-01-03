<?php
require 'config/database.php';

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

<form method="get" style="margin-bottom: 20px;">
    <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search); ?>">
    <select name="status">
        <option value="">All Statuses</option>
        <option value="New" <?= $filter_status === 'New' ? 'selected' : ''; ?>>New</option>
        <option value="In Progress" <?= $filter_status === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
        <option value="Closed" <?= $filter_status === 'Closed' ? 'selected' : ''; ?>>Closed</option>
    </select>
    <button type="submit">Search</button>
    <a href="manage.php" style="margin-left: 10px;">Reset</a>
</form>

<form method="get" action="export.php">
    <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
    <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status); ?>">
    <button type="submit">Export to Excel</button>
</form>
    
<table border="1">
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
                        <select name="status" onchange="this.form.submit()">
                            <option value="New" <?= $lead['status'] === 'New' ? 'selected' : ''; ?>>New</option>
                            <option value="In Progress" <?= $lead['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Closed" <?= $lead['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                        <input type="hidden" name="update_status" value="1">
                    </form>
                </td>
                <td>
                    <a href="edit.php?id=<?= $lead['id']; ?>">Edit</a>
                    <a href="delete.php?id=<?= $lead['id']; ?>" onclick="return confirm('Are you sure you want to delete this lead?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No leads found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($total_pages > 1): ?>
    <div style="margin-top: 20px;">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="manage.php?page=<?= $i; ?>&<?= http_build_query(array_merge($_GET, ['page' => $i])); ?>" style="margin-right: 5px; <?= $page === $i ? 'font-weight: bold;' : ''; ?>">
                <?= $i; ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
