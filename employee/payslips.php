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

// Get employee details for context if needed (optional since API handles it)
// We keep basic permission check or context if needed, but data fetching is moved to client.

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
                        <tbody id="payslipsTableBody">
                            <!-- Skeletons / Data will be injected here -->
                        </tbody>
                    </table>
                </div>
                <div id="noDataMessage" class="text-center py-5 d-none">
                    <i class="ti ti-receipt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No payslips found</h5>
                    <p class="text-muted">Your salary details will appear here once processed.</p>
                </div>
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
        loadPayslips();
    });

    function loadPayslips() {
        const tableId = 'payslipsTable';
        const tbody = document.getElementById('payslipsTableBody');

        // Show Skeleton
        SkeletonFactory.showTable(tableId, 5, 5);

        fetch('/hrms/api/api_payroll.php?action=get_payslips')
            .then(res => res.json())
            .then(result => {
                // Hide Skeleton (clears tbody)
                SkeletonFactory.hideTable(tableId);

                if (result.success && result.data && result.data.length > 0) {
                    let html = '';
                    result.data.forEach(payslip => {
                        const statusColor = payslip.status === 'paid' ? 'success' : (payslip.status === 'processed' ? 'info' : 'warning');
                        // Format currency
                        const gross = new Intl.NumberFormat('en-IN', { style: 'currency', currency: payslip.currency || 'INR' }).format(payslip.gross_salary);
                        const net = new Intl.NumberFormat('en-IN', { style: 'currency', currency: payslip.currency || 'INR' }).format(payslip.net_salary);

                        html += `
                            <tr>
                                <td>${payslip.period}</td>
                                <td>${gross}</td>
                                <td><strong>${net}</strong></td>
                                <td><span class="badge text-bg-${statusColor}">${capitalize(payslip.status)}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewPayslip(${payslip.id})">
                                        <i class="ti ti-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;

                    // Initialize DataTable
                    $('#payslipsTable').DataTable({
                        responsive: true,
                        order: [[0, 'desc']],
                        retrieve: true
                    });
                } else {
                    document.getElementById('payslipsTable').closest('.table-responsive').classList.add('d-none');
                    document.getElementById('noDataMessage').classList.remove('d-none');
                }
            })
            .catch(err => {
                console.error(err);
                SkeletonFactory.hideTable(tableId);
                showToast('Failed to load payslips', 'error');
            });
    }

    function viewPayslip(payslipId) {
        // Show loading state in modal if needed, or simple toast
        $('#payslipModal').modal('show');
        document.getElementById('payslipContent').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading payslip...</p>
            </div>
        `;

        fetch(`/hrms/api/api_payroll.php?action=get_payslip&id=${payslipId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const ps = data.data;
                    document.getElementById('payslipContent').innerHTML = ps.html;
                } else {
                    document.getElementById('payslipContent').innerHTML = `<div class="alert alert-danger">Failed to load payslip.</div>`;
                    showToast('Failed to load payslip', 'error');
                }
            })
            .catch(() => {
                document.getElementById('payslipContent').innerHTML = `<div class="alert alert-danger">Error loading payslip.</div>`;
                showToast('Failed to load payslip', 'error');
            });
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