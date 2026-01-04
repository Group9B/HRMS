<?php

$current_page = $_SERVER['PHP_SELF'] ?? '';
switch ($_SESSION['role_id']) {
  case 1:// Admin
    $navigation_menu = [
      'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'ti ti-dashboard',
        'url' => '/hrms/admin/index.php',
        'permission' => null, // Available to all logged in users
        'submenu' => []
      ],
      'Companies' => [
        'title' => 'Companies',
        'icon' => 'ti ti-building',
        'url' => '/hrms/admin/companies.php',
        'permission' => null,
        'submenu' => []
      ],
      'Users' => [
        'title' => 'Users',
        'icon' => 'ti ti-users',
        'url' => '/hrms/admin/user_management.php',
        'permission' => null,
        'submenu' => []
      ],
      'Reports/Analytics' => [
        'title' => 'Reports',
        'icon' => 'ti ti-chart-bar',
        'url' => '/hrms/admin/reports.php',
        'permission' => null,
        'submenu' => []
      ],
      'Support' => [
        'title' => 'Support',
        'icon' => 'ti ti-help',
        'url' => '/hrms/admin/support.php',
        'permission' => null,
        'submenu' => []
      ],
      'Configuration' => [
        'title' => 'Configuration',
        'icon' => 'ti ti-settings',
        'url' => '/hrms/admin/settings.php',
        'permission' => null,
        'submenu' => []
      ],
    ];
    break;
  case 2://Company Owner
    $navigation_menu = [
      'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'ti ti-dashboard',
        'url' => '/hrms/company/index.php',
        'permission' => null, // Available to all logged in users
        'submenu' => []
      ],
      'Users' => [
        'title' => 'Employees',
        'icon' => 'ti ti-users',
        'url' => '/hrms/company/employees.php',
        'permission' => null,
        'submenu' => []
      ],
      'Department' => [
        'title' => 'Organization',
        'icon' => 'ti ti-sitemap',
        'url' => '/hrms/company/organization.php',
        'permission' => null,
        'submenu' => []
      ],
      'Recruitment' => [
        'title' => 'Recruitment',
        'icon' => 'ti ti-user-plus',
        'url' => '/hrms/company/recruitment.php',
        'permission' => null,
        'submenu' => []
      ],
      'Attendence' => [
        'title' => 'Attendance',
        'icon' => 'ti ti-calendar-check',
        'url' => '/hrms/company/attendance.php',
        'permission' => null,
        'submenu' => []
      ],
      'Leave' => [
        'title' => 'Leaves',
        'icon' => 'ti ti-calendar',
        'url' => '/hrms/company/leaves.php',
        'permission' => null,
        'submenu' => []
      ],
      'Payslips' => [
        'title' => 'Payslips',
        'icon' => 'ti ti-receipt',
        'url' => '/hrms/company/payslips.php',
        'permission' => null,
        'submenu' => []
      ],
      'Settings' => [
        'title' => 'Settings',
        'icon' => 'ti ti-settings',
        'url' => '/hrms/company/company_settings.php',
        'permission' => null,
        'submenu' => []
      ],
    ];
    break;
  case 3://Human Resource
    $navigation_menu = [
      'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'ti ti-dashboard',
        'url' => '/hrms/hr/index.php',
        'permission' => null, // Available to all logged in users
        'submenu' => []
      ],
      'Employees' => [
        'title' => 'Employees',
        'icon' => 'ti ti-users',
        'url' => '/hrms/company/employees.php',
        'permission' => null,
        'submenu' => []
      ],
      'Departments' => [
        'title' => 'Organization',
        'icon' => 'ti ti-sitemap',
        'url' => '/hrms/company/organization.php',
        'permission' => null,
        'submenu' => []
      ],
      'Attendance' => [
        'title' => 'Attendance',
        'icon' => 'ti ti-calendar-check',
        'url' => '/hrms/company/attendance.php',
        'permission' => null,
        'submenu' => []
      ],
      'Leaves' => [
        'title' => 'Leaves',
        'icon' => 'ti ti-calendar',
        'url' => '/hrms/company/leaves.php',
        'permission' => null,
        'submenu' => []
      ],
      'Recruitment' => [
        'title' => 'Recruitment',
        'icon' => 'ti ti-user-plus',
        'url' => '/hrms/company/recruitment.php',
        'permission' => null,
        'submenu' => []
      ],
      'Payslips' => [
        'title' => 'Payslips',
        'icon' => 'ti ti-receipt',
        'url' => '/hrms/company/payslips.php',
        'permission' => null,
        'submenu' => []
      ],
      'Support' => [
        'title' => 'Support',
        'icon' => 'ti ti-help',
        'url' => '/hrms/pages/index.php',
        'permission' => null,
        'submenu' => []
      ],
    ];
    break;
  case 4://Employee
    $navigation_menu = [
      'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'ti ti-dashboard',
        'url' => '/hrms/employee/index.php',
        'permission' => null,
        'submenu' => []
      ],
      'profile' => [
        'title' => 'My Profile',
        'icon' => 'ti ti-user',
        'url' => '/hrms/employee/profile.php',
        'permission' => null,
        'submenu' => []
      ],
      'attendance' => [
        'title' => 'Attendance',
        'icon' => 'ti ti-calendar-check',
        'url' => '/hrms/employee/attendance.php',
        'permission' => null,
        'submenu' => []
      ],
      'leaves' => [
        'title' => 'Leaves',
        'icon' => 'ti ti-plane',
        'url' => '/hrms/company/leaves.php',
        'permission' => null,
        'submenu' => []
      ],
      'payslips' => [
        'title' => 'Payslips',
        'icon' => 'ti ti-receipt',
        'url' => '/hrms/employee/payslips.php',
        'permission' => null,
        'submenu' => []
      ],
      'goals' => [
        'title' => 'Tasks & Goals',
        'icon' => 'ti ti-checklist',
        'url' => '/hrms/employee/goals.php',
        'permission' => null,
        'submenu' => []
      ],
      'feedback' => [
        'title' => 'Feedback',
        'icon' => 'ti ti-message',
        'url' => '/hrms/employee/feedback.php',
        'permission' => null,
        'submenu' => []
      ],
    ];

    // Check if employee is a team leader
    $emp_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$_SESSION['user_id']]);
    if ($emp_result['success'] && !empty($emp_result['data'])) {
      $employee_id = $emp_result['data'][0]['id'];
      $leader_check = query($mysqli, "
        SELECT COUNT(*) as is_leader 
        FROM team_members 
        WHERE employee_id = ? 
        AND (role_in_team LIKE '%leader%' OR role_in_team LIKE '%lead%')
      ", [$employee_id]);

      if ($leader_check['success'] && $leader_check['data'][0]['is_leader'] > 0) {
        // Add My Team menu item before support
        $navigation_menu['my_team'] = [
          'title' => 'My Team',
          'icon' => 'ti ti-users-group',
          'url' => '/hrms/employee/my_team.php',
          'permission' => null,
          'submenu' => []
        ];
      }
    }

    $navigation_menu['support'] = [
      'title' => 'Support',
      'icon' => 'ti ti-help',
      'url' => '/hrms/pages/index.php',
      'permission' => null,
      'submenu' => []
    ];
    break;
  case 6://Manager
    $navigation_menu = [
      'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'ti ti-dashboard',
        'url' => '/hrms/manager/index.php',
        'permission' => null, // Available to all logged in users
        'submenu' => []
      ],
      'team' => [
        'title' => 'Team',
        'icon' => 'ti ti-users',
        'url' => '/hrms/manager/team_management.php',
        'permission' => null,
        'submenu' => []
      ],
      'leaves' => [
        'title' => 'Approvals',
        'icon' => 'ti ti-calendar-check',
        'url' => '/hrms/company/leaves.php',
        'permission' => null,
        'submenu' => []
      ],
      'tasks' => [
        'title' => 'Tasks',
        'icon' => 'ti ti-checklist',
        'url' => '/hrms/manager/task_management.php',
        'permission' => null,
        'submenu' => []
      ],
      'attendance' => [
        'title' => 'Attendance',
        'icon' => 'ti ti-calendar',
        'url' => '/hrms/manager/attendance.php',
        'permission' => null,
        'submenu' => []
      ],
      'performance' => [
        'title' => 'Performance',
        'icon' => 'ti ti-chart-line',
        'url' => '/hrms/manager/performance.php',
        'permission' => null,
        'submenu' => []
      ],
      'teams' => [
        'title' => 'Teams',
        'icon' => 'ti ti-users-group',
        'url' => '/hrms/manager/teams.php',
        'permission' => null,
        'submenu' => []
      ],
      'payslips' => [
        'title' => 'Payslips',
        'icon' => 'ti ti-receipt',
        'url' => '/hrms/manager/payslips.php',
        'permission' => null,
        'submenu' => []
      ],
      'support' => [
        'title' => 'Support',
        'icon' => 'ti ti-help',
        'url' => '/hrms/pages/index.php',
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
<div class="flex-column flex-shrink-0 p-3 bg-body-tertiary border-end sidebar d-md-flex position-fixed" id="backdrop">
  <ul class="nav nav-pills flex-column" id="sidebar">
    <div class="logo d-md-none d-sm-block">
      <div class="wrapper d-flex align-items-center justify-content-between">
        <a href="index.php" class="navbar-brand d-flex align-items-center text-decoration-none">
          <img src="/hrms/assets/img/SS.png" alt="" height="40" class="d-inline-block align-text-top pe-1">
          <h2 class="m-0">Staff Sync</h2>
        </a>
        <button class="btn fs-2 d-lg-none fa-color" id="sidebarToggle" type="button">
          <i class="ti ti-x"></i>
        </button>
      </div>
      <hr>
    </div>
    <?php foreach ($navigation_menu as $key => $item): ?>
      <?php if (shouldShowMenuItem($item)): ?>
        <li class="nav-item mb-2 <?php echo isActivePage($item['url']) ? 'sidebar-active' : 'text-muted'; ?>">
          <?php if (empty($item['submenu'])): ?>
            <a class="nav-link d-flex align-items-center py-2 px-3 rounded text-muted"
              href="<?php echo htmlspecialchars($item['url']); ?>">
              <i class="<?php echo htmlspecialchars($item['icon']); ?> me-2 fs-5" style="width: 20px;"></i>
              <span style="font-size: 0.95rem;"><?php echo htmlspecialchars($item['title']); ?></span>
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
                <i class="ti ti-chevron-down" style="font-size: 0.8rem;"></i>
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