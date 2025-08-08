<?php require_once '../components/layout/header.php'; ?>
<div class="d-flex">
  <?php require_once '../components/layout/sidebar.php'; ?>
  <div class="p-4" style="flex: 1;">
    <div class="row">
      <div class="col-md-6 col-lg-3 mb-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Total Employees</h5>
            <p class="card-text display-6 text-primary">150</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 mb-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Present Today</h5>
            <p class="card-text display-6 text-success">142</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 mb-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">On Leave</h5>
            <p class="card-text display-6 text-warning">8</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 mb-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Departments</h5>
            <p class="card-text display-6 text-info">12</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Quick Actions</h5>
          </div>
          <div class="card-body">
            <button class="btn btn-primary me-2">Add Employee</button>
            <button class="btn btn-success me-2">Generate Report</button>
            <button class="btn btn-warning me-2">View Attendance</button>
            <button class="btn btn-info">Payroll Overview</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>