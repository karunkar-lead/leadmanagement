<?php
require 'config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Lead ID.");
}
$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
$stmt->execute([$id]);
$lead = $stmt->fetch();

if (!$lead) {
    die("Lead not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $status = $_POST['status'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    if (!in_array($status, ['New', 'In Progress', 'Closed'])) {
        die("Invalid status value.");
    }

    $stmt = $pdo->prepare("UPDATE leads SET name = ?, email = ?, phone = ?, status = ? WHERE id = ?");
    $stmt->execute([$name, $email, $phone, $status, $id]);

    header("Location: manage.php");
    exit;
}
?>

<form method="post">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($lead['name']); ?>" required>
    <br>

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($lead['email']); ?>" required>
    <br>

    <label>Phone:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($lead['phone']); ?>" required>
    <br>

    <label>Status:</label>
    <select name="status" required>
        <option value="New" <?= $lead['status'] === 'New' ? 'selected' : ''; ?>>New</option>
        <option value="In Progress" <?= $lead['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
        <option value="Closed" <?= $lead['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
    </select>
    <br>

    <button type="submit">Update Lead</button>
</form>
