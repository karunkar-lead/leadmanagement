<!-- <?php
require 'config/database.php';

$leads = $pdo->query("SELECT * FROM leads")->fetchAll();
?>

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
        <?php foreach ($leads as $lead): ?>
        <tr>
            <td><?= htmlspecialchars($lead['id']); ?></td>
            <td><?= htmlspecialchars($lead['name']); ?></td>
            <td><?= htmlspecialchars($lead['email']); ?></td>
            <td><?= htmlspecialchars($lead['phone']); ?></td>
            <td><?= htmlspecialchars($lead['status']); ?></td>
            <td>
                <a href="edit.php?id=<?= $lead['id']; ?>">Edit</a>
                <a href="delete.php?id=<?= $lead['id']; ?>" onclick="return confirm('Are you sure you want to delete this lead?');">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table> -->

<?php
require 'config/database.php';

// Fetch all leads
$leads = $pdo->query("SELECT * FROM leads")->fetchAll();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    if (!in_array($status, ['New', 'In Progress', 'Closed'])) {
        die("Invalid status value.");
    }

    $stmt = $pdo->prepare("UPDATE leads SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    header("Location: manage.php"); // Reload the page
    exit;
}
?>

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
    </tbody>
</table>

