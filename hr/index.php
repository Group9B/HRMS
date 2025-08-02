<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/header_style.css">
</head>
<body class="bg-body">
  <div class="d-flex">
    <!-- Sidebar -->
    <div class="d-flex flex-column flex-shrink-0 p-3 hrms-sidebar" style="width: 250px; min-height: 100vh;">
      <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
        <span class="fs-4">HRMS</span>
      </a>
      <hr>
      <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
          <a href="#" class="nav-link active" aria-current="page">
            <i class="bi bi-speedometer2 me-2"></i>
            Dashboard
          </a>
        </li>
        <li>
          <a href="#" class="nav-link">
            <i class="bi bi-people me-2"></i>
            Employees
          </a>
        </li>
        <li>
          <a href="#" class="nav-link">
            <i class="bi bi-calendar-check me-2"></i>
            Attendance
          </a>
        </li>
        <li>
          <a href="#" class="nav-link">
            <i class="bi bi-cash-stack me-2"></i>
            Payroll
          </a>
        </li>
        <li>
          <a href="#" class="nav-link">
            <i class="bi bi-gear me-2"></i>
            Settings
          </a>
        </li>
      </ul>
    </div>

    <!-- Content -->
    <div class="p-4" style="flex: 1;">
      <!-- Header with Theme Toggle -->
      <div class="hrms-header p-3 mb-4 d-flex justify-content-between align-items-center">
        <div>
          <h2 class="mb-0 text-primary">Welcome to HRMS Dashboard</h2>
          <p class="text-secondary mb-0">Manage your human resources efficiently</p>
        </div>
        <div class="theme-toggle-wrapper">
          <div id="toggleThemeBtn" class="theme-toggle">
            <div class="toggle-circle"></div>
          </div>
        </div>
      </div>
      
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
  <script>
    // Theme toggle logic
    document.getElementById('toggleThemeBtn').addEventListener('click', () => {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // Animate the transition
        html.style.transition = 'background-color 0.3s ease';
        html.setAttribute('data-bs-theme', newTheme);
        
        // Store theme preference
        localStorage.setItem('theme', newTheme);
    });

    // Load saved theme preference
    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        }
    });
  </script>
</body>
</html>