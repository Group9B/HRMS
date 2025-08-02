<?php include 'header.php'; ?>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 250px; min-height: 100vh;">
      <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
        <span class="fs-4 text-primary">HRMS</span>
      </a>
      <hr>
      <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
          <a href="#" class="nav-link active bg-primary text-white" aria-current="page">
            Dashboard
          </a>
        </li>
        <li>
          <a href="#" class="nav-link text-primary">
            Employees
          </a>
        </li>
        <li>
          <a href="#" class="nav-link text-primary">
            Attendance
          </a>
        </li>
        <li>
          <a href="#" class="nav-link text-primary">
            Payroll
          </a>
        </li>
        <li>
          <a href="#" class="nav-link text-primary">
            Settings
          </a>
        </li>
      </ul>
    </div>

    <!-- Main content -->
    <div class="p-4" style="flex: 1;">
      <h2 class="text-primary">Welcome to HRMS</h2>
      <p>This sidebar is built using Bootstrap only with your custom color variables.</p>
    </div>
  </div>
    <?php include 'footer.php'; ?>
