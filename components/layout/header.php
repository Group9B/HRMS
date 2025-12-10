<?php
require_once '../config/db.php';
$additionalScripts = [];
require_once '../includes/functions.php';

$is_site_active = query($mysqli, "SELECT setting_value from system_settings WHERE setting_key = 'maintenance_mode'");

if (isLoggedIn()) {
    if ($_SESSION['role_id'] !== 1) {
        if ($is_site_active['success'] && $is_site_active['data'][0]['setting_value'] == '1') {
            redirect("/hrms/pages/500.php");
        }
    }
}
?>
<!doctype html>
<html lang="en" data-bs-theme-base="neutral">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title><?php echo isset($title) ? $title : 'StaffSync HRMS'; ?></title>
        <link rel="icon" href="/hrms/assets/img/SS.png" type="image/png">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
        <link rel="stylesheet" href="/hrms/assets/css/datatable.css">
        <link rel="stylesheet" href="/hrms/assets/css/datatable_bootstrap_responsive.css">
        <link rel="stylesheet" href="/hrms/assets/css/custom.css">
        <link rel="stylesheet" href="/hrms/assets/css/colors.css">
    </head>

    <body>
        <div id="pageLoader"
            class="page-loader d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100 bg-white"
            style="z-index: 9999;">
            <div class="spinner-border text-primary" role="status"></div>
        </div>

        <div class="page">
            <?php if (isLoggedIn()): ?>
                <header class="navbar navbar-expand-md d-none d-lg-flex d-print-none border-bottom">
                    <div class="container-xl">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
                            aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="navbar-nav flex-row order-md-last ms-auto">
                            <div class="d-none d-md-flex">
                                <div class="nav-item dropdown d-none d-md-flex me-3">
                                    <!-- <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1"
                                        aria-label="Show notifications">
                                        <i class="fas fa-bell text-secondary" style="font-size: 1.2rem;"></i>
                                        <span class="badge bg-red badge-blink"></span>
                                    </a> -->
                                    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">Notifications</h3>
                                            </div>
                                            <div class="list-group list-group-flush list-group-hoverable">
                                                <div class="list-group-item">Example Notification</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="nav-item">
                                    <div class="theme-toggle-wrapper cursor-pointer" id="toggleThemeBtn">
                                        <i class="fas fa-moon text-secondary" style="font-size: 1.2rem;"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="nav-item dropdown ms-3">
                                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown"
                                    aria-label="Open user menu">
                                    <span class="avatar avatar-sm" style="background-image: url(...)"></span>
                                    <div class="d-none d-xl-block ps-2">
                                        <div><?php echo $_SESSION['username'] ?? 'User'; ?></div>
                                        <div class="mt-1 small text-secondary">Role:
                                            <?php echo $_SESSION['role_id'] ?? 'User'; ?>
                                        </div>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <a href="profile.php" class="dropdown-item">Profile</a>
                                    <a href="#" class="dropdown-item">Settings</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="/hrms/auth/logout.php" class="dropdown-item text-danger">Logout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            <?php endif; ?>

            <?php if (!empty($_SESSION['toasts'])): ?>
                <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
                    <?php foreach ($_SESSION['toasts'] as $toast): ?>
                        <div class="toast align-items-center show text-white bg-<?php echo toastBgClass($toast['type']); ?>"
                            role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <?= htmlspecialchars($toast['message']) ?>
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                    aria-label="Close"></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['toasts']); ?>
                </div>
            <?php endif; ?>