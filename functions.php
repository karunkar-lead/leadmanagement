<?php

function hasPermission($pdo, $role_id, $permission) {
    $permissions = [
        'Admin' => ['view', 'edit', 'delete'],
        'Manager' => ['view', 'edit'],
        'User' => ['view'],
    ];

    $stmt = $pdo->prepare("SELECT role_name FROM roles WHERE id = ?");
    $stmt->execute([$role_id]);
    $role = $stmt->fetchColumn();

    if ($role && in_array($permission, $permissions[$role])) {
        return true;
    }
    return false;
}
?>
