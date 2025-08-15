<?php
require_once '../includes/functions.php';
?>
<html lang="en" data-bs-theme="light">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo isset($title) ? $title : 'StaffSync HRMS'; ?></title>
        <link rel="icon" href="/hrms/assets/img/SS.png" type="image/png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
            integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="/hrms/assets/css/bootstrap.css">
        <link rel="stylesheet" href="/hrms/assets/css/datatable.css">
        <!-- <link rel="stylesheet" href="/hrms/assets/css/theme.css"> -->
        <link rel="stylesheet" href="/hrms/assets/css/custom.css">
    </head>

    <body class="bg-body">
        <div class="hrms-header p-1 d-flex justify-content-between align-items-center border-bottom">
            <div class="logo">
                <a href="index.php" class="navbar-brand d-flex align-items-center text-decoration-none">
                    <img src="/hrms/assets/img/SS.png" alt="" height="40" class="d-inline-block align-text-top">
                    <h2 class="m-0">Staff Sync</h2>
                </a>
            </div>
            <div class="wrapper d-flex align-items-center justify-content-end gap-3">
                <!-- theme toggle -->
                <div class="theme-toggle-wrapper">
                    <div id="toggleThemeBtn" class="theme-toggle">
                        <div class="toggle-circle"></div>
                    </div>
                </div>
                <!-- notifications -->
                <div class="notifications">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="notificationButton"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell svg"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationButton">
                            <li><a class="dropdown-item" href="#">New employee added</a></li>
                            <li><a class="dropdown-item" href="#">Leave request approved</a></li>
                            <li><a class="dropdown-item" href="#">Payroll processed</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="#">View all notifications</a></li>
                        </ul>
                    </div>
                </div>
                <div class="user-menu">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-user svg"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="userMenuButton">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="/hrms/auth/logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($_SESSION['toasts'])): ?>
            <div class="toast-container position-fixed top-0 end-0 p-3 show" style="z-index: 1055;">
                <?php foreach ($_SESSION['toasts'] as $toast): ?>
                    <div class="toast align-items-center text-white bg-<?= toastBgClass($toast['type']) ?> mb-2" role="alert"
                        aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
                        <div class="d-flex">
                            <div class="toast-body w-100">
                                <?= htmlspecialchars($toast['message']) ?>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto p-2" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['toasts']); ?>
        <?php endif; ?>