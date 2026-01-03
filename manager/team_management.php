<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Team Management";

if (!isLoggedIn() || $_SESSION['role_id'] !== 6) {
    redirect("/hrms/auth/login.php");
}

$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];

// Get manager's employee record
$manager_result = query($mysqli, "SELECT * FROM employees WHERE user_id = ?", [$user_id]);
$manager = $manager_result['success'] ? $manager_result['data'][0] : null;

if (!$manager) {
    redirect("/hrms/pages/unauthorized.php");
}

$manager_id = $manager['id'];
$manager_department_id = $manager['department_id'];

// Get team members (employees assigned by this manager in team_members)
$team_members_result = query($mysqli, "
    SELECT e.*, u.email, u.status as user_status, d.name as department_name, des.name as designation_name, s.name as shift_name
    FROM employees e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN designations des ON e.designation_id = des.id
    LEFT JOIN shifts s ON e.shift_id = s.id
    JOIN team_members tm ON e.id = tm.employee_id
    WHERE tm.assigned_by = ? AND e.status = 'active'
    ORDER BY e.first_name ASC
", [$user_id]);

$team_members = $team_members_result['success'] ? $team_members_result['data'] : [];

// Get department info
$department_result = query($mysqli, "SELECT * FROM departments WHERE id = ?", [$manager_department_id]);
$department = $department_result['success'] ? $department_result['data'][0] : null;

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 text-gray-800">
                <i class="ti ti-users me-2"></i>Team Management
                <small class="text-muted">- <?= htmlspecialchars($department['name'] ?? 'Department') ?></small>
            </h2>
            <div>
                <a href="teams.php" class="btn btn-info me-2">
                    <i class="ti ti-settings me-2"></i>Manage Teams
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="ti ti-plus me-2"></i>Assign Task
                </button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#teamReportModal">
                    <i class="ti ti-chart-bar me-2"></i>Team Report
                </button>
            </div>
        </div>

        <!-- Team Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-primary"><i class="ti ti-users"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Team Members
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($team_members) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-success"><i class="ti ti-user-check"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Members</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($team_members, function ($member) {
                                    return $member['user_status'] === 'active';
                                })) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-info"><i class="ti ti-checklist"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending Tasks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-tasks-count">--</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-warning"><i class="ti ti-clock"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">On Leave Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="on-leave-count">--</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Team Members</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($team_members)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="teamTable">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Employee Code</th>
                                    <th>Designation</th>
                                    <th>Shift</th>
                                    <th>Status</th>
                                    <th>Join Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($team_members as $member): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-3">
                                                    <?= strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">
                                                        <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                                    </div>
                                                    <small class="text-muted"><?= htmlspecialchars($member['email']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($member['employee_code'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($member['designation_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($member['shift_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <span
                                                class="badge bg-<?= $member['user_status'] === 'active' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($member['user_status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $member['date_of_joining'] ? date('M j, Y', strtotime($member['date_of_joining'])) : 'N/A' ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary"
                                                    onclick="viewEmployee(<?= $member['id'] ?>)" title="View Details">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success"
                                                    onclick="assignTask(<?= $member['id'] ?>)" title="Assign Task">
                                                    <i class="ti ti-checklist"></i>
                                                </button>
                                                <button class="btn btn-outline-info"
                                                    onclick="viewPerformance(<?= $member['id'] ?>)" title="View Performance">
                                                    <i class="ti ti-chart-line"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted p-5">
                        <i class="ti ti-users fa-3x mb-3" style="font-size: 2rem;"></i>
                        <h5>No Team Members Found</h5>
                        <p>There are no employees in your department yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="taskForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="task_employee" class="form-label">Assign To</label>
                        <select class="form-select" id="task_employee" name="employee_id" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($team_members as $member): ?>
                                <option value="<?= $member['id'] ?>">
                                    <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="task_title" class="form-label">Task Title</label>
                        <input type="text" class="form-control" id="task_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="task_description" class="form-label">Description</label>
                        <textarea class="form-control" id="task_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="task_due_date" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="task_due_date" name="due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Team Report Modal -->
<div class="modal fade" id="teamReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Team Performance Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="teamReportContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="exportTeamReport()">Export Report</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(45deg, #4e73df, #36b9cc);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }

    .stat-card {
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 14px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
    }

    .stat-card .card-body {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-top: 14px;
        padding-bottom: 14px;
    }

    .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 6px;
        color: white;
        font-size: 20px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .stat-card .text-xs {
        letter-spacing: .03em;
        opacity: .9;
    }

    .stat-card .h5 {
        margin: 0;
    }
</style>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#teamTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[0, 'asc']]
        });

        // Load team statistics
        loadTeamStats();

        // Handle task form submission
        $('#taskForm').on('submit', function (e) {
            e.preventDefault();
            assignTaskToEmployee();
        });
    });

    function loadTeamStats() {
        fetch('/hrms/api/api_manager.php?action=get_team_stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#pending-tasks-count').text(data.data.pending_tasks);
                    $('#on-leave-count').text(data.data.on_leave_today);
                }
            })
            .catch(error => {
                console.error('Error loading team stats:', error);
            });
    }

    function viewEmployee(employeeId) {
        // Open employee profile in same tab; managers are allowed via updated employee/profile.php
        window.location.href = `/hrms/employee/profile.php?employee_id=${employeeId}`;
    }

    function assignTask(employeeId) {
        $('#task_employee').val(employeeId);
        $('#addTaskModal').modal('show');
    }

    function assignTaskToEmployee() {
        const formData = new FormData(document.getElementById('taskForm'));
        formData.append('action', 'assign_task');

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    $('#addTaskModal').modal('hide');
                    document.getElementById('taskForm').reset();
                    loadTeamStats();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function viewPerformance(employeeId) {
        // Redirect to performance page
        window.location.href = `/hrms/manager/performance.php?employee_id=${employeeId}`;
    }

    $('#teamReportModal').on('show.bs.modal', function () {
        loadTeamReport();
    });

    function loadTeamReport() {
        fetch('/hrms/api/api_manager.php?action=get_team_report')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTeamReport(data.data);
                } else {
                    $('#teamReportContent').html('<div class="alert alert-danger">Failed to load team report.</div>');
                }
            })
            .catch(error => {
                $('#teamReportContent').html('<div class="alert alert-danger">Error loading team report.</div>');
            });
    }

    function displayTeamReport(data) {
        let html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Team Overview</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total Members:</span>
                        <strong>${data.total_members}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Active Members:</span>
                        <strong>${data.active_members}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Completed Tasks:</span>
                        <strong>${data.completed_tasks}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Pending Tasks:</span>
                        <strong>${data.pending_tasks}</strong>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Performance Summary</h6>
                <div class="progress mb-2">
                    <div class="progress-bar" style="width: ${data.task_completion_rate}%">${data.task_completion_rate}%</div>
                </div>
                <small class="text-muted">Task Completion Rate</small>
            </div>
        </div>
    `;

        $('#teamReportContent').html(html);
    }

    function exportTeamReport() {
        // Implement export functionality
        showToast('Export feature will be available soon.', 'info');
    }
</script>