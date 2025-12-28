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
    <div class="row" id="dashboardStats"></div>
    <div class="row">
      <div class="col-lg-8 mb-4">
        <div class="card main-content-card shadow-sm">
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
        <div class="card main-content-card shadow-sm">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Quick Actions</h6>
          </div>
          <div class="card-body quick-actions">
            <div class="d-grid gap-2">
              <a href="companies.php" class="btn btn-secondary"><i class="ti ti-plus"></i> Add New
                Company</a>
              <a href="user_management.php" class="btn btn-secondary"><i class="ti ti-user-plus"></i> Create Admin
                User</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-xl-4 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Uploads Storage (5GB)</h6>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center p-sm-0"><canvas id="storageChart"
              class=""
              style="position:relative !important; aspect-ratio: 1/1 !important; height: 280px !important; width: 280px !important;"></canvas>
          </div>
        </div>
      </div>
      <div class="col-xl-4 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">User Role Distribution</h6>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center p-sm-0"><canvas
              id="roleDistributionChart" class=""
              style="aspect-ratio: 1/1 !important; height: 280px !important; width: 280px !important;"></canvas>
          </div>
        </div>
      </div>
      <div class="col-xl-4 mb-4">
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
        const canvas = document.getElementById('storageChart');
        canvas.width = 409;
        canvas.height = 409;
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: ['Used Space (GB)', 'Free Space (GB)'],
            datasets: [{
              data: [result.data.used_gb, result.data.free_gb],
              backgroundColor: ['#4e73df', '#F6AA1C'],
              hoverBackgroundColor: ['#2e59d9', '#9D6807'],
            }]
          },
          options: { responsive: false, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
      }
    });

    // Fetch and render role distribution chart
    fetch('/hrms/api/api_reports_superadmin.php').then(res => res.json()).then(result => {
      if (result.success && result.data.userRole) {
        const roleData = result.data.userRole;
        const canvas = document.getElementById('roleDistributionChart');
        canvas.width = 409;
        canvas.height = 409;
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: Object.keys(roleData),
            datasets: [{
              data: Object.values(roleData),
              backgroundColor: [
                '#4e73df',
                '#1cc28a',
                '#f2613f',
                '#fec260'
              ],
              borderColor: '#fff',
              borderWidth: 2
            }]
          },
          options: {
            responsive: false,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'bottom' }
            }
          }
        });
      }
    });

    initializeTodoList('#todoForm', '#todoList');
  });
</script>