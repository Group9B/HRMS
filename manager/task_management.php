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
$where_conditions = ["e.department_id = ?"];
$params = [$manager_department_id];

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
$tasks_result = query($mysqli, "
    SELECT t.*, e.first_name, e.last_name, e.employee_code,
           des.name as designation_name, d.name as department_name
    FROM tasks t
    JOIN employees e ON t.employee_id = e.id
    LEFT JOIN designations des ON e.designation_id = des.id
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE $where_clause
    ORDER BY t.created_at DESC
", $params);

$tasks = $tasks_result['success'] ? $tasks_result['data'] : [];


// Get team members for task assignment
$team_members_result = query($mysqli, "
    SELECT DISTINCT e.id, e.first_name, e.last_name, e.employee_code, 
           des.name as designation_name
    FROM employees e
    JOIN team_members tm ON e.id = tm.employee_id
    LEFT JOIN designations des ON e.designation_id = des.id
    WHERE tm.assigned_by = ? AND e.status = 'active'
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
    WHERE e.department_id = ?
", [$manager_department_id]);

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
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 text-gray-800">
                <i class="ti ti-checklist me-2"></i>Task Management
            </h2>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="ti ti-plus me-2"></i>Assign New Task
                </button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkTaskModal">
                    <i class="ti ti-checklist me-2"></i>Bulk Assign
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-warning"><i class="ti ti-clock"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['pending'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-info"><i class="ti ti-play"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">In Progress</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['in_progress'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-success"><i class="ti ti-check"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['completed'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-danger"><i class="ti ti-x"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Cancelled</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['cancelled'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="in_progress" <?= $status_filter === 'in_progress' ? 'selected' : '' ?>>In
                                Progress</option>
                            <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed
                            </option>
                            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select class="form-select" id="employee_id" name="employee_id">
                            <option value="">All Employees</option>
                            <?php foreach ($team_members as $member): ?>
                                <option value="<?= $member['id'] ?>" <?= $employee_filter == $member['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tasks Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Team Tasks</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($tasks)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="tasksTable">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($task['title']) ?></div>
                                                <small
                                                    class="text-muted"><?= htmlspecialchars(substr($task['description'] ?: 'No description', 0, 100)) ?><?= strlen($task['description'] ?: '') > 100 ? '...' : '' ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2">
                                                    <?= strtoupper(substr($task['first_name'], 0, 1) . substr($task['last_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">
                                                        <?= htmlspecialchars($task['first_name'] . ' ' . $task['last_name']) ?>
                                                    </div>
                                                    <small
                                                        class="text-muted"><?= htmlspecialchars($task['employee_code'] ?? 'N/A') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $status_classes = [
                                                'pending' => 'warning',
                                                'in_progress' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $status_class = $status_classes[$task['status']] ?? 'secondary';
                                            ?>
                                            <span
                                                class="badge bg-<?= $status_class ?>"><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($task['due_date']): ?>
                                                <?php
                                                $due_date = new DateTime($task['due_date']);
                                                $today = new DateTime();
                                                $is_overdue = $due_date < $today && $task['status'] !== 'completed';
                                                ?>
                                                <span class="<?= $is_overdue ? 'text-danger fw-bold' : '' ?>">
                                                    <?= $due_date->format('M j, Y') ?>
                                                </span>
                                                <?php if ($is_overdue): ?>
                                                    <br><small class="text-danger">Overdue</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">No due date</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($task['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="viewTask(<?= $task['id'] ?>)"
                                                    title="View Details">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success" onclick="editTask(<?= $task['id'] ?>)"
                                                    title="Edit Task">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <?php if ($task['status'] !== 'completed' && $task['status'] !== 'cancelled'): ?>
                                                    <button class="btn btn-outline-danger" onclick="cancelTask(<?= $task['id'] ?>)"
                                                        title="Cancel Task">
                                                        <i class="ti ti-x"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted p-5">
                        <i class="ti ti-checklist" style="font-size: 3rem;"></i>
                        <div class="mb-3"></div>
                        <h5>No Tasks Found</h5>
                        <p>No tasks match your current filters.</p>
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
                        <div class="row">
                            <?php foreach ($team_members as $member): ?>
                                <div class="col-md-6">
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
                        <input type="date" class="form-control" id="bulk_task_due_date" name="due_date">
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
                        <input type="date" class="form-control" id="edit_task_due_date" name="due_date">
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
    $(document).ready(function () {
        // Initialize DataTable
        $('#tasksTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[5, 'desc']] // Sort by created date
        });

        // Handle task form submission
        $('#taskForm').on('submit', function (e) {
            e.preventDefault();
            assignTask();
        });

        // Handle bulk task form submission
        $('#bulkTaskForm').on('submit', function (e) {
            e.preventDefault();
            assignBulkTasks();
        });
    });

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
                    location.reload();
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
                    location.reload();
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
        const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Task Information</h6>
                <p><strong>Title:</strong> ${task.title}</p>
                <p><strong>Description:</strong> ${task.description || 'No description provided'}</p>
                <p><strong>Status:</strong> <span class="badge bg-${getStatusClass(task.status)}">${task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ')}</span></p>
            </div>
            <div class="col-md-6">
                <h6>Assignment Details</h6>
                <p><strong>Assigned To:</strong> ${task.first_name} ${task.last_name}</p>
                <p><strong>Employee Code:</strong> ${task.employee_code || 'N/A'}</p>
                <p><strong>Due Date:</strong> ${task.due_date ? new Date(task.due_date).toLocaleDateString() : 'No due date'}</p>
                <p><strong>Created:</strong> ${new Date(task.created_at).toLocaleString()}</p>
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
                    location.reload();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function cancelTask(taskId) {
        if (confirm('Are you sure you want to cancel this task?')) {
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
                        location.reload();
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                });
        }
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
</script>