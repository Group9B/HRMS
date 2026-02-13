<?php
// Fix includes to work from both root and subdirectories
$root_path = dirname(__DIR__, 2); // Goes up 2 levels from components/layout to HRMS root
require_once $root_path . '/config/db.php';
$additionalScripts = [];
require_once $root_path . '/includes/functions.php';
$hideHeader = $hideHeader ?? false; // allow pages to suppress header/navbar

$is_site_active = query($mysqli, "SELECT setting_value from system_settings WHERE setting_key = 'maintenance_mode'");

if (isLoggedIn()) {
    if ($_SESSION['role_id'] !== 1) {
        if ($is_site_active['success'] && $is_site_active['data'][0]['setting_value'] == '1') {
            redirect("/hrms/pages/500.php");
        }
    }
}
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
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($title) ? $title : 'StaffSync HRMS'; ?></title>
    <link rel="icon" href="/hrms/assets/img/SS.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">
    <link rel="stylesheet" href="/hrms/assets/css/bootstrap.css?v=1.0">
    <link rel="stylesheet" href="/hrms/assets/css/datatable.css?v=1.1">
    <link rel="stylesheet" href="/hrms/assets/css/datatable_bootstrap_responsive.css?v=1.1">
    <link rel="stylesheet" href="/hrms/assets/css/custom.css?v=1.0">
    <link rel="stylesheet" href="/hrms/assets/css/skeleton.css?v=2.0">
</head>

<body class="bg-body">
    </div>
    </div>
    </div>
    </div>
    <?php if (!$hideHeader && isLoggedIn()): ?>
        <!-- Dashboard Header for Logged-in Users -->
        <div
            class="hrms-header p-1 d-flex justify-content-between align-items-center border-bottom position-fixed top-0 start-0 w-100 bg-body-tertiary">
            <div class="wrapper d-flex align-items-center justify-content-start">
                <?php if (isLoggedIn()): ?>
                    <button class="btn fs-2 d-sm-none fa-color" id="sidebarToggle" type="button">
                        <i class="ti ti-menu-2"></i>
                    </button>
                <?php endif; ?>
                <div class="logo">
                    <a href="/hrms/index.php" class="navbar-brand d-flex align-items-center text-decoration-none">
                        <img src="/hrms/assets/img/SS.png" alt="" height="40" class="d-inline-block align-text-top pe-1">
                        <h2 class="m-0 d-none d-md-block h4 fw-medium">Staff Sync</h2>
                        <h2 class="m-0 d-block d-md-none h4">SS</h2>
                    </a>
                </div>
            </div>
            <div class="wrapper d-flex align-items-center justify-content-end gap-3">
                <div class="wrapper-of-btn d-flex align-items-center justify-content-end gap-0">
                    <div class="notifications position-relative">
                        <div class="dropdown">
                            <button class="btn dropdown-toggle position-relative action border-0" type="button"
                                id="notificationButton" data-bs-toggle="dropdown" aria-expanded="false">
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
                                    <a class="dropdown-item small text-primary" href="/hrms/employee/notifications.php">View
                                        all
                                        notifications</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <button class="btn fs-5 border-0" id="toggleThemeBtn" aria-label="Toggle theme">
                        <i class="ti ti-moon"></i>
                    </button>
                </div>
                <div class="user-menu d-flex align-items-center gap-2">
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
    <?php elseif (!$hideHeader): ?>
        <!-- Public Navbar for Guests -->
        <nav class="navbar navbar-expand-lg bg-body sticky-top shadow-sm py-3">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center fw-bold text-primary" href="/hrms/index.php">
                    <img src="/hrms/assets/img/SS.png" alt="Logo" width="30" height="30"
                        class="d-inline-block align-text-top me-2">
                    StaffSync
                </a>

                <!-- Custom Toggler Trigger -->
                <button class="navbar-toggler border-0 shadow-none z-3" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                    <i class="ti ti-menu-2 ti-lg" id="navbarTogglerIcon"></i>
                </button>

                <!-- Offcanvas Menu (Right Side) -->
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                    aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header justify-content-between">
                        <h5 class="offcanvas-title fw-bold text-primary" id="offcanvasNavbarLabel">StaffSync</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-end flex-grow-1 align-items-center gap-3">
                            <li class="nav-item">
                                <a class="nav-link text-body fw-semibold" href="/hrms/pages/features.php">Features</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-body fw-semibold" href="/hrms/pages/benefits.php">Benefits</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-body fw-semibold" href="/hrms/pages/features.php">Pricing</a>
                            </li>
                            <li class="nav-item ms-lg-2 w-20 w-lg-auto">
                                <a href="/hrms/auth/login.php"
                                    class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-semibold w-100 w-lg-auto">Log
                                    In</a>
                            </li>
                            <li class="nav-item w-20 w-lg-auto">
                                <a href="/hrms/pages/register.php"
                                    class="btn btn-primary btn-sm px-3 rounded-pill fw-semibold shadow-sm w-100 w-lg-auto">Register</a>
                            </li>
                        </ul>
                        <!-- Theme Toggle (ID matches main.js) -->
                        <button class="btn btn-link text-primary p-0 ms-3 me-auto text-decoration-none" id="toggleThemeBtn"
                            aria-label="Toggle theme">
                            <i class="ti ti-moon fs-5"></i>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    <?php endif; ?>
    <?php if (!empty($_SESSION['toasts'])):
        ?>
        <div class="toast-container position-fixed top-0 end-0 p-3 show" style="z-index: 1055;">
            <?php foreach ($_SESSION['toasts'] as $toast): ?>
                <div class="toast align-items-center show text-white bg-<?= toastBgClass($toast['type']) ?> mb-2" role="alert"
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

    <?php
    $trialDays = getTrialDaysLeft();
    if (!is_null($trialDays)):
        $alertClass = ($trialDays <= 3) ? 'warning' : 'info';
        $msg = ($trialDays <= 3)
            ? "<strong>Action Required:</strong> Only $trialDays days left in your free trial."
            : "You have $trialDays days remaining in your free trial.";
        ?>
        <div class="alert alert-<?= $alertClass ?> text-center mb-0 fixed-bottom border-top border-<?= $alertClass ?> py-2 shadow-lg"
            style="z-index: 1060;">
            <?= $msg ?> <a href="#" class="btn btn-sm btn-dark ms-2" onclick="alert('Payment Gateway Required')">Upgrade
                Now</a>
        </div>
    <?php endif; ?>

    <!-- Theme Initialization Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const html = document.documentElement;
            // Load saved theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            html.setAttribute('data-bs-theme', savedTheme);
        });
    </script>