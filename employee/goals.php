<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Tasks & Goals";

if (!isLoggedIn() || $_SESSION['role_id'] !== 4) {
    redirect("/hrms/pages/unauthorized.php");
}
require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="h3 mb-0"><i class="ti ti-checklist me-2"></i>Tasks & Goals</h2>
        </div>

        <!-- Task Statistics -->
        <div class="row mb-4" id="stats-container">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-primary border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0" id="stat-total">--</h4>
                                <p class="text-muted mb-0">Total Tasks</p>
                            </div>
                            <div class="flex-shrink-0"><i class="ti ti-checklist fa-2x text-primary"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-warning border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0" id="stat-pending">--</h4>
                                <p class="text-muted mb-0">Pending</p>
                            </div>
                            <div class="flex-shrink-0"><i class="ti ti-clock fa-2x text-warning"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-info border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0" id="stat-in-progress">--</h4>
                                <p class="text-muted mb-0">In Progress</p>
                            </div>
                            <div class="flex-shrink-0"><i class="ti ti-loader fa-2x text-info"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-success border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0" id="stat-completed">--</h4>
                                <p class="text-muted mb-0">Completed</p>
                            </div>
                            <div class="flex-shrink-0"><i class="ti ti-circle-check fa-2x text-success"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Assigned Tasks</h6>
                        <button class="btn btn-sm btn-secondary py-1 px-2" onclick="loadAssignedTasks()"><i
                                class="ti ti-refresh"></i></button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tasksTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Task Details</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Assigned By</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal To-Do List -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Personal To-Do List</h6>
                    </div>
                    <div class="card-body">
                        <form id="todoForm" class="d-flex mb-3">
                            <input type="text" name="task" class="form-control me-2" placeholder="Add a new task..."
                                required>
                            <button type="submit" class="btn btn-primary">Add</button>
                        </form>
                        <ul class="list-group" id="todoList">
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    let assignedTasksTable;
    $(function () {
        assignedTasksTable = $('#tasksTable').DataTable({
            responsive: true,
            searching: false,
            paging: false,
            info: false,
            data: [],
            order: [[1, 'asc']],
            columns: [
                { data: 'title', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong><br><small class="text-muted">${escapeHTML(r.description || '')}</small>` },
                { data: 'due_date', render: (d, t, r) => d ? `<span class="badge text-bg-${new Date(d) < new Date() && r.status !== 'completed' ? 'danger' : 'secondary'}">${new Date(d).toLocaleDateString()}</span>` : `<span class="text-muted">N/A</span>` },
                {
                    data: 'status', render: (d, t, r) => {
                        if (d === 'completed') {
                            return `<span class="badge bg-success"><i class="ti ti-check me-1"></i>Completed</span>`;
                        }
                        return `
                        <select class="form-select form-select-sm" onchange="updateTaskStatus(${r.id}, this.value)" aria-label="Update task status">
                            <option value="pending" ${d === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="in_progress" ${d === 'in_progress' ? 'selected' : ''}>In Progress</option>
                            <option value="completed" ${d === 'completed' ? 'selected' : ''}>Completed</option>
                        </select>`;
                    }
                },
                { data: 'assigned_by_name', defaultContent: 'System' }
            ],
            language: {
                emptyTable: `<div class="text-center py-5">
                    <i class="ti ti-checklist fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Tasks Assigned</h5>
                        <p class="text-muted">New Tasks will appear here when assigned.</p>
                    </div>`
            }
        });

        loadAssignedTasks();
        // Correctly initialize the modular to-do list with form and list selectors
        initializeTodoList('#todoForm', '#todoList');
    });

    function loadAssignedTasks() {
        assignedTasksTable.clear().draw();
        $('#tasksTable tbody').html('<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

        // Fetch data from the dedicated tasks API
        fetch('/hrms/api/api_tasks.php?action=get_assigned_tasks')
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    assignedTasksTable.rows.add(result.data).draw();
                    updateStats(result.data);
                } else {
                    showToast(result.message, 'error');
                    $('#tasksTable tbody').html('<tr><td colspan="4" class="text-center text-danger">Could not load tasks.</td></tr>');
                }
            });
    }

    function updateStats(tasks = []) {
        $('#stat-total').text(tasks.length);
        $('#stat-pending').text(tasks.filter(t => t.status === 'pending').length);
        $('#stat-in-progress').text(tasks.filter(t => t.status === 'in_progress').length);
        $('#stat-completed').text(tasks.filter(t => t.status === 'completed').length);
    }

    function updateTaskStatus(taskId, newStatus) {
        const formData = new FormData();
        formData.append('action', 'update_task_status');
        formData.append('task_id', taskId);
        formData.append('status', newStatus);

        // Send update to the dedicated tasks API
        fetch('/hrms/api/api_tasks.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    showToast(result.message, 'success');
                    loadAssignedTasks();
                } else {
                    showToast(result.message, 'error');
                    loadAssignedTasks();
                }
            });
    }
</script>