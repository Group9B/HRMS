<?php

$current_page = $_SERVER['PHP_SELF'] ?? '';
switch ($_SESSION['role_id']) {
  case 1:// Super Admin
    $navigation_menu = [
      'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'url' => '/hrms/admin/index.php',
        'permission' => null, // Available to all logged in users
        'submenu' => []
      ],
      'Companies' => [
        'title' => 'Company Management',
        'icon' => 'fas fa-building',
        'url' => '/hrms/admin/companies.php',
        'permission' => null,
        'submenu' => []
      ],
      'Users' => [
        'title' => 'User Management',
        'icon' => 'fas fa-users',
        'url' => '/hrms/admin/user_management.php',
        'permission' => null,
        'submenu' => []
      ],
      'Configuration' => [
        'title' => 'Configuration',
        'icon' => 'fas fa-cog',
        'url' => '/hrms/admin/settings.php',
        'permission' => null,
        'submenu' => []
      ],
      'Reports/Analytics' => [
        'title' => 'Reports Analytics',
        'icon' => 'fas fa-chart-bar',
        'url' => '/hrms/admin/reports.php',
        'permission' => null,
        'submenu' => []
      ],
      'Support' => [
        'title' => 'Support',
        'icon' => 'fas fa-circle-question',
        'url' => '/hrms/admin/support.php',
        'permission' => null,
        'submenu' => []
      ],
    ];
    break;
  case 2://Company Admin
    $navigation_menu = [
      'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'url' => '/hrms/company/index.php',
        'permission' => null, // Available to all logged in users
        'submenu' => []
      ],
      'Users' => [
        'title' => 'Employee Management',
        'icon' => 'fas fa-users',
        'url' => '/hrms/company/employees.php',
        'permission' => null,
        'submenu' => []
      ],
      'Department' => [
        'title' => 'Organization Management',
        'icon' => 'fas fa-sitemap',
        'url' => '/hrms/company/organization.php',
        'permission' => null,
        'submenu' => []
      ],
      'Attendence' => [
        'title' => 'Attendence Management',
        'icon' => 'fas fa-calendar-check',
        'url' => '/hrms/company/attendance.php',
        'permission' => null,
        'submenu' => []
      ],
      'Leave' => [
        'title' => 'Leave Management',
        'icon' => 'fas fa-calendar-alt',
        'url' => '/hrms/company/leaves.php',
        'permission' => null,
        'submenu' => []
      ],
      'Payslips' => [
        'title' => 'Payslips',
        'icon' => 'fas fa-file-invoice-dollar',
        'url' => '/hrms/company/payslips.php',
        'permission' => null,
        'submenu' => []
      ],
      'Settings' => [
        'title' => 'Settings',
        'icon' => 'fas fa-cog',
        'url' => '/hrms/company/company_settings.php',
        'permission' => null,
        'submenu' => []
      ],
    ];
    break;
  case 3://HR Manager
    $navigation_menu = [
      'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'url' => '/hrms/hr/index.php',
        'permission' => null, // Available to all logged in users
        'submenu' => []
      ],
      'Employees' => [
        'title' => 'Employee Management',
        'icon' => 'fas fa-users',
        'url' => '/hrms/company/employees.php',
        'permission' => null,
        'submenu' => []
      ],
      'Departments' => [
        'title' => 'Organization Management',
        'icon' => 'fas fa-sitemap',
        'url' => '/hrms/company/organization.php',
        'permission' => null,
        'submenu' => []
      ],
      'Attendance' => [
        'title' => 'Attendance',
        'icon' => 'fas fa-calendar-check',
        'url' => '/hrms/company/attendance.php',
        'permission' => null,
        'submenu' => []
      ],
      'Leaves' => [
        'title' => 'Leave Management',
        'icon' => 'fas fa-calendar-alt',
        'url' => '/hrms/company/leaves.php',
        'permission' => null,
        'submenu' => []
      ],
      'Payslips' => [
        'title' => 'Payslips',
        'icon' => 'fas fa-file-invoice-dollar',
        'url' => '/hrms/company/payslips.php',
        'permission' => null,
        'submenu' => []
      ],
      /* Add more HR specific menu items here */
    ];
    break;
  case 4://Employee
    $navigation_menu = [
      'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'url' => '/hrms/employee/index.php',
        'permission' => null, // Available to all logged in users
        'submenu' => []
      ],
      'profile' => [
        'title' => 'My Profile',
        'icon' => 'fas fa-user',
        'url' => '/hrms/employee/profile.php',
        'permission' => null,
        'submenu' => []
      ],
      'attendance' => [
        'title' => 'Attendance',
        'icon' => 'fas fa-calendar-check',
        'url' => '/hrms/employee/attendance.php',
        'permission' => null,
        'submenu' => []
      ],
      'leaves' => [
        'title' => 'Leaves',
        'icon' => 'fas fa-plane-departure',
        'url' => '/hrms/company/leaves.php',
        'permission' => null,
        'submenu' => []
      ],
      'payslips' => [
        'title' => 'Payslips',
        'icon' => 'fas fa-file-invoice-dollar',
        'url' => '/hrms/employee/payslips.php',
        'permission' => null,
        'submenu' => []
      ],
      'goals' => [
        'title' => 'Tasks & Goals',
        'icon' => 'fas fa-tasks',
        'url' => '/hrms/employee/goals.php',
        'permission' => null,
        'submenu' => []
      ],
      'feedback' => [
        'title' => 'Feedback',
        'icon' => 'fas fa-comment-dots',
        'url' => '/hrms/employee/feedback.php',
        'permission' => null,
        'submenu' => []
      ],
    ];
    break;
  case 6://Manager
    $navigation_menu = [
      'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'url' => '/hrms/manager/index.php',
        'permission' => null, // Available to all logged in users
        'submenu' => []
      ],
      'team' => [
        'title' => 'Team Management',
        'icon' => 'fas fa-users',
        'url' => '/hrms/manager/team_management.php',
        'permission' => null,
        'submenu' => []
      ],
      'leaves' => [
        'title' => 'Leave Approval',
        'icon' => 'fas fa-calendar-check',
        'url' => '/hrms/manager/leave_approval.php',
        'permission' => null,
        'submenu' => []
      ],
      'tasks' => [
        'title' => 'Task Management',
        'icon' => 'fas fa-tasks',
        'url' => '/hrms/manager/task_management.php',
        'permission' => null,
        'submenu' => []
      ],
      'attendance' => [
        'title' => 'Team Attendance',
        'icon' => 'fas fa-calendar-alt',
        'url' => '/hrms/manager/attendance.php',
        'permission' => null,
        'submenu' => []
      ],
      'performance' => [
        'title' => 'Performance',
        'icon' => 'fas fa-chart-line',
        'url' => '/hrms/manager/performance.php',
        'permission' => null,
        'submenu' => []
      ],
      'teams' => [
        'title' => 'Teams',
        'icon' => 'fas fa-users-cog',
        'url' => '/hrms/manager/teams.php',
        'permission' => null,
        'submenu' => []
      ],
      'payslips' => [
        'title' => 'Payslips',
        'icon' => 'fas fa-file-invoice-dollar',
        'url' => '/hrms/manager/payslips.php',
        'permission' => null,
        'submenu' => []
      ],
    ];
    break;
  case 5:
    redirect("/hrms/auditor/");
    break;
  default:
    http_response_code(404);
    break;
}
// Function to check if menu item should be displayed
function shouldShowMenuItem($item)
{
  if (!isset($item['permission']) || $item['permission'] === null) {
    return true;
  }
  return hasPermission($item['permission']);
}

// Function to check if current page is active
function isActivePage($url)
{
  global $current_page;
  return strpos($current_page, $url) !== false;
}
?>
<div class="flex-column flex-shrink-0 p-3 bg-body-tertiary border-end sidebar d-md-flex position-fixed" id="backdrop"
  style="">
  <ul class="nav nav-pills flex-column mb-auto" id="sidebar">
    <div class="logo d-md-none d-sm-block">
      <div class="wrapper d-flex align-items-center justify-content-between">
        <a href="index.php" class="navbar-brand d-flex align-items-center text-decoration-none">
          <img src="/hrms/assets/img/SS.png" alt="" height="40" class="d-inline-block align-text-top pe-1">
          <h2 class="m-0">Staff Sync</h2>
        </a>
        <button class="btn fs-2 d-lg-none fa-color" id="sidebarToggle" type="button">
          <i class="fas fa-x"></i>
        </button>
      </div>
      <hr>
    </div>
    <?php foreach ($navigation_menu as $key => $item): ?>
      <?php if (shouldShowMenuItem($item)): ?>
        <li class="nav-item mb-2">
          <?php if (empty($item['submenu'])): ?>
            <a class="nav-link d-flex align-items-center py-2 px-3 rounded <?php echo isActivePage($item['url']) ? 'active bg-primary text-white' : 'text-muted'; ?>"
              href="<?php echo htmlspecialchars($item['url']); ?>">
              <i class="<?php echo htmlspecialchars($item['icon']); ?> me-2" style="width: 20px;"></i>
              <span><?php echo htmlspecialchars($item['title']); ?></span>
            </a>
          <?php else: ?>
            <div class="nav-item">
              <a class="nav-link d-flex align-items-center justify-content-between py-2 px-3 rounded text-muted" href="#"
                data-bs-toggle="collapse" data-bs-target="#submenu-<?php echo $key; ?>"
                aria-expanded="<?php echo isActivePage($item['url']) ? 'true' : 'false'; ?>">
                <div class="d-flex align-items-center">
                  <i class="<?php echo htmlspecialchars($item['icon']); ?> me-2" style="width: 20px;"></i>
                  <span><?php echo htmlspecialchars($item['title']); ?></span>
                </div>
                <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
              </a>

              <div class="collapse <?php echo isActivePage($item['url']) ? 'show' : ''; ?>" id="submenu-<?php echo $key; ?>">
                <ul class="nav flex-column ms-3 mt-2">
                  <?php foreach ($item['submenu'] as $subitem): ?>
                    <?php if (shouldShowMenuItem($subitem)): ?>
                      <li class="nav-item">
                        <a class="nav-link py-1 px-3 rounded <?php echo isActivePage($subitem['url']) ? 'active bg-primary text-white' : 'text-muted'; ?>"
                          href="<?php echo htmlspecialchars($subitem['url']); ?>">
                          <i class="<?php echo htmlspecialchars($subitem['icon']); ?> me-2" style="width: 16px;"></i>
                          <span style="font-size: 0.9rem;"><?php echo htmlspecialchars($subitem['title']); ?></span>
                        </a>
                      </li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          <?php endif; ?>
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
</div>
<div class="breaker me-md-5"></div>
<div class="breaker me-md-5"></div>
<div class="breaker me-md-5"></div>
<div class="breaker me-md-5"></div>