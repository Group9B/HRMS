<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "My Attendance";

if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}
if ($_SESSION['role_id'] !== 4) {
    redirect("/hrms/pages/unauthorized.php");
}

$user_id = $_SESSION['user_id'];
$empRes = query($mysqli, "SELECT id, first_name, last_name FROM employees WHERE user_id = ?", [$user_id]);
if (!$empRes['success'] || empty($empRes['data'])) {
    redirect('/hrms/pages/unauthorized.php');
}
$employee = $empRes['data'][0];
$employee_id = $employee['id'];

$month = $_GET['month'] ?? date('Y-m');
$start = $month . '-01';
$end = date('Y-m-t', strtotime($start));

$attRes = query($mysqli, "SELECT date, status FROM attendance WHERE employee_id = ? AND date BETWEEN ? AND ? ORDER BY date ASC", [$employee_id, $start, $end]);
$rows = $attRes['success'] ? $attRes['data'] : [];

require_once '../components/layout/header.php';
?>
<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="h3 mb-0"><i class="ti ti-calendar-check me-2"></i>My Attendance</h2>
            <form class="d-flex align-items-center gap-2" method="get">
                <div class="input-group">
                    <span class="input-group-text">Month</span>
                    <input type="month" class="form-control" name="month" value="<?= htmlspecialchars($month) ?>">
                </div>
                <button type="submit" class="btn btn-outline-primary">Go</button>
            </form>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive mobile-table-scroll">
                    <table class="table table-hover table-sm align-middle nowrap" id="attendanceTable"
                        style="width:100%">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($r['date'])) ?></td>
                                    <td>
                                        <span
                                            class="badge text-bg-<?= $r['status'] === 'present' ? 'success' : ($r['status'] === 'absent' ? 'danger' : ($r['status'] === 'leave' ? 'warning' : 'info')) ?>">
                                            <?= ucfirst($r['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>
<script>
    $(function () { $('#attendanceTable').DataTable({ autoWidth: false, responsive: true, order: [[0, 'asc']] }); });
</script>