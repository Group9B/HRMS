<?php
require_once '../config/db.php';
$additionalScripts = [];
require_once '../includes/functions.php';

$is_site_active = query($mysqli, "SELECT setting_value from system_settings WHERE setting_key = 'maintenance_mode'");

// if(isLoggedIn()){
//     echo $_SESSION['role_id'];
//     if(!$_SESSION['role_id']===1){
//         echo 'here';
//         if ($is_site_active['success'] && $is_site_active['data'][0]['setting_value'] == '1') {
//             redirect("/hrms/pages/500.php");
//         }
//     }
// }

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
        <link rel="stylesheet" href="/hrms/assets/css/datatable_bootstrap_responsive.css">
        <link rel="stylesheet" href="/hrms/assets/css/custom.css">
    </head>

    <body class="bg-body">
        <div class="bg-body d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100 "
            style="z-index: 9999;" id="pageLoader">
            <div class="text-center">
                <div class="spinner-border text-primary fs-2 mb-3 d-flex justify-content-center align-items-center"
                    style="width: 5rem; height: 5rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                    <div class="spinner-border text-success fs-2 d-flex justify-content-center align-items-center"
                        style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                        <div class="spinner-border text-warning fs-6" style="width: 1rem; height: 1rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="hrms-header p-1 d-flex justify-content-between align-items-center border-bottom position-fixed top-0 start-0 w-100 bg-body-tertiary">
            <div class="wrapper d-flex align-items-center justify-content-start">
                <?php if (isLoggedIn()): ?>
                    <button class="btn fs-2 d-sm-none fa-color" id="sidebarToggle" type="button">
                        <i class="fas fa-bars"></i>
                    </button>
                <?php endif; ?>
                <div class="logo">
                    <a href="index.php" class="navbar-brand d-flex align-items-center text-decoration-none">
                        <img src="/hrms/assets/img/SS.png" alt="" height="40"
                            class="d-inline-block align-text-top pe-1">
                        <h2 class="m-0 d-none d-md-block h4 fw-medium">Staff Sync</h2>
                        <h2 class="m-0 d-block d-md-none h4">SS</h2>
                    </a>
                </div>
            </div>
            <div class="wrapper d-flex align-items-center justify-content-end gap-3">
                <?php if (isLoggedIn()): ?>
                    <?php if (1 === 0): ?>
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
                    <?php endif; ?>
                    <div class="user-menu">
                        <div class="dropdown">
                            <button class="btn dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fas fa-user svg"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="userMenuButton">
                                <li><a class="dropdown-item" href="ok.php">Profile</a></li>
                                <li><a class="dropdown-item" href="#">Settings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <div class="theme-toggle-wrapper">
                                        <div id="toggleThemeBtn" class="theme-toggle">
                                            <div class="toggle-circle"></div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="/hrms/auth/logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="breaker mb-5"></div>

        <?php if (!empty($_SESSION['toasts'])):
            ?>
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