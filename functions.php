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

    // Check if the role exists and has the specified permission
    if ($role && in_array($permission, $permissions[$role])) {
        return true;
    }
    return false;
}


// function hasPermission($required_role) {
    // session_start();
    
    // // Check if the user is logged in
    // if (!isset($_SESSION['role_id'])) {
    //     die("Access denied. You must be logged in.");
    // }

    // // If the user's role does not match the required role, deny access
    // if ($_SESSION['role_id'] !== $required_role) {
    //     die("You do not have permission to access this page.");
    // }
// }
?>
