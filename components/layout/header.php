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
<html lang="en" data-bs-theme="light">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo isset($title) ? $title : 'StaffSync HRMS'; ?></title>
        <link rel="icon" href="/hrms/assets/img/SS.png" type="image/png">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
        <link rel="stylesheet" href="/hrms/assets/css/bootstrap.css?v=1.0">
        <link rel="stylesheet" href="/hrms/assets/css/datatable.css?v=1.1">
        <link rel="stylesheet" href="/hrms/assets/css/datatable_bootstrap_responsive.css?v=1.1">
        <link rel="stylesheet" href="/hrms/assets/css/custom.css?v=1.0">
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
        <?php if (isLoggedIn()): ?>
            <div
                class="hrms-header p-1 d-flex justify-content-between align-items-center border-bottom position-fixed top-0 start-0 w-100 bg-body-tertiary">
                <div class="wrapper d-flex align-items-center justify-content-start">
                    <?php if (isLoggedIn()): ?>
                        <button class="btn fs-2 d-sm-none fa-color" id="sidebarToggle" type="button">
                            <i class="ti ti-menu-2"></i>
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
                    <div class="wrapper-of-btn d-flex align-items-center justify-content-end gap-0">
                        <div class="notifications position-relative">
                            <div class="dropdown">
                                <button class="btn dropdown-toggle position-relative action border-0 opacity-75"
                                    type="button" id="notificationButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-bell fs-5"></i>
                                    <span id="notificationBadge"
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                                        0
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="notificationButton"
                                    id="notificationList" style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                                    <li class="dropdown-header d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">Notifications</span>
                                        <a href="#" id="markAllReadBtn" class="text-decoration-none small">Mark all read</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li class="notification-empty text-center text-muted py-3">
                                        <i class="ti ti-bell-off fs-1 d-block mb-2"></i>
                                        No notifications
                                    </li>
                                    <li class="notification-divider d-none">
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li class="text-center">
                                        <a class="dropdown-item small text-primary"
                                            href="/hrms/employee/notifications.php">View
                                            all
                                            notifications</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <button class="btn fs-5" id="toggleThemeBtn" aria-label="Toggle theme">
                            <i class="ti ti-moon"></i>
                        </button>
                    </div>
                    <div class="user-menu d-flex align-items-center gap-2">
                        <?php
                        $role_id = $_SESSION['role_id'] ?? 0;
                        $role_names = [
                            1 => 'Admin',
                            2 => 'Company Owner',
                            3 => 'HR',
                            4 => 'Employee',
                            5 => 'Candidate',
                            6 => 'Manager'
                        ];
                        $role_colors = [
                            1 => 'danger',
                            2 => 'primary',
                            3 => 'success',
                            4 => 'info',
                            5 => 'secondary',
                            6 => 'warning'
                        ];
                        $role_name = $role_names[$role_id] ?? 'Unknown';
                        $role_color = $role_colors[$role_id] ?? 'secondary';
                        ?>
                        <span
                            class="badge bg-<?= $role_color ?>-subtle text-<?= $role_color ?>-emphasis px-2 py-1 d-none d-sm-inline-block">
                            <?= htmlspecialchars($role_name) ?>
                        </span>
                        <div class="dropdown">
                            <button class="btn dropdown-toggle d-flex justify-content-between align-items-center border-0"
                                type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="avatar"></div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="userMenuButton">
                                <li class="dropdown-header d-sm-none">
                                    <small><?= htmlspecialchars($role_name) ?></small>
                                </li>
                                <li class="dropdown-divider d-sm-none"></li>
                                <?php if (in_array($_SESSION['role_id'] ?? 0, [3, 4, 6])): ?>
                                    <li><a class="dropdown-item" href="/hrms/employee/profile.php">
                                            <i class="ti ti-user"></i> Profile</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/hrms/user/account.php"><i
                                            class="ti ti-settings me-2"></i>Settings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="/hrms/auth/logout.php"><i
                                            class="ti ti-logout"></i>
                                        Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="breaker mb-5"></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['toasts'])):
            ?>
            <div class="toast-container position-fixed top-0 end-0 p-3 show" style="z-index: 1055;">
                <?php foreach ($_SESSION['toasts'] as $toast): ?>
                    <div class="toast align-items-center show text-white bg-<?= toastBgClass($toast['type']) ?> mb-2"
                        role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
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
        <script>
            // Update theme icon to match current theme on page load
            document.addEventListener('DOMContentLoaded', function () {
                const themeBtn = document.getElementById('toggleThemeBtn');
                if (themeBtn) {
                    const icon = themeBtn.querySelector('i');
                    const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
                    if (currentTheme === 'dark') {
                        icon.classList.remove('ti-moon');
                        icon.classList.add('ti-sun');
                    }
                }

                // Watch for theme attribute changes and update icon accordingly
                const observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        if (mutation.attributeName === 'data-bs-theme') {
                            const icon = themeBtn.querySelector('i');
                            const theme = document.documentElement.getAttribute('data-bs-theme');
                            if (theme === 'dark') {
                                icon.classList.remove('ti-moon');
                                icon.classList.add('ti-sun');
                            } else {
                                icon.classList.remove('ti-sun');
                                icon.classList.add('ti-moon');
                            }
                        }
                    });
                });

                observer.observe(document.documentElement, { attributes: true });
            });
        </script>