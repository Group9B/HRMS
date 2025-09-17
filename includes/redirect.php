<?php
// This file acts as a central router after a user logs in.

// Start session to access user's role
session_start();

require_once '../config/db.php';
require_once '../includes/functions.php';

// First, ensure the user is actually logged in.
if (!isLoggedIn()) {
    // If not, send them back to the login page.
    redirect('/hrms/auth/login.php');
    exit();
}

// Get the user's role ID from the session.
$role_id = $_SESSION['role_id'] ?? 0;

$redirect_path = '';

// Determine the correct dashboard path based on the user's role.
switch ($role_id) {
    case 1: // Super Admin
        $redirect_path = '/hrms/admin/index.php';
        break;
    case 2: // Company Admin
        $redirect_path = '/hrms/admin/index.php';
        break;
    case 3: // HR Manager
        $redirect_path = '/hrms/admin/index.php';
        break;
    case 4: // Employee
        $redirect_path = '/hrms/employee/index.php';
        break;
    case 6:
        $redirect_path = '/hrms/manager/index.php';
        break;
    default:
        // If the user has an unknown role, it's safest to log them out
        // and send them back to the login page for security.
        session_destroy();
        redirect('/hrms/auth/login.php?error=invalid_role');
        exit();
}

// Execute the redirection.
redirect($redirect_path);
exit();
?>