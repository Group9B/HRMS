<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

requireAuth();

// Allow HR Manager, Company Admin, Super Admin
$user = getCurrentUser($mysqli);
$roleName = $user['role_name'] ?? '';
if (!in_array($roleName, ['HR Manager','Company Admin','Super Admin'], true)) {
  header('Location: /hrms/pages/unauthorized.php');
  exit;
}
require_once '../components/layout/header.php';
?>
<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
      <div class="row mt-4">
        <div class="col-12">
          <h3 class="mb-3">Payslips</h3>
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
                  <label class="form-label">Deductions</label>
                  <input type="number" step="0.01" id="deductionsInput" class="form-control" value="0">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Template</label>
                  <select id="templateSelect" class="form-select"></select>
                </div>
                <div class="col-12 d-flex align-items-center gap-3">
                  <button type="submit" class="btn btn-primary">Generate Payslip</button>
                  <div id="generateStatus" class="text-muted"></div>
                </div>
              </form>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header">Create Custom Payslip Template</div>
            <div class="card-body">
              <form id="templateForm" class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Template Name</label>
                  <input type="text" id="tplName" class="form-control" required>
                </div>
                <div class="col-md-8">
                  <label class="form-label">Email Subject</label>
                  <input type="text" id="tplSubject" class="form-control" placeholder="Your payslip for {{period}}" value="Your payslip for {{period}}">
                </div>
                <div class="col-12">
                  <label class="form-label">HTML Body (with placeholders)</label>
                  <textarea id="tplBody" class="form-control" rows="8" placeholder="Use placeholders like {{company_name}}, {{employee_name}}, {{earnings_rows}}, {{deductions_rows}}, {{gross_salary}}, {{net_salary}}"></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label">Optional Components</label>
                  <div class="row">
                    <div class="col-md-2"><div class="form-check"><input class="form-check-input tpl-comp" type="checkbox" value="insurance" id="compInsurance"><label class="form-check-label" for="compInsurance">Insurance</label></div></div>
                    <div class="col-md-2"><div class="form-check"><input class="form-check-input tpl-comp" type="checkbox" value="pf" id="compPf"><label class="form-check-label" for="compPf">PF</label></div></div>
                    <div class="col-md-2"><div class="form-check"><input class="form-check-input tpl-comp" type="checkbox" value="esi" id="compEsi"><label class="form-check-label" for="compEsi">ESI</label></div></div>
                    <div class="col-md-2"><div class="form-check"><input class="form-check-input tpl-comp" type="checkbox" value="gratuity" id="compGratuity"><label class="form-check-label" for="compGratuity">Gratuity</label></div></div>
                    <div class="col-md-2"><div class="form-check"><input class="form-check-input tpl-comp" type="checkbox" value="shares" id="compShares"><label class="form-check-label" for="compShares">Shares</label></div></div>
                    <div class="col-md-2"><div class="form-check"><input class="form-check-input tpl-comp" type="checkbox" value="bonus" id="compBonus"><label class="form-check-label" for="compBonus">Bonus</label></div></div>
                  </div>
                </div>
                <div class="col-12 d-flex align-items-center gap-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="tplActive" checked>
                    <label class="form-check-label" for="tplActive">Active</label>
                  </div>
                  <button type="submit" class="btn btn-success">Create Template</button>
                  <div id="tplStatus" class="text-muted"></div>
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
      $(function() {
        const $tbody = $('#payslipsTable tbody');
        const $employeeSelect = $('#employeeSelect');
        const $templateSelect = $('#templateSelect');
        const $generateForm = $('#generateForm');
        const $generateStatus = $('#generateStatus');
        const $templateForm = $('#templateForm');
        const $tplStatus = $('#tplStatus');

        function loadPayslips() {
          $.getJSON('/hrms/api/api_payroll.php?action=get_company_payslips', function(res) {
            if (!res || !res.success) {
              $tbody.html('<tr><td colspan="9" class="text-danger">Failed to load payslips</td></tr>');
              return;
            }
            if (!res.data || res.data.length === 0) {
              $tbody.html('<tr><td colspan="9" class="text-muted">No payslips found</td></tr>');
              return;
            }
            const rows = res.data.map(function(p) {
              const empName = (p.first_name || '') + ' ' + (p.last_name || '');
              const sendBtn = '<button class="btn btn-sm btn-outline-primary send-btn" data-id="'+p.id+'">Send</button>';
              return '<tr>'+
                '<td>' + p.id + '</td>'+
                '<td>' + empName + '</td>'+
                '<td>' + (p.employee_code || '') + '</td>'+
                '<td>' + (p.period || '') + '</td>'+
                '<td>' + (parseFloat(p.gross_salary).toFixed(2)) + '</td>'+
                '<td>' + (parseFloat(p.net_salary).toFixed(2)) + '</td>'+
                '<td>' + (p.status || '') + '</td>'+
                '<td>' + (p.generated_at || '') + '</td>'+
                '<td>' + sendBtn + '</td>'+
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
          $.getJSON('/hrms/api/api_employees.php?action=get_employees', function(res) {
            if (res && res.success && res.data) {
              const opts = res.data.map(function(e) {
                const name = (e.first_name||'') + ' ' + (e.last_name||'');
                return '<option value="'+e.id+'">'+name+' ('+(e.employee_code||'')+')</option>';
              }).join('');
              $employeeSelect.html('<option value="">Select employee</option>'+opts);
            } else {
              $employeeSelect.html('<option value="">Failed to load</option>');
            }
          });
        }

        function loadTemplates() {
          $.getJSON('/hrms/api/api_payroll.php?action=list_templates', function(res) {
            if (res && res.success && res.data) {
              const opts = res.data.map(function(t){
                return '<option value="'+t.id+'">'+t.name+'</option>';
              }).join('');
              $templateSelect.html('<option value="">Default</option>'+opts);
            } else {
              $templateSelect.html('<option value="">Default</option>');
            }
          });
        }

        $generateForm.on('submit', function(e){
          e.preventDefault();
          const employee_id = parseInt($employeeSelect.val()||'0',10);
          const period = $('#periodInput').val();
          const currency = $('#currencyInput').val()||'INR';
          const gross = parseFloat($('#grossInput').val()||'0');
          const deductions = parseFloat($('#deductionsInput').val()||'0');
          const template_id = parseInt($templateSelect.val()||'0',10) || '';
          if (!employee_id || !period) { return; }
          $generateStatus.text('Generating...');
          const form = new FormData();
          form.append('action','generate_payslip');
          form.append('employee_id', employee_id);
          form.append('period', period);
          form.append('currency', currency);
          if (template_id) form.append('template_id', template_id);
          const earnings = [{ name: 'Gross', amount: gross }];
          const deductionsArr = deductions ? [{ name: 'Deductions', amount: deductions }] : [];
          form.append('earnings', JSON.stringify(earnings));
          form.append('deductions', JSON.stringify(deductionsArr));
          fetch('/hrms/api/api_payroll.php', { method: 'POST', body: form })
            .then(r=>r.json()).then(function(res){
              if (res && res.success) {
                $generateStatus.text('Generated payslip #'+res.data.payslip_id);
                loadPayslips();
              } else {
                $generateStatus.text('Failed to generate');
              }
            }).catch(function(){ $generateStatus.text('Error generating'); });
        });

        $tbody.on('click', '.send-btn', function(){
          const id = parseInt($(this).data('id'),10);
          if (!id) return;
          if (!confirm('Send payslip #'+id+' to employee (and optionally manager)?')) return;
          const form = new FormData();
          form.append('action','send_payslip');
          form.append('payslip_id', id);
          form.append('to_employee', '1');
          form.append('to_manager', '0');
          fetch('/hrms/api/api_payroll.php', { method:'POST', body: form })
            .then(r=>r.json()).then(function(res){
              if (res && res.success) {
                alert('Payslip sent');
                loadPayslips();
              } else {
                alert('Failed to send');
              }
            }).catch(function(){ alert('Error sending'); });
        });

        loadPayslips();
        loadEmployees();
        loadTemplates();

        $templateForm.on('submit', function(e){
          e.preventDefault();
          const name = $('#tplName').val().trim();
          const subject = $('#tplSubject').val().trim() || 'Your payslip for {{period}}';
          const body = $('#tplBody').val().trim();
          const is_active = $('#tplActive').is(':checked') ? 1 : 0;
          const components = $('.tpl-comp:checked').map(function(){ return $(this).val(); }).get();
          if (!name || !body) { $tplStatus.text('Name and body required'); return; }
          const form = new FormData();
          form.append('action','create_template');
          form.append('name', name);
          form.append('subject', subject);
          form.append('body_html', body);
          form.append('components', JSON.stringify(components));
          form.append('is_active', is_active);
          $tplStatus.text('Creating...');
          fetch('/hrms/api/api_payroll.php', { method:'POST', body: form })
            .then(r=>r.json()).then(function(res){
              if (res && res.success) {
                $tplStatus.text('Template created');
                loadTemplates();
              } else {
                $tplStatus.text('Failed to create template');
              }
            }).catch(function(){ $tplStatus.text('Error creating template'); });
        });
      });
    </script>


