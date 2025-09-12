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
$total_employees = query($mysqli, "SELECT COUNT(*) as count FROM employees")['data'][0]['count'] ?? 0;
$pending_leaves = query($mysqli, "SELECT COUNT(*) as count FROM leaves WHERE status = 'pending'")['data'][0]['count'] ?? 0;
$open_tickets = query($mysqli, "SELECT COUNT(*) as count FROM support_tickets WHERE status = 'open'")['data'][0]['count'] ?? 0;

// Recent Companies List
$recent_companies_result = query($mysqli, "SELECT c.id, c.name, c.created_at, (SELECT COUNT(*) FROM users u WHERE u.company_id = c.id) as user_count FROM companies c ORDER BY c.created_at DESC LIMIT 4");
$recent_companies = $recent_companies_result['success'] ? $recent_companies_result['data'] : [];

require_once '../components/layout/header.php';
$additionalScripts[] = '/hrms/assets/js/chart.js'
  ?>

<div class="d-flex">
  <?php require_once '../components/layout/sidebar.php'; ?>
  <div class="p-3 p-md-4" style="flex: 1;">
    <h2 class="h3 mb-4 text-gray-800"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>

    <!-- Stat Cards Row -->
    <div class="row">
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
          <div class="card-body">
            <div class="icon-circle bg-primary"><i class="fas fa-building"></i></div>
            <div>
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Companies</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $active_companies ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
          <div class="card-body">
            <div class="icon-circle bg-success"><i class="fas fa-users-cog"></i></div>
            <div>
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Employees</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_employees ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
          <div class="card-body">
            <div class="icon-circle bg-info"><i class="fas fa-plane-departure"></i></div>
            <div>
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending Leaves</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pending_leaves ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
          <div class="card-body">
            <div class="icon-circle bg-warning"><i class="fas fa-life-ring"></i></div>
            <div>
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Open Support Tickets</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $open_tickets ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
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
                      <div class="vr mx-2"></div><span class="badge bg-success">Active</span>
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
              <a href="companies.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Company</a>
              <a href="user_management.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Create Admin
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
              class="" style="aspect-ratio: 1/1; height: 250px; width: 250px;"></canvas>
          </div>
        </div>
      </div>
      <div class="col-xl-8 mb-4">
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
    fetch('api_dashboard.php?action=get_storage_usage').then(res => res.json()).then(result => {
      if (result.success) {
        const ctx = document.getElementById('storageChart').getContext('2d');
        console.log(result.data);
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
          options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
        });
      }
    });
    initializeTodoList('#todoForm', '#todoList');
  });
</script>