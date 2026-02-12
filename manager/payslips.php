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
include_once '../components/layout/header.php';
?>

<div class="d-flex">
  <?php require_once '../components/layout/sidebar.php'; ?>
  <div class="p-3 p-md-4" style="flex: 1;">
    <div class="col-12">
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
</div>
<?php require_once '../components/layout/footer.php'; ?>
<script>
  $(function () {
    const $tbody = $('#payslipsTable tbody');
    $.getJSON('/hrms/api/api_payroll.php?action=get_company_payslips', function (res) {
      if (!res || !res.success) {
        $tbody.html('<tr><td colspan="8" class="text-danger">Failed to load payslips</td></tr>');
        return;
      }
      if (!res.data || res.data.length === 0) {
        $tbody.html('<tr><td colspan="8" class="text-muted">No payslips found</td></tr>');
        return;
      }
      const rows = res.data.map(function (p) {
        const empName = (p.first_name || '') + ' ' + (p.last_name || '');
        return '<tr>' +
          '<td>' + p.id + '</td>' +
          '<td>' + empName + '</td>' +
          '<td>' + (p.employee_code || '') + '</td>' +
          '<td>' + (p.period || '') + '</td>' +
          '<td>' + (parseFloat(p.gross_salary).toFixed(2)) + '</td>' +
          '<td>' + (parseFloat(p.net_salary).toFixed(2)) + '</td>' +
          '<td>' + (p.status || '') + '</td>' +
          '<td>' + (p.generated_at || '') + '</td>' +
          '</tr>';
      }).join('');
      $tbody.html(rows);
      $('#payslipsTable').DataTable();
    });
  });
</script>
</body>

</html>