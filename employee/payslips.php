<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "My Payslips";

if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}
if ($_SESSION['role_id'] !== 4) {
    redirect("/hrms/pages/unauthorized.php");
}

$user_id = $_SESSION['user_id'];

// Get employee details
$employee_result = query($mysqli, "SELECT id, first_name, last_name FROM employees WHERE user_id = ?", [$user_id]);
if (!$employee_result['success'] || empty($employee_result['data'])) {
    redirect('/hrms/pages/unauthorized.php');
}
$employee = $employee_result['data'][0];
$employee_id = $employee['id'];

// Get payslips
$payslips_result = query($mysqli, "SELECT * FROM payroll WHERE employee_id = ? ORDER BY year DESC, month DESC", [$employee_id]);
$payslips = $payslips_result['success'] ? $payslips_result['data'] : [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="h3 mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>My Payslips</h2>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Salary Details</h6>
            </div>
            <div class="card-body">
                <?php if (empty($payslips)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No payslips found</h5>
                        <p class="text-muted">Your salary details will appear here once processed.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="payslipsTable">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Basic Salary</th>
                                    <th>HRA</th>
                                    <th>Allowances</th>
                                    <th>Deductions</th>
                                    <th>Net Salary</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payslips as $payslip): 
                                    $period = date('F Y', strtotime($payslip['year'] . '-' . $payslip['month'] . '-01'));
                                ?>
                                    <tr>
                                        <td><?= $period ?></td>
                                        <td>₹<?= number_format($payslip['basic'], 2) ?></td>
                                        <td>₹<?= number_format($payslip['hra'] ?? 0, 2) ?></td>
                                        <td>₹<?= number_format($payslip['allowances'] ?? 0, 2) ?></td>
                                        <td>₹<?= number_format($payslip['deductions'] ?? 0, 2) ?></td>
                                        <td><strong>₹<?= number_format($payslip['net_salary'], 2) ?></strong></td>
                                        <td>
                                            <span class="badge text-bg-<?= $payslip['status'] === 'paid' ? 'success' : ($payslip['status'] === 'processed' ? 'info' : 'warning') ?>">
                                                <?= ucfirst($payslip['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewPayslip(<?= $payslip['id'] ?>)" 
                                                    <?= $payslip['status'] === 'pending' ? 'disabled' : '' ?>>
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Payslip Modal -->
<div class="modal fade" id="payslipModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payslip Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="payslipContent">
                <!-- Payslip content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printPayslip()">
                    <i class="fas fa-print me-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
$(function() {
    $('#payslipsTable').DataTable({
        responsive: true,
        order: [[0, 'desc']]
    });
});

function viewPayslip(payslipId) {
    fetch(`/hrms/api/api_payroll.php?action=get_payslip&id=${payslipId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const payslip = data.data;
                const period = new Date(payslip.year + '-' + payslip.month + '-01').toLocaleDateString('en-US', { 
                    month: 'long', 
                    year: 'numeric' 
                });
                
                const content = `
                    <div class="payslip">
                        <div class="text-center mb-4">
                            <h4>Payslip</h4>
                            <h6>${period}</h6>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Employee Details</h6>
                                <p><strong>Name:</strong> ${payslip.employee_name}</p>
                                <p><strong>Employee Code:</strong> ${payslip.employee_code}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <h6>Payment Details</h6>
                                <p><strong>Status:</strong> ${payslip.status}</p>
                                <p><strong>Processed:</strong> ${new Date(payslip.processed_at).toLocaleDateString()}</p>
                            </div>
                        </div>
                        
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Earnings</th>
                                    <th class="text-end">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Basic Salary</td>
                                    <td class="text-end">${parseFloat(payslip.basic).toFixed(2)}</td>
                                </tr>
                                ${payslip.hra ? `<tr><td>House Rent Allowance</td><td class="text-end">${parseFloat(payslip.hra).toFixed(2)}</td></tr>` : ''}
                                ${payslip.allowances ? `<tr><td>Allowances</td><td class="text-end">${parseFloat(payslip.allowances).toFixed(2)}</td></tr>` : ''}
                                <tr class="table-light">
                                    <td><strong>Total Earnings</strong></td>
                                    <td class="text-end"><strong>${(parseFloat(payslip.basic) + parseFloat(payslip.hra || 0) + parseFloat(payslip.allowances || 0)).toFixed(2)}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Deductions</th>
                                    <th class="text-end">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${payslip.deductions ? `<tr><td>Total Deductions</td><td class="text-end">${parseFloat(payslip.deductions).toFixed(2)}</td></tr>` : '<tr><td colspan="2">No deductions</td></tr>'}
                                <tr class="table-light">
                                    <td><strong>Net Salary</strong></td>
                                    <td class="text-end"><strong>${parseFloat(payslip.net_salary).toFixed(2)}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `;
                
                document.getElementById('payslipContent').innerHTML = content;
                $('#payslipModal').modal('show');
            } else {
                showToast('Failed to load payslip', 'error');
            }
        })
        .catch(() => showToast('Failed to load payslip', 'error'));
}

function printPayslip() {
    const content = document.getElementById('payslipContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Payslip</title>
                <link href="/hrms/assets/css/bootstrap.css" rel="stylesheet">
                <style>
                    body { margin: 20px; }
                    .payslip { max-width: 800px; margin: 0 auto; }
                    @media print { body { margin: 0; } }
                </style>
            </head>
            <body>
                ${content}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
</script>