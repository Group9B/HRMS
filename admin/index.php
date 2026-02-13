<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Dashboard";

if (!isLoggedIn()) {
  redirect("/hrms/auth/login.php");
}
if ($_SESSION['role_id'] !== 1) {
  redirect("/hrms/unauthorized.php");
}
// --- CORRECTED DATA FETCHING for Super Admin ---
$active_companies = query($mysqli, "SELECT COUNT(*) as count FROM companies")['data'][0]['count'] ?? 0;
$total_users = query($mysqli, "SELECT COUNT(*) as count FROM users WHERE role_id != 1")['data'][0]['count'] ?? 0;
$total_employees = query($mysqli, "SELECT COUNT(*) as count FROM employees")['data'][0]['count'] ?? 0;
$open_tickets = query($mysqli, "SELECT COUNT(*) as count FROM support_tickets WHERE status = 'open'")['data'][0]['count'] ?? 0;

// Recent Companies List
$recent_companies_result = query($mysqli, "SELECT c.id, c.name, c.created_at, (SELECT COUNT(*) FROM users u WHERE u.company_id = c.id) as user_count FROM companies c ORDER BY c.created_at DESC LIMIT 4");
$recent_companies = $recent_companies_result['success'] ? $recent_companies_result['data'] : [];

require_once '../components/layout/header.php';
// $additionalScripts[] = '/hrms/assets/js/chart.js'
?>

<div class="d-flex">
  <?php require_once '../components/layout/sidebar.php'; ?>
  <div class="p-3 p-md-4" style="flex: 1;">
    <!-- Stats Row -->
    <div class="row" id="dashboardStats"></div>

    <!-- Recent Companies + User Role Distribution -->
    <div class="row">
      <div class="col-lg-8 mb-4">
        <div class="card main-content-card shadow-sm h-100">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Recent Companies</h6>
          </div>
          <div class="card-body">
            <div class="recent-companies-list">
              <?php if (!empty($recent_companies)):
                foreach ($recent_companies as $company): ?>
                  <div class="list-item">
                    <div>
                      <div class="company-name"><?= htmlspecialchars($company['name']); ?></div>
                      <div class="user-count text-muted"><?= $company['user_count']; ?> users</div>
                    </div>
                    <div class="d-flex align-items-center">
                      <div class="created-at text-muted"><?= date('F j, Y', strtotime($company['created_at'])); ?></div>
                      <div class="vr mx-2"></div><span class="badge bg-success-subtle text-success-emphasis">Active</span>
                    </div>
                  </div>
                <?php endforeach; else: ?>
                <div class="text-center text-muted p-4">No recent companies to display.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">User Role Distribution</h6>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center p-sm-0">
            <canvas id="roleDistributionChart"
              style="aspect-ratio: 1/1 !important; height: 280px !important; width: 280px !important;"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Uploads Storage + Application Logs -->
    <div class="row">
      <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Uploads Storage (5GB)</h6>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center p-sm-0">
            <canvas id="storageChart"
              style="position:relative !important; aspect-ratio: 1/1 !important; height: 280px !important; width: 280px !important;"></canvas>
          </div>
        </div>
      </div>
      <?php
      $logFile = '../logs/app.log';
      if (file_exists($logFile)):
        ?>
        <div class="col-lg-8 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
              <h6 class="m-0 font-weight-bold">Application Logs (Latest 50)</h6>
              <button class="btn btn-sm btn-outline-secondary" onclick="refreshLogs()"><i class="ti ti-refresh"></i>
                Refresh</button>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
              <div id="logContent" style="font-family: 'Courier New', monospace; font-size: 0.875rem;"></div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Actions + To-Do List -->
    <div class="row">
      <div class="col-lg-6 mb-4">
        <div class="card main-content-card shadow-sm h-100">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Quick Actions</h6>
          </div>
          <div class="card-body quick-actions">
            <div class="d-grid gap-2">
              <a href="companies.php" class="btn btn-secondary"><i class="ti ti-plus"></i> Add New Company</a>
              <a href="user_management.php" class="btn btn-secondary"><i class="ti ti-user-plus"></i> Create Admin
                User</a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">My To-Do List</h6>
          </div>
          <div class="card-body">
            <form id="todoForm" class="d-flex mb-3">
              <input type="text" name="task" class="form-control me-2" placeholder="Add a new task..." required>
              <button type="submit" class="btn btn-primary">Add</button>
            </form>
            <ul class="todo-list" id="todoList"></ul>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>

<script>
  $(function () {
    const COLORS = {
      primary: '#4e73df',
      success: '#1cc88a',
      info: '#36b9cc',
      warning: '#f6c23e',
      danger: '#e74a3b',
      secondary: '#858796'
    };

    let charts = {};

    function getChartTheme() {
      const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
      return {
        textColor: isDark ? '#adb5bd' : '#6e707e',
        gridColor: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)',
        borderColor: isDark ? '#444' : '#e3e6f0'
      };
    }

    function hexToRgba(hex, alpha) {
      const r = parseInt(hex.slice(1, 3), 16);
      const g = parseInt(hex.slice(3, 5), 16);
      const b = parseInt(hex.slice(5, 7), 16);
      return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    function updateChartTheme(chart) {
      if (!chart) return;
      const theme = getChartTheme();
      if (chart.options.scales) {
        ['x', 'y'].forEach(axis => {
          if (chart.options.scales[axis]) {
            chart.options.scales[axis].grid.color = theme.gridColor;
            chart.options.scales[axis].ticks.color = theme.textColor;
          }
        });
      }
      if (chart.options.plugins?.legend?.labels) chart.options.plugins.legend.labels.color = theme.textColor;
      chart.update();
    }

    const observer = new MutationObserver(() => Object.values(charts).forEach(c => updateChartTheme(c)));
    observer.observe(document.documentElement, { attributes: true });

    // Render dashboard stats using modular function
    const stats = [
      { label: 'Active Companies', value: <?= $active_companies ?>, color: 'primary', icon: 'building' },
      { label: 'Active Users', value: <?= $total_users ?>, color: 'success', icon: 'users' },
      { label: 'Total Employees', value: <?= $total_employees ?>, color: 'info', icon: 'users-group' },
      { label: 'Open Support Tickets', value: <?= $open_tickets ?>, color: 'warning', icon: 'help' }
    ];

    renderStatCards('dashboardStats', stats);

    fetch('api_dashboard.php?action=get_storage_usage').then(res => res.json()).then(result => {
      if (result.success) {
        const theme = getChartTheme();
        const ctx = document.getElementById('storageChart').getContext('2d');
        charts.storage = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: ['Used Space (GB)', 'Free Space (GB)'],
            datasets: [{
              data: [result.data.used_gb, result.data.free_gb],
              backgroundColor: [
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)'
              ],
              borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)'
              ],
              borderWidth: 1.5
            }]
          },
          options: {
            responsive: false,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: { color: theme.textColor, usePointStyle: true, padding: 20 }
              }
            }
          }
        });
      }
    });

    // Fetch and render role distribution chart
    fetch('/hrms/api/api_reports_superadmin.php').then(res => res.json()).then(result => {
      if (result.success && result.data.userRole) {
        const theme = getChartTheme();
        const roleData = result.data.userRole;
        const ctx = document.getElementById('roleDistributionChart').getContext('2d');
        charts.roles = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: Object.keys(roleData),
            datasets: [{
              data: Object.values(roleData),
              backgroundColor: [
                'rgba(54, 162, 235, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 99, 132, 0.2)'
              ],
              borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 99, 132, 1)'
              ],
              borderWidth: 1.5
            }]
          },
          options: {
            responsive: false,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: { color: theme.textColor, usePointStyle: true, padding: 20 }
              }
            }
          }
        });
      }
    });

    initializeTodoList('#todoForm', '#todoList');

    // Load application logs if widget exists
    if (document.getElementById('logContent')) {
      loadLogs();
    }
  });

  function loadLogs() {
    fetch('api_dashboard.php?action=get_logs')
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          const logContent = document.getElementById('logContent');
          if (result.data.logs.length === 0) {
            logContent.innerHTML = '<div class="text-muted">No logs available</div>';
          } else {
            logContent.innerHTML = result.data.logs.map(line => {
              const escaped = escapeHTML(line);
              let className = 'text-body-secondary';
              if (line.includes('ERROR') || line.includes('error')) className = 'text-danger';
              else if (line.includes('WARNING') || line.includes('warning')) className = 'text-warning';
              else if (line.includes('SUCCESS') || line.includes('success')) className = 'text-success';
              return `<div class="${className}">${escaped}</div>`;
            }).join('');
          }
        } else {
          document.getElementById('logContent').innerHTML = '<div class="text-danger">Failed to load logs</div>';
        }
      })
      .catch(() => {
        document.getElementById('logContent').innerHTML = '<div class="text-danger">Error loading logs</div>';
      });
  }

  function refreshLogs() {
    loadLogs();
    showToast('Logs refreshed', 'success');
  }
</script>