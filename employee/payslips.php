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
$payslips_result = query($mysqli, "SELECT p.id, p.period, p.status, p.gross_salary, p.net_salary FROM payslips p WHERE p.employee_id = ? ORDER BY p.period DESC", [$employee_id]);
$payslips = $payslips_result['success'] ? $payslips_result['data'] : [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="h3 mb-0"><i class="ti ti-receipt me-2"></i>My Payslips</h2>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Salary Details</h6>
            </div>
            <div class="card-body">
                <?php if (empty($payslips)): ?>
                    <div class="text-center py-5">
                        <i class="ti ti-receipt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No payslips found</h5>
                        <p class="text-muted">Your salary details will appear here once processed.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="payslipsTable">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Gross</th>
                                    <th>Net</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payslips as $payslip): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($payslip['period']) ?></td>
                                        <td>₹<?= number_format($payslip['gross_salary'], 2) ?></td>
                                        <td><strong>₹<?= number_format($payslip['net_salary'], 2) ?></strong></td>
                                        <td>
                                            <span
                                                class="badge text-bg-<?= $payslip['status'] === 'paid' ? 'success' : ($payslip['status'] === 'processed' ? 'info' : 'warning') ?>">
                                                <?= ucfirst($payslip['status']) ?>
                                            </span>
                                        </td>
                                        <td><button class="btn btn-sm btn-outline-primary"
                                                onclick="viewPayslip(<?= (int) $payslip['id'] ?>)"><i class="ti ti-eye"></i>
                                                View</button></td>
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
                    <i class="ti ti-printer me-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
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
                    const ps = data.data;
                    document.getElementById('payslipContent').innerHTML = ps.html;
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