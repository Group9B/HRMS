<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

requireAuth();

// Allow HR Manager, Company Admin, Super Admin
$user = getCurrentUser($mysqli);
$role_id = $_SESSION['role_id'];

if (!in_array($role_id, [1, 2, 3], true)) {
  header('Location: /hrms/pages/unauthorized.php');
  exit;
}
require_once '../components/layout/header.php';
?>
<div class="d-flex">
  <?php require_once '../components/layout/sidebar.php'; ?>
  <div class="p-3 p-md-4" style="flex: 1;">
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header">Generate Payslip</div>
          <div class="card-body">
            <form id="generateForm" class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Employee</label>
                <select id="employeeSelect" class="form-select" required></select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Period (YYYY-MM)</label>
                <input type="month" id="periodInput" class="form-control" required>
              </div>
              <div class="col-md-2">
                <label class="form-label">Currency</label>
                <input type="text" id="currencyInput" class="form-control" value="INR">
              </div>
              <div class="col-md-2">
                <label class="form-label">Gross</label>
                <input type="number" step="0.01" id="grossInput" class="form-control" required>
              </div>
              <div class="col-md-2">
                <label class="form-label">Base Deductions</label>
                <input type="number" step="0.01" id="deductionsInput" class="form-control" value="0">
              </div>
              <div class="col-12">
                <label class="form-label">Optional Components (select to include)</label>
                <div class="row g-2">
                  <div class="col-md-2">
                    <div class="form-check">
                      <input class="form-check-input opt-comp" type="checkbox" id="optPf" data-type="deduction"
                        data-label="PF" data-target="#optPfAmt">
                      <label class="form-check-label" for="optPf">PF</label>
                    </div>
                    <input type="number" step="0.01" class="form-control form-control-sm mt-1" id="optPfAmt"
                      placeholder="0.00" disabled>
                  </div>
                  <div class="col-md-2">
                    <div class="form-check">
                      <input class="form-check-input opt-comp" type="checkbox" id="optEsi" data-type="deduction"
                        data-label="ESI" data-target="#optEsiAmt">
                      <label class="form-check-label" for="optEsi">ESI</label>
                    </div>
                    <input type="number" step="0.01" class="form-control form-control-sm mt-1" id="optEsiAmt"
                      placeholder="0.00" disabled>
                  </div>
                  <div class="col-md-2">
                    <div class="form-check">
                      <input class="form-check-input opt-comp" type="checkbox" id="optInsurance" data-type="deduction"
                        data-label="Insurance" data-target="#optInsuranceAmt">
                      <label class="form-check-label" for="optInsurance">Insurance</label>
                    </div>
                    <input type="number" step="0.01" class="form-control form-control-sm mt-1" id="optInsuranceAmt"
                      placeholder="0.00" disabled>
                  </div>
                  <div class="col-md-2">
                    <div class="form-check">
                      <input class="form-check-input opt-comp" type="checkbox" id="optGratuity" data-type="deduction"
                        data-label="Gratuity" data-target="#optGratuityAmt">
                      <label class="form-check-label" for="optGratuity">Gratuity</label>
                    </div>
                    <input type="number" step="0.01" class="form-control form-control-sm mt-1" id="optGratuityAmt"
                      placeholder="0.00" disabled>
                  </div>
                  <div class="col-md-2">
                    <div class="form-check">
                      <input class="form-check-input opt-comp" type="checkbox" id="optBonus" data-type="earning"
                        data-label="Bonus" data-target="#optBonusAmt">
                      <label class="form-check-label" for="optBonus">Bonus</label>
                    </div>
                    <input type="number" step="0.01" class="form-control form-control-sm mt-1" id="optBonusAmt"
                      placeholder="0.00" disabled>
                  </div>
                  <div class="col-md-2">
                    <div class="form-check">
                      <input class="form-check-input opt-comp" type="checkbox" id="optShares" data-type="earning"
                        data-label="Shares" data-target="#optSharesAmt">
                      <label class="form-check-label" for="optShares">Shares</label>
                    </div>
                    <input type="number" step="0.01" class="form-control form-control-sm mt-1" id="optSharesAmt"
                      placeholder="0.00" disabled>
                  </div>
                </div>
                <small class="text-muted">Only one standard template is used; selected components will be included with
                  their amounts.</small>
              </div>
              <div class="col-12 d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary">Generate Payslip</button>
                <div id="generateStatus" class="text-muted"></div>
              </div>
            </form>
          </div>
        </div>
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
                    <th>Actions</th>
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
    <?php require_once '../components/layout/footer.php'; ?>

    <script>
      $(function () {
        const $tbody = $('#payslipsTable tbody');
        const $employeeSelect = $('#employeeSelect');
        const $templateSelect = $('#templateSelect');
        const $generateForm = $('#generateForm');
        const $generateStatus = $('#generateStatus');
        // removed template creation controls

        function loadPayslips() {
          $.getJSON('/hrms/api/api_payroll.php?action=get_company_payslips', function (res) {
            if (!res || !res.success) {
              $tbody.html('<tr><td colspan="9" class="text-danger">Failed to load payslips</td></tr>');
              return;
            }
            if (!res.data || res.data.length === 0) {
              $tbody.html('<tr><td colspan="9" class="text-muted">No payslips found</td></tr>');
              return;
            }
            const rows = res.data.map(function (p) {
              const empName = (p.first_name || '') + ' ' + (p.last_name || '');
              const sendBtn = '<button class="btn btn-sm btn-outline-primary send-btn" data-id="' + p.id + '">Send</button>';
              return '<tr>' +
                '<td>' + p.id + '</td>' +
                '<td>' + empName + '</td>' +
                '<td>' + (p.employee_code || '') + '</td>' +
                '<td>' + (p.period || '') + '</td>' +
                '<td>' + (parseFloat(p.gross_salary).toFixed(2)) + '</td>' +
                '<td>' + (parseFloat(p.net_salary).toFixed(2)) + '</td>' +
                '<td>' + (p.status || '') + '</td>' +
                '<td>' + (p.generated_at || '') + '</td>' +
                '<td>' + sendBtn + '</td>' +
                '</tr>';
            }).join('');
            $tbody.html(rows);
            if ($.fn.DataTable.isDataTable('#payslipsTable')) {
              $('#payslipsTable').DataTable().destroy();
            }
            $('#payslipsTable').DataTable();
          });
        }

        function loadEmployees() {
          $.getJSON('/hrms/api/api_employees.php?action=get_employees', function (res) {
            if (res && res.success && res.data) {
              const opts = res.data.map(function (e) {
                const name = (e.first_name || '') + ' ' + (e.last_name || '');
                return '<option value="' + e.id + '">' + name + ' (' + (e.employee_code || '') + ')</option>';
              }).join('');
              $employeeSelect.html('<option value="">Select employee</option>' + opts);
            } else {
              $employeeSelect.html('<option value="">Failed to load</option>');
            }
          });
        }

        function loadTemplates() {
          $.getJSON('/hrms/api/api_payroll.php?action=list_templates', function (res) {
            if (res && res.success && res.data) {
              const opts = res.data.map(function (t) {
                return '<option value="' + t.id + '">' + t.name + '</option>';
              }).join('');
              $templateSelect.html('<option value="">Default</option>' + opts);
            } else {
              $templateSelect.html('<option value="">Default</option>');
            }
          });
        }

        function collectSelectedComponents() {
          const earnings = [];
          const deductions = [];
          $('.opt-comp').each(function () {
            const $cb = $(this);
            const target = $($cb.data('target'));
            const label = $cb.data('label');
            const type = $cb.data('type');
            if ($cb.is(':checked')) {
              const amt = parseFloat(target.val() || '0') || 0;
              if (amt > 0) {
                if (type === 'earning') earnings.push({ name: label, amount: amt });
                if (type === 'deduction') deductions.push({ name: label, amount: amt });
              }
            }
          });
          return { earnings, deductions };
        }

        $('.opt-comp').on('change input', function () {
          const targetSel = $(this).data('target');
          $(targetSel).prop('disabled', !$(this).is(':checked'));
        });

        $generateForm.on('submit', function (e) {
          e.preventDefault();
          const employee_id = parseInt($employeeSelect.val() || '0', 10);
          const period = $('#periodInput').val();
          const currency = $('#currencyInput').val() || 'INR';
          const gross = parseFloat($('#grossInput').val() || '0');
          const deductions = parseFloat($('#deductionsInput').val() || '0');
          const template_id = parseInt($templateSelect.val() || '0', 10) || '';
          if (!employee_id || !period) { return; }
          $generateStatus.text('Generating...');
          const form = new FormData();
          form.append('action', 'generate_payslip');
          form.append('employee_id', employee_id);
          form.append('period', period);
          form.append('currency', currency);
          if (template_id) form.append('template_id', template_id);
          const selected = collectSelectedComponents();
          const earnings = [{ name: 'Gross', amount: gross }, ...selected.earnings];
          const deductionsArr = (deductions ? [{ name: 'Base Deductions', amount: deductions }] : []).concat(selected.deductions);
          form.append('earnings', JSON.stringify(earnings));
          form.append('deductions', JSON.stringify(deductionsArr));
          fetch('/hrms/api/api_payroll.php', { method: 'POST', body: form })
            .then(r => r.json()).then(function (res) {
              if (res && res.success) {
                $generateStatus.text('Generated payslip #' + res.data.payslip_id);
                // Optimistically update table without full reload if data returned
                if (res.data && res.data.payslip) {
                  const p = res.data.payslip;
                  const empName = (p.first_name || '') + ' ' + (p.last_name || '');
                  const row = '<tr>' +
                    '<td>' + p.id + '</td>' +
                    '<td>' + empName + '</td>' +
                    '<td>' + (p.employee_code || '') + '</td>' +
                    '<td>' + (p.period || '') + '</td>' +
                    '<td>' + (parseFloat(p.gross_salary).toFixed(2)) + '</td>' +
                    '<td>' + (parseFloat(p.net_salary).toFixed(2)) + '</td>' +
                    '<td>' + (p.status || '') + '</td>' +
                    '<td>' + (p.generated_at || '') + '</td>' +
                    '<td><button class="btn btn-sm btn-outline-primary send-btn" data-id="' + p.id + '">Send</button></td>' +
                    '</tr>';
                  if ($.fn.DataTable.isDataTable('#payslipsTable')) {
                    const dt = $('#payslipsTable').DataTable();
                    dt.row.add($(row)).draw(false);
                  } else {
                    $tbody.prepend(row);
                  }
                } else {
                  loadPayslips();
                }
              } else {
                $generateStatus.text('Failed to generate');
              }
            }).catch(function () { $generateStatus.text('Error generating'); });
        });

        $tbody.on('click', '.send-btn', function () {
          const id = parseInt($(this).data('id'), 10);
          if (!id) return;
          if (!confirm('Send payslip #' + id + ' to employee (and optionally manager)?')) return;
          const form = new FormData();
          form.append('action', 'send_payslip');
          form.append('payslip_id', id);
          form.append('to_employee', '1');
          form.append('to_manager', '0');
          fetch('/hrms/api/api_payroll.php', { method: 'POST', body: form })
            .then(r => r.json()).then(function (res) {
              if (res && res.success) {
                alert('Payslip sent');
                // Update status cell in place if row returned
                if (res.data && res.data.id) {
                  const id = res.data.id;
                  $('#payslipsTable tbody tr').each(function () {
                    const $tr = $(this);
                    const rowId = parseInt($tr.find('td').eq(0).text(), 10);
                    if (rowId === id) {
                      $tr.find('td').eq(6).text(res.data.status || 'sent');
                      $tr.find('td').eq(7).text(res.data.generated_at || '');
                    }
                  });
                } else {
                  loadPayslips();
                }
              } else {
                alert('Failed to send');
              }
            }).catch(function () { alert('Error sending'); });
        });

        // removed "Send to Mail" ad-hoc button

        loadPayslips();
        loadEmployees();
        loadTemplates();

        // template creation removed
      });
    </script>