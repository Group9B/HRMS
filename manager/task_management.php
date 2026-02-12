<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Task Management";

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

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$employee_filter = $_GET['employee_id'] ?? '';

// Build query conditions
// Build query conditions
$where_conditions = ["(e.department_id = ? OR t.assigned_by = ?)"];
$params = [$manager_department_id, $user_id];

if ($status_filter !== 'all') {
    $where_conditions[] = "t.status = ?";
    $params[] = $status_filter;
}

if (!empty($employee_filter)) {
    $where_conditions[] = "t.employee_id = ?";
    $params[] = $employee_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get tasks
// Tasks are now fetched via AJAX
$tasks = [];

// Get manager's teams for filter
$manager_teams_result = query($mysqli, "SELECT id, name FROM teams WHERE created_by = ? ORDER BY name ASC", [$user_id]);
$manager_teams = $manager_teams_result['success'] ? $manager_teams_result['data'] : [];


// Get team members for task assignment
$team_members_result = query($mysqli, "
    SELECT e.id, e.first_name, e.last_name, e.employee_code, 
           des.name as designation_name,
           GROUP_CONCAT(tm.team_id) as team_ids
    FROM employees e
    JOIN team_members tm ON e.id = tm.employee_id
    LEFT JOIN designations des ON e.designation_id = des.id
    WHERE tm.assigned_by = ? AND e.status = 'active'
    GROUP BY e.id
    ORDER BY e.first_name ASC
", [$user_id]);

$team_members = $team_members_result['success'] ? $team_members_result['data'] : [];

// Get task statistics
$stats_result = query($mysqli, "
    SELECT 
        COUNT(CASE WHEN t.status = 'pending' THEN 1 END) as pending,
        COUNT(CASE WHEN t.status = 'in_progress' THEN 1 END) as in_progress,
        COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed,
        COUNT(CASE WHEN t.status = 'cancelled' THEN 1 END) as cancelled
    FROM tasks t
    JOIN employees e ON t.employee_id = e.id
    JOIN employees e ON t.employee_id = e.id
    WHERE (e.department_id = ? OR t.assigned_by = ?)
", [$manager_department_id, $user_id]);

$stats = $stats_result['success'] ? $stats_result['data'][0] : [
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'cancelled' => 0
];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4 overflow-x-hidden" style="flex: 1;">
        <!-- Statistics Cards -->
        <div id="taskStats" class="row mb-2"></div>

        <div class="card shadow-sm">
            <div class="card-header">
                <div
                    class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-checklist fs-4"></i>
                        <h6 class="m-0 font-weight-bold">Team Tasks</h6>
                    </div>
                    <div class="wrapper d-flex gap-3"><button class="btn btn-sm btn-secondary ms-2" type="button"
                            data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false"
                            aria-controls="filterCollapse">
                            <i class="ti ti-filter me-1"></i>Filters
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-plus me-1"></i>Assign Tasks
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#addTaskModal">
                                        <i class="ti ti-user me-2"></i>Assign to Individual
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#bulkTaskModal">
                                        <i class="ti ti-users me-2"></i>Bulk Assign to Multiple
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Collapsible Filters -->
                <div class="collapse mt-3" id="filterCollapse">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending
                                </option>
                                <option value="in_progress" <?= $status_filter === 'in_progress' ? 'selected' : '' ?>>In
                                    Progress</option>
                                <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed
                                </option>
                                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="employee_id" class="form-label">Employee</label>
                            <select class="form-select form-select-sm" id="employee_id" name="employee_id">
                                <option value="">All Employees</option>
                                <?php foreach ($team_members as $member): ?>
                                    <option value="<?= $member['id'] ?>" <?= $employee_filter == $member['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                    <i class="ti ti-search me-1"></i>Apply
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="window.location.href='task_management.php'">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tasksTable">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Assigned To</th>
                                <th>Team</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="taskForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="task_employee" class="form-label">Assign To *</label>
                        <select class="form-select" id="task_employee" name="employee_id" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($team_members as $member): ?>
                                <option value="<?= $member['id'] ?>">
                                    <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="team_select_container" style="display: none;">
                        <label for="task_team" class="form-label">Select Team</label>
                        <select class="form-select" id="task_team" name="team_id">
                            <option value="">Select Team (Optional)</option>
                        </select>
                        <small class="text-muted">Associate this task with a specific team.</small>
                    </div>
                    <div class="mb-3">
                        <label for="task_title" class="form-label">Task Title *</label>
                        <input type="text" class="form-control" id="task_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="task_description" class="form-label">Description</label>
                        <textarea class="form-control" id="task_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="task_due_date" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="task_due_date" name="due_date"
                            min="<?= date('Y-m-d') ?>">
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

<!-- Bulk Task Modal -->
<div class="modal fade" id="bulkTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Assign Tasks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkTaskForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Assign To *</label>
                        <div class="mb-3">
                            <label class="form-label">Filter by Team</label>
                            <select class="form-select form-select-sm" id="bulk_team_filter">
                                <option value="">All Teams (Show All Employees)</option>
                                <?php foreach ($manager_teams as $team): ?>
                                    <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row" id="bulk_employee_list">
                            <?php foreach ($team_members as $member): ?>
                                <div class="col-md-6 employee-item" data-team-ids="<?= $member['team_ids'] ?? '' ?>">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="employee_ids[]"
                                            value="<?= $member['id'] ?>" id="emp_<?= $member['id'] ?>">
                                        <label class="form-check-label" for="emp_<?= $member['id'] ?>">
                                            <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-body bg-dark border-top border-bottom" id="bulk_team_validation"
                    style="display:none;">
                    <!-- Content injected by JS -->
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bulk_task_title" class="form-label">Task Title *</label>
                        <input type="text" class="form-control" id="bulk_task_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_task_description" class="form-label">Description</label>
                        <textarea class="form-control" id="bulk_task_description" name="description"
                            rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_task_due_date" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="bulk_task_due_date" name="due_date"
                            min="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Tasks</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTaskForm">
                <div class="modal-body">
                    <input type="hidden" name="task_id" id="edit_task_id">
                    <div class="mb-3">
                        <label for="edit_task_title" class="form-label">Task Title *</label>
                        <input type="text" class="form-control" id="edit_task_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_task_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_task_description" name="description"
                            rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_task_due_date" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="edit_task_due_date" name="due_date"
                            min="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="taskDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>
<script>
    // Initialize DataTable variable
    let tasksTable;

    // Render Stats using global function
    const taskStats = [
        { label: 'Pending', value: '<?= $stats['pending'] ?>', color: 'warning', icon: 'clock' },
        { label: 'In Progress', value: '<?= $stats['in_progress'] ?>', color: 'info', icon: 'play' },
        { label: 'Completed', value: '<?= $stats['completed'] ?>', color: 'success', icon: 'check' },
        { label: 'Cancelled', value: '<?= $stats['cancelled'] ?>', color: 'danger', icon: 'x' }
    ];
    renderStatCards('taskStats', taskStats);

    $(document).ready(function () {
        // Initialize DataTable
        tasksTable = $('#tasksTable').DataTable({
            pageLength: 10,
            order: [[5, 'desc']], // Sort by created date
            columnDefs: [
                { orderable: false, targets: 6 } // Disable sorting on Actions column
            ]
        });

        // Load tasks initially
        fetchTasks();

        // Handle Filter Form Submission
        $('.card-header form').on('submit', function (e) {
            e.preventDefault();
            fetchTasks();
        });

        // Handle employee selection to fetch teams
        $('#task_employee').on('change', function () {
            const employeeId = $(this).val();
            const teamContainer = $('#team_select_container');
            const teamSelect = $('#task_team');

            teamContainer.hide();
            teamSelect.empty().append('<option value="">Select Team (Optional)</option>');

            if (employeeId) {
                fetch(`/hrms/api/api_manager.php?action=get_employee_teams&employee_id=${employeeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            data.data.forEach(team => {
                                teamSelect.append(`<option value="${team.id}">${team.name}</option>`);
                            });

                            // Auto-select if only one team
                            if (data.data.length === 1) {
                                teamSelect.val(data.data[0].id);
                            }

                            // If user is forced to select a team if exists, we might want to make it required or strictly visible
                            teamContainer.show();
                        }
                    })
                    .catch(error => console.error('Error fetching teams:', error));
            }
        });

        // Handle form submission
        $('#taskForm').on('submit', function (e) {
            e.preventDefault();
            assignTask();
        });

        // Bulk Assign Team Validation
        $('input[name="employee_ids[]"]').on('change', function () {
            validateBulkTeams();
        });

        function validateBulkTeams() {
            const selectedEmployees = $('input[name="employee_ids[]"]:checked').map(function () {
                return $(this).val();
            }).get();

            const validationContainer = $('#bulk_team_validation');
            const submitBtn = $('#bulkTaskForm button[type="submit"]');

            if (selectedEmployees.length === 0) {
                validationContainer.hide().empty();
                submitBtn.prop('disabled', false); // Allow submission check to fail naturally or valid empty state
                return;
            }

            $.post('/hrms/api/api_manager.php', {
                action: 'get_common_teams',
                employee_ids: selectedEmployees
            }, function (response) {
                const data = typeof response === 'string' ? JSON.parse(response) : response;

                validationContainer.show().empty();

                if (data.success && data.data.length > 0) {
                    if (data.data.length === 1) {
                        // Single common team - Auto select
                        const team = data.data[0];
                        validationContainer.html(`
                            <div class="alert alert-success m-0 py-2">
                                <i class="ti ti-check me-2"></i>
                                Assigning to Team: <strong>${team.name}</strong>
                                <input type="hidden" name="team_id" value="${team.id}">
                            </div>
                        `);
                        submitBtn.prop('disabled', false);
                    } else {
                        // Multiple common teams - User selection
                        let options = data.data.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
                        validationContainer.html(`
                            <div class="alert alert-info m-0 py-2">
                                <i class="ti ti-info-circle me-2"></i>
                                Employees share multiple teams. Please select one:
                                <select class="form-select form-select-sm mt-2" name="team_id" required>
                                    <option value="">Select Team...</option>
                                    ${options}
                                </select>
                            </div>
                        `);
                        submitBtn.prop('disabled', false);
                    }
                } else {
                    // No common teams
                    validationContainer.html(`
                        <div class="alert alert-danger m-0 py-2">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <strong>Error:</strong> Selected employees do not belong to the same team.
                            <div class="small mt-1">Bulk assignment requires all employees to share at least one common team.</div>
                        </div>
                    `);
                    submitBtn.prop('disabled', true);
                }
            });
        }

        // Handle Team Filter Change (Client-side filtering)
        $('#bulk_team_filter').on('change', function () {
            const selectedTeamId = $(this).val();

            // Filter employees
            $('.employee-item').each(function () {
                const memberTeams = $(this).data('team-ids').toString().split(',');
                const checkbox = $(this).find('input[type="checkbox"]');

                if (selectedTeamId === "" || memberTeams.includes(selectedTeamId)) {
                    $(this).show();
                } else {
                    $(this).hide();
                    checkbox.prop('checked', false); // Uncheck hidden employees
                }
            });

            // Re-validate to update the validation UI
            validateBulkTeams();
        });

        // Handle bulk task form submission
        $('#bulkTaskForm').on('submit', function (e) {
            e.preventDefault();
            assignBulkTasks();
        });
    });

    function fetchTasks() {
        const status = $('#status').val();
        const employeeId = $('#employee_id').val();

        // Show loading state if needed (optional)

        fetch(`/hrms/api/api_manager.php?action=get_tasks&status=${status}&employee_id=${employeeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderTasks(data.data);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error fetching tasks:', error);
                showToast('Failed to load tasks.', 'error');
            });
    }

    function renderTasks(tasks) {
        // Clear existing data in DataTable
        tasksTable.clear();

        if (tasks.length === 0) {
            tasksTable.draw();
            return;
        }

        tasks.forEach(task => {
            const hasTeam = task.assigned_team_name && task.assigned_team_name.trim() !== '';

            // Task Info Column
            const taskInfo = `
                <div>
                    <div class="fw-bold">${escapeHtml(task.title)}</div>
                    <small class="text-muted">${escapeHtml((task.description || 'No description').substring(0, 100))}${task.description && task.description.length > 100 ? '...' : ''}</small>
                </div>
            `;

            // Assigned To Column with avatar function
            const avatarData = generateAvatarData({ id: task.employee_id, username: task.employee_code });
            const assignedTo = `
                <div class="d-flex align-items-center">
                    <div class="avatar me-2" style="background-color: ${avatarData.color};">
                        ${avatarData.initials}
                    </div>
                    <div>
                        <div class="fw-bold">
                            ${escapeHtml(task.first_name + ' ' + task.last_name)}
                        </div>
                        <small class="text-muted">${escapeHtml(task.employee_code || 'N/A')}</small>
                    </div>
                </div>
            `;

            // Team Column
            const teamInfo = hasTeam ? `
                <a href="manage_team_members.php?id=${task.assigned_team_id}"
                    class="badge bg-primary-subtle text-primary-emphasis text-decoration-none py-2">
                    <i class="ti ti-users me-1"></i>${escapeHtml(task.assigned_team_name)}
                </a>
            ` : '<span class="text-muted small">Not in a Team</span>';

            // Status Column with subtle badges
            const statusLabels = {
                'pending': 'warning',
                'in_progress': 'info',
                'completed': 'success',
                'cancelled': 'danger'
            };
            const statusClass = statusLabels[task.status] || 'secondary';
            const statusLabel = task.status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
            const status = `<span class="badge bg-${statusClass}-subtle text-${statusClass}-emphasis">${statusLabel}</span>`;

            // Due Date Column
            let dueDate = '<span class="text-muted">No due date</span>';
            if (task.due_date) {
                const dateObj = new Date(task.due_date);
                const today = new Date();
                today.setHours(0, 0, 0, 0); // normalize today

                // Simple parsing if format is YYYY-MM-DD
                const isOverdue = new Date(task.due_date) < new Date() && task.status !== 'completed';

                dueDate = `
                    <span class="${isOverdue ? 'text-danger fw-bold' : ''}">
                        ${dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                    </span>
                    ${isOverdue ? '<br><small class="text-danger">Overdue</small>' : ''}
                `;
            }

            // Created Date Column - using humanizeDate function
            const formattedDate = humanizeDate(task.created_at);

            // Actions Column using createActionDropdown
            const actionConfig = {
                onEdit: () => editTask(task.id),
                onDelete: () => deleteTask(task.id)
            };

            // Add cancel option for non-completed/cancelled tasks
            if (task.status !== 'completed' && task.status !== 'cancelled') {
                actionConfig.onClose = () => cancelTask(task.id);
            }

            const actions = createActionDropdown(actionConfig, {
                editTooltip: 'Edit Task',
                deleteTooltip: 'Delete Task',
                closeTooltip: 'Cancel Task'
            });

            // Add row to DataTable
            tasksTable.row.add([
                taskInfo,
                assignedTo,
                teamInfo,
                status,
                dueDate,
                formattedDate,
                actions
            ]);
        });

        // Redraw table
        tasksTable.draw();
    }

    // Helper to escape HTML to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function assignTask() {
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
                    // Reload tasks via AJAX instead of page reload
                    fetchTasks();
                    // Also update stats if needed (would require another API call or incrementing JS stats)
                    // For now, reloading tasks list is the priority. 
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function assignBulkTasks() {
        const formData = new FormData(document.getElementById('bulkTaskForm'));
        formData.append('action', 'bulk_assign_tasks');

        // Check if at least one employee is selected
        const selectedEmployees = $('input[name="employee_ids[]"]:checked');
        if (selectedEmployees.length === 0) {
            showToast('Please select at least one employee.', 'error');
            return;
        }

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    $('#bulkTaskModal').modal('hide');
                    document.getElementById('bulkTaskForm').reset();
                    fetchTasks();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function viewTask(taskId) {
        fetch(`/hrms/api/api_manager.php?action=get_task_details&task_id=${taskId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTaskDetails(data.data);
                    $('#taskDetailsModal').modal('show');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function displayTaskDetails(task) {
        const statusClass = getStatusClass(task.status);
        const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Task Information</h6>
                <p><strong>Title:</strong> ${escapeHtml(task.title)}</p>
                <p><strong>Description:</strong> ${escapeHtml(task.description || 'No description provided')}</p>
                <p><strong>Status:</strong> <span class="badge bg-${statusClass}-subtle text-${statusClass}-emphasis">${task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ')}</span></p>
            </div>
            <div class="col-md-6">
                <h6>Assignment Details</h6>
                <p><strong>Assigned To:</strong> ${escapeHtml(task.first_name + ' ' + task.last_name)}</p>
                <p><strong>Employee Code:</strong> ${escapeHtml(task.employee_code || 'N/A')}</p>
                <p><strong>Due Date:</strong> ${task.due_date ? new Date(task.due_date).toLocaleDateString() : 'No due date'}</p>
                <p><strong>Created:</strong> ${humanizeDate(task.created_at)} <small class="text-muted">(${new Date(task.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })})</small></p>
            </div>
        </div>
    `;

        $('#taskDetailsContent').html(html);
    }

    function editTask(taskId) {
        fetch(`/hrms/api/api_manager.php?action=get_task_details&task_id=${taskId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const task = data.data;
                    $('#edit_task_id').val(task.id);
                    $('#edit_task_title').val(task.title);
                    $('#edit_task_description').val(task.description);
                    $('#edit_task_due_date').val(task.due_date);

                    $('#editTaskModal').modal('show');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    $('#editTaskForm').on('submit', function (e) {
        e.preventDefault();
        updateTask();
    });

    function updateTask() {
        const formData = new FormData(document.getElementById('editTaskForm'));
        formData.append('action', 'update_task');

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    $('#editTaskModal').modal('hide');
                    fetchTasks();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function cancelTask(taskId) {
        showConfirmationModal(
            'Are you sure you want to cancel this task?',
            function () {
                const formData = new FormData();
                formData.append('action', 'cancel_task');
                formData.append('task_id', taskId);

                fetch('/hrms/api/api_manager.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            fetchTasks();
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showToast('An error occurred. Please try again.', 'error');
                    });
            },
            'Cancel Task',
            'Cancel',
            'btn-warning'
        );
    }

    function deleteTask(taskId) {
        showConfirmationModal(
            'Are you sure you want to <strong>delete</strong> this task? This action cannot be undone.',
            function () {
                const formData = new FormData();
                formData.append('action', 'delete_task');
                formData.append('task_id', taskId);

                fetch('/hrms/api/api_manager.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            fetchTasks();
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showToast('An error occurred. Please try again.', 'error');
                    });
            },
            'Delete Task',
            'Delete',
            'btn-danger'
        );
    }

    function getStatusClass(status) {
        const classes = {
            'pending': 'warning',
            'in_progress': 'info',
            'completed': 'success',
            'cancelled': 'danger'
        };
        return classes[status] || 'secondary';
    }
    // Remove standalone renderStatCards call if it was called here? No, I added the const declaration above. 
</script>