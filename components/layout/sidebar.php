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
        'title' => 'Companies',
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
        'title' => 'Deparments Management',
        'icon' => 'fas fa-sitemap',
        'url' => '/hrms/company/departments.php',
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
      'Settings' => [
        'title' => 'Settings',
        'icon' => 'fas fa-cog',
        'url' => '/hrms/company/company_settings.php',
        'permission' => null,
        'submenu' => []
      ],
    ];
    break;
  case 3:
    $navigation_menu = [
      'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'url' => '/hrms/hr/index.php',
        'permission' => null, // Available to all logged in users
        'submenu' => []
      ],
      'Employees' => [
        'title' => 'Employees',
        'icon' => 'fas fa-users',
        'url' => '/hrms/company/employees.php',
        'permission' => null,
        'submenu' => []
      ],
      'Departments' => [
        'title' => 'Departments',
        'icon' => 'fas fa-sitemap',
        'url' => '/hrms/hr/departments.php',
        'permission' => null,
        'submenu' => []
      ],
      'Designations' => [
        'title' => 'Designations',
        'icon' => 'fas fa-id-badge',
        'url' => '/hrms/hr/designations.php',
        'permission' => null,
        'submenu' => []
      ],
      'Attendance' => [
        'title' => 'Attendance',
        'icon' => 'fas fa-calendar-check',
        'url' => '/hrms/hr/attendance.php',
        'permission' => null,
        'submenu' => []
      ],
      'Leaves' => [
        'title' => 'Leave Management',
        'icon' => 'fas fa-calendar-alt',
        'url' => '/hrms/hr/leaves.php',
        'permission' => null,
        'submenu' => []
      ],
      /* Add more HR specific menu items here */
    ];
    break;
  case 4:
    redirect("/hrms/employee/");
    break;
  case 5:
    redirect("/hrms/auditor/");
    break;
  default:
    http_response_code(404);
    break;
}
/* $navigation_menu = [
  'dashboard' => [
    'title' => 'Dashboard',
    'icon' => 'fas fa-tachometer-alt',
    'url' => '/hrms/admin/',
    'permission' => null, // Available to all logged in users
    'submenu' => []
  ],
  'admin' => [
    'title' => 'Administration',
    'icon' => 'fas fa-cogs',
    'url' => '#',
    'permission' => 'user_management',
    'submenu' => [
      [
        'title' => 'User Management',
        'url' => '/hrms/admin/user-management.php',
        'icon' => 'fas fa-users',
        'permission' => 'user_management'
      ],
      [
        'title' => 'Company Settings',
        'url' => '/hrms/admin/company-settings.php',
        'icon' => 'fas fa-building',
        'permission' => 'company_settings'
      ],
      [
        'title' => 'System Settings',
        'url' => '/hrms/admin/system-settings.php',
        'icon' => 'fas fa-sliders-h',
        'permission' => 'system_settings'
      ],
      [
        'title' => 'Email Templates',
        'url' => '/hrms/admin/email-templates.php',
        'icon' => 'fas fa-envelope',
        'permission' => 'email_templates'
      ]
    ]
  ],
  'hr' => [
    'title' => 'Human Resources',
    'icon' => 'fas fa-user-tie',
    'url' => '#',
    'permission' => 'employee_management',
    'submenu' => [
      [
        'title' => 'Employees',
        'url' => '/hrms/hr/employees.php',
        'icon' => 'fas fa-users',
        'permission' => 'employee_management'
      ],
      [
        'title' => 'Departments',
        'url' => '/hrms/hr/departments.php',
        'icon' => 'fas fa-sitemap',
        'permission' => 'employee_management'
      ],
      [
        'title' => 'Designations',
        'url' => '/hrms/hr/designations.php',
        'icon' => 'fas fa-id-badge',
        'permission' => 'employee_management'
      ],
      [
        'title' => 'Shifts',
        'url' => '/hrms/hr/shifts.php',
        'icon' => 'fas fa-clock',
        'permission' => 'employee_management'
      ]
    ]
  ],
  'attendance' => [
    'title' => 'Attendance',
    'icon' => 'fas fa-calendar-check',
    'url' => '/hrms/hr/attendance.php',
    'permission' => 'attendance_management',
    'submenu' => []
  ],
  'payroll' => [
    'title' => 'Payroll',
    'icon' => 'fas fa-money-bill-wave',
    'url' => '/hrms/hr/payroll.php',
    'permission' => 'payroll_management',
    'submenu' => []
  ],
  'leaves' => [
    'title' => 'Leave Management',
    'icon' => 'fas fa-calendar-alt',
    'url' => '/hrms/hr/leaves.php',
    'permission' => 'leave_management',
    'submenu' => []
  ],
  'reports' => [
    'title' => 'Reports',
    'icon' => 'fas fa-chart-bar',
    'url' => '/hrms/reports/',
    'permission' => 'reports',
    'submenu' => []
  ],
  'audit' => [
    'title' => 'Audit & Logs',
    'icon' => 'fas fa-history',
    'url' => '/hrms/admin/audit-logs.php',
    'permission' => 'audit_logs',
    'submenu' => []
  ]
]; */
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
<div class="flex-column flex-shrink-0 p-3 bg-body border-end sidebar d-md-flex" id="backdrop"
  style="width: 200px; min-height: 100vh;">
  <ul class="nav nav-pills flex-column mb-auto" id="sidebar"><?php foreach ($navigation_menu as $key => $item): ?>
      <?php if (shouldShowMenuItem($item)): ?>
        <li class="nav-item mb-2">
          <?php if (empty($item['submenu'])): ?>
            <!-- Single Menu Item -->
            <a class="nav-link d-flex align-items-center py-2 px-3 rounded <?php echo isActivePage($item['url']) ? 'active bg-primary text-white' : 'text-muted'; ?>"
              href="<?php echo htmlspecialchars($item['url']); ?>">
              <i class="<?php echo htmlspecialchars($item['icon']); ?> me-2" style="width: 20px;"></i>
              <span><?php echo htmlspecialchars($item['title']); ?></span>
            </a>
          <?php else: ?>
            <!-- Menu Item with Submenu -->
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
