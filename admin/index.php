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

// --- DATA FETCHING ---

// Stat Card: Total Companies
$total_companies_result = $mysqli->query("SELECT COUNT(*) as count FROM companies");
$total_companies = $total_companies_result->fetch_assoc()['count'];

// Stat Card: Total Users (from users table)
$total_users_result = $mysqli->query("SELECT COUNT(*) as count FROM users");
$total_users = $total_users_result->fetch_assoc()['count'];

// Stat Card: Total Departments
$total_departments_result = $mysqli->query("SELECT COUNT(*) as count FROM departments");
$total_departments = $total_departments_result->fetch_assoc()['count'];

// Stat Card: Employees on Leave Today
$today = date('Y-m-d');
$on_leave_result = $mysqli->query("SELECT COUNT(*) as count FROM attendance WHERE date = '$today' AND status = 'leave'");
$on_leave_today = $on_leave_result->fetch_assoc()['count'];


// Recent Companies Table
$recent_companies_query = "
    SELECT 
        c.id, 
        c.name, 
        c.created_at,
        (SELECT COUNT(*) FROM users u WHERE u.company_id = c.id) as user_count
    FROM companies c 
    ORDER BY c.created_at DESC 
    LIMIT 5
";
$recent_companies_result = $mysqli->query($recent_companies_query);


require_once '../components/layout/header.php';
?>

<div class="d-flex">
  <?php require_once '../components/layout/sidebar.php'; ?>
  <div class="p-3 p-md-4" style="flex: 1;">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

    <!-- Stat Cards Row -->
    <div class="row">
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
          <div class="card-body">
            <div class="icon-circle bg-primary"><i class="fas fa-building"></i></div>
            <div>
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Companies</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_companies ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
          <div class="card-body">
            <div class="icon-circle bg-success"><i class="fas fa-users"></i></div>
            <div>
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Users</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_users ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
          <div class="card-body">
            <div class="icon-circle bg-info"><i class="fas fa-sitemap"></i></div>
            <div>
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Departments</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_departments ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
          <div class="card-body">
            <div class="icon-circle bg-warning"><i class="fas fa-user-clock"></i></div>
            <div>
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">On Leave Today</div>
              <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $on_leave_today ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">

      <!-- Recent Companies Column -->
      <div class="col-lg-8 mb-4">
        <div class="card main-content-card shadow-sm">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Recent Companies</h6>
          </div>
          <div class="card-body">
            <div class="recent-companies-list">
              <?php if ($recent_companies_result && $recent_companies_result->num_rows > 0): ?>
                <?php while ($company = $recent_companies_result->fetch_assoc()): ?>
                  <div class="list-item">
                    <div>
                      <div class="company-name"><?= htmlspecialchars($company['name']); ?></div>
                      <div class="user-count text-muted"><?= $company['user_count']; ?> users</div>
                    </div>
                    <div class="d-flex justify-space-between align-items-center">
                      <div class="created-at text-muted"><?= date('F j, Y', strtotime($company['created_at'])); ?>
                      </div>
                      <div class="vr mx-2 font-weight-bold"></div>
                      <span class="badge bg-success">Active</span>
                    </div>
                  </div>
                <?php endwhile; ?>
              <?php else: ?>
                <div class="text-center text-muted p-4">No recent companies to display.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions Column -->
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
              <a href="#" class="btn btn-info"><i class="fas fa-file-alt"></i> Generate Report</a>
              <a href="#" class="btn btn-warning text-dark"><i class="fas fa-database"></i> Schedule Backup</a>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>