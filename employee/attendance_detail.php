<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: /hrms/auth/login.php');
    exit();
}

// Map role IDs to role names for security (don't expose IDs to frontend)
$roleId = $_SESSION['role_id'] ?? 0;
$userId = $_SESSION['user_id'] ?? 0;
$companyId = $_SESSION['company_id'] ?? 0;

$roleMap = [
    1 => 'admin',
    2 => 'company_owner',
    3 => 'hr',
    4 => 'employee',
    5 => 'candidate',
    6 => 'manager'
];
$roleName = $roleMap[$roleId] ?? 'unknown';

// Get current user's employee record
$employeeResult = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$userId]);
$currentEmployeeId = ($employeeResult['success'] && !empty($employeeResult['data'])) ? (int) $employeeResult['data'][0]['id'] : 0;

// Role-based access control:
// - company_owner: can view all employees except themselves
// - hr: can view all employees including themselves
// - manager: can view their team members AND themselves
// - employee: can view only themselves
$canViewOthers = in_array($roleName, ['company_owner', 'hr', 'manager']);
$canViewSelf = in_array($roleName, ['hr', 'manager', 'employee']);
$showDropdown = ($roleName !== 'employee') && $canViewOthers;

// Validate access
if (!in_array($roleName, ['company_owner', 'hr', 'manager', 'employee'])) {
    header('Location: /hrms/pages/unauthorized.php');
    exit();
}

// Selected employee from query string (honored only if allowed)
$selectedEmployeeId = $currentEmployeeId;
if ($canViewOthers && isset($_GET['employee_id']) && (int) $_GET['employee_id'] > 0) {
    $selectedEmployeeId = (int) $_GET['employee_id'];
}

$title = 'Attendance Detail';
require_once __DIR__ . '/../components/layout/header.php';
$additionalScripts = ['attendance-calendar.js', 'attendance-detail.js'];
?>

<div class="d-flex">
    <?php require_once __DIR__ . '/../components/layout/sidebar.php'; ?>
    <div class="flex-grow-1 p-3 p-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-outline-secondary" id="prevMonthBtn" title="Previous Month">
                        <i class="ti ti-chevron-left"></i>
                    </button>
                    <h5 class="mb-0 mx-2" id="monthLabel"></h5>
                    <button class="btn btn-outline-secondary" id="nextMonthBtn" title="Next Month">
                        <i class="ti ti-chevron-right"></i>
                    </button>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <?php if ($showDropdown): ?>
                        <select id="employeeSelect" class="form-select form-select-sm" style="min-width: 220px;">
                            <option value="">Loading...</option>
                        </select>
                    <?php else: ?>
                        <span class="badge bg-primary-subtle text-primary px-3 py-2">My Attendance</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Summary Chips -->
        <div id="summaryChips" class="d-flex flex-wrap gap-2 mb-3"></div>

        <!-- Calendar and Table -->
        <div class="row g-3">
            <!-- Calendar Section -->
            <div class="col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-body-tertiary">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-calendar-event"></i>
                            <span class="fw-semibold">Calendar View</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="attendance-calendar"></div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="col-lg-8">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-body-tertiary">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-list-details"></i>
                            <span class="fw-semibold">Daily Details</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive mobile-table-scroll">
                            <table class="table table-hover table-sm table-bordered align-middle nowrap"
                                id="attendanceTable" style="width:100%">
                                <thead>
                                    <tr class="bg-primary bg-opacity-10">
                                        <th class="fw-semibold">Date</th>
                                        <th class="fw-semibold">Status</th>
                                        <th class="fw-semibold">Check-in</th>
                                        <th class="fw-semibold">Check-out</th>
                                        <th class="fw-semibold">Worked</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            Loading attendance data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    window.__ATTENDANCE_DETAIL__ = {
        roleName: <?php echo json_encode($roleName); ?>,
        userId: <?php echo (int) $userId; ?>,
        currentEmployeeId: <?php echo (int) $currentEmployeeId; ?>,
        selectedEmployeeId: <?php echo (int) $selectedEmployeeId; ?>,
        canViewOthers: <?php echo $canViewOthers ? 'true' : 'false'; ?>,
        canViewSelf: <?php echo $canViewSelf ? 'true' : 'false'; ?>,
        showDropdown: <?php echo $showDropdown ? 'true' : 'false'; ?>,
        detailUrl: null,
    };
</script>

<?php require_once __DIR__ . '/../components/layout/footer.php'; ?>