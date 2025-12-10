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
      'Recruitment' => [
        'title' => 'Recruitment Management',
        'icon' => 'fas fa-user-plus',
        'url' => '/hrms/company/recruitment.php',
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
<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
      aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <h1 class="navbar-brand navbar-brand-autodark">
      <a href="index.php">
        <img src="/hrms/assets/img/SS.png" width="110" height="32" alt="Staff Sync" class="navbar-brand-image">
        Staff Sync
      </a>
    </h1>

    <div class="collapse navbar-collapse" id="sidebar-menu">
      <ul class="navbar-nav pt-lg-3">
        <?php foreach ($navigation_menu as $key => $item): ?>
          <?php if (shouldShowMenuItem($item)): ?>
            <?php if (empty($item['submenu'])): ?>
              <li class="nav-item <?php echo isActivePage($item['url']) ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo htmlspecialchars($item['url']); ?>">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                  </span>
                  <span class="nav-link-title">
                    <?php echo htmlspecialchars($item['title']); ?>
                  </span>
                </a>
              </li>
            <?php else: ?>
              <li class="nav-item dropdown <?php echo isActivePage($item['url']) ? 'active' : ''; ?>">
                <a class="nav-link dropdown-toggle" href="#navbar-<?php echo $key; ?>" data-bs-toggle="dropdown"
                  data-bs-auto-close="false" role="button"
                  aria-expanded="<?php echo isActivePage($item['url']) ? 'true' : 'false'; ?>">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                  </span>
                  <span class="nav-link-title">
                    <?php echo htmlspecialchars($item['title']); ?>
                  </span>
                </a>
                <div class="dropdown-menu <?php echo isActivePage($item['url']) ? 'show' : ''; ?>">
                  <div class="dropdown-menu-columns">
                    <div class="dropdown-menu-column">
                      <?php foreach ($item['submenu'] as $subitem): ?>
                        <?php if (shouldShowMenuItem($subitem)): ?>
                          <a class="dropdown-item <?php echo isActivePage($subitem['url']) ? 'active' : ''; ?>"
                            href="<?php echo htmlspecialchars($subitem['url']); ?>">
                            <?php echo htmlspecialchars($subitem['title']); ?>
                          </a>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </li>
            <?php endif; ?>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</aside>