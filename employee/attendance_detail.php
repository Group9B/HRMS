<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: /hrms/auth/login.php');
    exit();
}

$roleId = $_SESSION['role_id'] ?? 0;
$userId = $_SESSION['user_id'] ?? 0;
$employeeResult = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$userId]);
$currentEmployeeId = ($employeeResult['success'] && !empty($employeeResult['data'])) ? (int) $employeeResult['data'][0]['id'] : 0;

// Role-based allowance to view others
// 2: Company Admin/Owner, 3: HR, 6: Manager can view others; 4: Employee only self
$canViewOthers = in_array($roleId, [2, 3, 6]);

// Selected employee from query string (honored only if allowed and provided)
$selectedEmployeeId = $currentEmployeeId;
if ($canViewOthers && isset($_GET['employee_id']) && (int) $_GET['employee_id'] > 0) {
    $selectedEmployeeId = (int) $_GET['employee_id'];
}

$title = 'Attendance Detail';
include __DIR__ . '/../components/layout/header.php';
$additionalScripts = ['attendance-calendar.js', 'attendance-detail.js'];
?>
<div class="d-flex">
    <?php include __DIR__ . '/../components/layout/sidebar.php'; ?>
    <div class="flex-grow-1 p-3 p-md-4">

        <div class="card shadow-sm mb-3">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-outline-secondary" id="prevMonthBtn"><i
                            class="ti ti-chevron-left"></i></button>
                    <h5 class="mb-0" id="monthLabel"></h5>
                    <button class="btn btn-outline-secondary" id="nextMonthBtn"><i
                            class="ti ti-chevron-right"></i></button>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <?php if ($canViewOthers): ?>
                        <select id="employeeSelect" class="form-select form-select-sm" style="min-width: 220px;"></select>
                    <?php else: ?>
                        <span class="badge bg-primary-subtle text-primary">My Attendance</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body">
                <div id="summaryChips" class="d-flex flex-wrap gap-2 mb-3"></div>
                <div class="row g-3">
                    <div class="col-lg-5">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-body-tertiary">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti ti-calendar-event"></i>
                                    <span>Calendar</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="attendance-calendar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-body-tertiary">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti ti-list-details"></i>
                                    <span>Daily Details</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive mobile-table-scroll">
                                    <table class="table table-hover table-sm table-bordered align-middle nowrap"
                                        id="attendanceTable" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Check-in</th>
                                                <th>Check-out</th>
                                                <th>Worked</th>
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
        </div>
    </div>
</div>
<?php include __DIR__ . '/../components/layout/footer.php'; ?>
<script>
    window.__ATTENDANCE_DETAIL__ = {
        roleId: <?php echo (int) $roleId; ?>,
        userId: <?php echo (int) $userId; ?>,
        currentEmployeeId: <?php echo (int) $currentEmployeeId; ?>,
        selectedEmployeeId: <?php echo (int) $selectedEmployeeId; ?>,
        canViewOthers: <?php echo $canViewOthers ? 'true' : 'false'; ?>
    };
</script>