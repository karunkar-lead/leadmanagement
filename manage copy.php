<?php
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
            <td><?= $lead['id']; ?></td>
            <td><?= $lead['name']; ?></td>
            <td><?= $lead['email']; ?></td>
            <td><?= $lead['phone']; ?></td>
            <td><?= $lead['status']; ?></td>
            <td>
                <a href="edit.php?id=<?= $lead['id']; ?>">Edit</a>
                <a href="delete.php?id=<?= $lead['id']; ?>">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
