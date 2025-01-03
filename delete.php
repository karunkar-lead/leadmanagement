<?php
require 'config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Lead ID.");
}
$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
$stmt->execute([$id]);

header("Location: manage.php");
exit;
?>
