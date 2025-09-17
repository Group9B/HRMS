<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAuth();

$user = getCurrentUser($mysqli);
$roleName = $user['role_name'] ?? '';
if ($roleName !== 'Manager') {
  header('Location: /hrms/pages/unauthorized.php');
  exit;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payslips</title>
    <link rel="stylesheet" href="/hrms/assets/css/bootstrap.css">
    <link rel="stylesheet" href="/hrms/assets/css/datatable.css">
    <link rel="stylesheet" href="/hrms/assets/css/datatable_bootstrap_responsive.css">
  </head>
  <body>
    <?php include_once '../components/layout/header.php'; ?>
    <?php include_once '../components/layout/sidebar.php'; ?>
    <main class="container-fluid" style="margin-left: 260px;">
      <div class="row mt-4">
        <div class="col-12">
          <h3 class="mb-3">Team Payslips</h3>
          <div class="card">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped" id="payslipsTable">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Employee</th>
                      <th>Code</th>
                      <th>Period</th>
                      <th>Gross</th>
                      <th>Net</th>
                      <th>Status</th>
                      <th>Generated At</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <script src="/hrms/assets/js/jquery.js"></script>
    <script src="/hrms/assets/js/bootstrap.js"></script>
    <script src="/hrms/assets/js/datatable.js"></script>
    <script src="/hrms/assets/js/main.js"></script>
    <script src="/hrms/assets/js/datatable_bootstrap.js"></script>
    <script>
      $(function() {
        const $tbody = $('#payslipsTable tbody');
        $.getJSON('/hrms/api/api_payroll.php?action=get_company_payslips', function(res) {
          if (!res || !res.success) {
            $tbody.html('<tr><td colspan="8" class="text-danger">Failed to load payslips</td></tr>');
            return;
          }
          if (!res.data || res.data.length === 0) {
            $tbody.html('<tr><td colspan="8" class="text-muted">No payslips found</td></tr>');
            return;
          }
          const rows = res.data.map(function(p) {
            const empName = (p.first_name || '') + ' ' + (p.last_name || '');
            return '<tr>'+
              '<td>' + p.id + '</td>'+
              '<td>' + empName + '</td>'+
              '<td>' + (p.employee_code || '') + '</td>'+
              '<td>' + (p.period || '') + '</td>'+
              '<td>' + (parseFloat(p.gross_salary).toFixed(2)) + '</td>'+
              '<td>' + (parseFloat(p.net_salary).toFixed(2)) + '</td>'+
              '<td>' + (p.status || '') + '</td>'+
              '<td>' + (p.generated_at || '') + '</td>'+
            '</tr>';
          }).join('');
          $tbody.html(rows);
          $('#payslipsTable').DataTable();
        });
      });
    </script>
  </body>
  </html>


