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

// Data Fetching
$active_companies = query($mysqli, "SELECT COUNT(*) as count FROM companies")['data'][0]['count'] ?? 0;
$total_employees = query($mysqli, "SELECT COUNT(*) as count FROM employees")['data'][0]['count'] ?? 0;
$pending_leaves = query($mysqli, "SELECT COUNT(*) as count FROM leaves WHERE status = 'pending'")['data'][0]['count'] ?? 0;
$open_tickets = query($mysqli, "SELECT COUNT(*) as count FROM support_tickets WHERE status = 'open'")['data'][0]['count'] ?? 0;

$recent_companies_result = query($mysqli, "SELECT c.id, c.name, c.created_at, (SELECT COUNT(*) FROM users u WHERE u.company_id = c.id) as user_count FROM companies c ORDER BY c.created_at DESC LIMIT 4");
$recent_companies = $recent_companies_result['success'] ? $recent_companies_result['data'] : [];

require_once '../components/layout/header.php';
$additionalScripts[] = '/hrms/assets/js/chart.js';
?>

<?php require_once '../components/layout/sidebar.php'; ?>

<div class="page-wrapper">

  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <div class="page-pretitle">Overview</div>
          <h2 class="page-title">Dashboard</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">

      <div class="row row-deck row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Active Companies</div>
              </div>
              <div class="h1 mb-3"><?= $active_companies ?></div>
              <div class="d-flex mb-2">
                <div class="text-primary me-2"><i class="fas fa-building"></i></div>
                <div class="text-secondary">Total registered</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Total Employees</div>
              </div>
              <div class="h1 mb-3"><?= $total_employees ?></div>
              <div class="d-flex mb-2">
                <div class="text-success me-2"><i class="fas fa-users-cog"></i></div>
                <div class="text-secondary">Across all companies</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Pending Leaves</div>
              </div>
              <div class="h1 mb-3"><?= $pending_leaves ?></div>
              <div class="d-flex mb-2">
                <div class="text-info me-2"><i class="fas fa-plane-departure"></i></div>
                <div class="text-secondary">Action required</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="subheader">Support Tickets</div>
              </div>
              <div class="h1 mb-3"><?= $open_tickets ?></div>
              <div class="d-flex mb-2">
                <div class="text-warning me-2"><i class="fas fa-life-ring"></i></div>
                <div class="text-secondary">Open items</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row row-cards">

        <div class="col-lg-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Recent Companies</h3>
            </div>
            <div class="list-group list-group-flush list-group-hoverable">
              <?php if (!empty($recent_companies)):
                foreach ($recent_companies as $company): ?>
                  <div class="list-group-item">
                    <div class="row align-items-center">
                      <div class="col-auto"><span class="badge bg-green"></span></div>
                      <div class="col text-truncate">
                        <a href="#" class="text-reset d-block"><?= htmlspecialchars($company['name']); ?></a>
                        <div class="d-block text-secondary text-truncate mt-n1"><?= $company['user_count']; ?> users</div>
                      </div>
                      <div class="col-auto">
                        <div class="text-secondary"><?= date('F j, Y', strtotime($company['created_at'])); ?></div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; else: ?>
                <div class="p-3 text-center text-secondary">No recent companies to display.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
              <div class="d-flex flex-column gap-2">
                <a href="companies.php" class="btn btn-primary w-100">
                  <i class="fas fa-plus me-2"></i> Add New Company
                </a>
                <a href="user_management.php" class="btn btn-success w-100">
                  <i class="fas fa-user-plus me-2"></i> Create Admin User
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Uploads Storage (5GB)</h3>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-center">
                <canvas id="storageChart" style="max-height: 250px;"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">My To-Do List</h3>
            </div>
            <div class="card-body">
              <form id="todoForm" class="d-flex mb-3 gap-2">
                <input type="text" name="task" class="form-control" placeholder="Add a new task..." required>
                <button type="submit" class="btn btn-primary">Add</button>
              </form>
              <ul class="list-group list-group-flush" id="todoList"></ul>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div> <?php require_once '../components/layout/footer.php'; ?>

<script>
  $(function () {
    fetch('api_dashboard.php?action=get_storage_usage').then(res => res.json()).then(result => {
      if (result.success) {
        const ctx = document.getElementById('storageChart').getContext('2d');
        new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: ['Used Space (GB)', 'Free Space (GB)'],
            datasets: [{
              data: [result.data.used_gb, result.data.free_gb],
              backgroundColor: ['#206bc4', '#f1f3f5'], // Tabler colors
              borderWidth: 0
            }]
          },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
      }
    });
    initializeTodoList('#todoForm', '#todoList');
  });
</script>