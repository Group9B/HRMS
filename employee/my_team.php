<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "My Team";

if (!isLoggedIn() || $_SESSION['role_id'] !== 4) {
    redirect("/hrms/auth/login.php");
}

$user_id = $_SESSION['user_id'];

// Get employee ID
$emp_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
$employee_id = $emp_result['success'] && !empty($emp_result['data']) ? $emp_result['data'][0]['id'] : 0;

// Verify user is a team leader
$leader_check = query($mysqli, "
    SELECT COUNT(*) as is_leader 
    FROM team_members 
    WHERE employee_id = ? 
    AND (role_in_team LIKE '%leader%' OR role_in_team LIKE '%lead%')
", [$employee_id]);

if (!$leader_check['success'] || $leader_check['data'][0]['is_leader'] == 0) {
    redirect("/hrms/employee/index.php");
}

// Get teams where user is a leader
$teams_result = query($mysqli, "
    SELECT DISTINCT t.id, t.name, t.description, t.created_at,
           COUNT(tm2.id) as member_count
    FROM teams t
    JOIN team_members tm ON t.id = tm.team_id
    LEFT JOIN team_members tm2 ON t.id = tm2.team_id
    WHERE tm.employee_id = ? 
    AND (tm.role_in_team LIKE '%leader%' OR tm.role_in_team LIKE '%lead%')
    GROUP BY t.id
    ORDER BY t.name ASC
", [$employee_id]);

$my_teams = $teams_result['success'] ? $teams_result['data'] : [];

// Get stats  
$stats_result = query($mysqli, "
    SELECT 
        COUNT(DISTINCT t.id) as team_count,
        COUNT(DISTINCT tm.employee_id) as member_count,
        COUNT(DISTINCT CASE WHEN tk.status = 'pending' THEN tk.id END) as pending_tasks,
        COUNT(DISTINCT CASE WHEN tk.status = 'completed' THEN tk.id END) as completed_tasks
    FROM team_members tml
    JOIN teams t ON tml.team_id = t.id
    LEFT JOIN team_members tm ON t.id = tm.team_id
    LEFT JOIN tasks tk ON tm.employee_id = tk.employee_id
    WHERE tml.employee_id = ?
    AND (tml.role_in_team LIKE '%leader%' OR tml.role_in_team LIKE '%lead%')
", [$employee_id]);

$stats = $stats_result['success'] && !empty($stats_result['data']) ? $stats_result['data'][0] : [
    'team_count' => 0,
    'member_count' => 0,
    'pending_tasks' => 0,
    'completed_tasks' => 0
];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="row mb-4" id="statsCards">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ti ti-users-group fs-1 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">My Teams</div>
                                <div class="fs-4 fw-bold">
                                    <?= $stats['team_count'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ti ti-users fs-1 text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Team Members</div>
                                <div class="fs-4 fw-bold">
                                    <?= $stats['member_count'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ti ti-clock fs-1 text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Pending Tasks</div>
                                <div class="fs-4 fw-bold">
                                    <?= $stats['pending_tasks'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ti ti-check fs-1 text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Completed</div>
                                <div class="fs-4 fw-bold">
                                    <?= $stats['completed_tasks'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Teams Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">My Teams</h6>
            </div>
            <div class="card-body" id="teamsContainer">
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members Section (Hidden by default) -->
        <div class="card shadow-sm d-none" id="teamMembersCard">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="m-0 font-weight-bold" id="selectedTeamName">Team Members</h6>
                    <small class="text-muted" id="selectedTeamDesc"></small>
                </div>
                <button class="btn btn-sm btn-secondary" onclick="showTeams()">
                    <i class="ti ti-arrow-left me-1"></i>Back to Teams
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="membersTable">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Role in Team</th>
                                <th>Designation</th>
                                <th>Task Progress</th>
                                <th>Performance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Task Modal -->
<div class="modal fade" id="assignTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignTaskForm">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Task to Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="task_employee_id" name="employee_id">
                    <div class="mb-3">
                        <label class="form-label">Assigning to:</label>
                        <div class="fw-bold" id="task_employee_name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Task Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date" min="<?= date('Y-m-d') ?>">
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

<!-- Add Performance Review Modal -->
<div class="modal fade" id="performanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="performanceForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Performance Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="perf_employee_id" name="employee_id">
                    <input type="hidden" name="id" value="0">
                    <div class="mb-3">
                        <label class="form-label">Reviewing:</label>
                        <div class="fw-bold" id="perf_employee_name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Period *</label>
                        <input type="month" class="form-control" name="period" value="<?= date('Y-m') ?>"
                            max="<?= date('Y-m') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Score (0-100) *</label>
                        <input type="number" class="form-control" name="score" min="0" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="4"
                            placeholder="Detailed performance feedback..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Review</button>
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
                <h5 class="modal-title">
                    <i class="ti ti-checklist me-2"></i>
                    <span id="taskModalMemberName"></span> - Tasks
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="taskDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    let assignTaskModal, performanceModal, taskDetailsModal;
    let currentTeamId = null;
    const employeeId = <?= $employee_id ?>;

    $(function () {
        assignTaskModal = new bootstrap.Modal('#assignTaskModal');
        performanceModal = new bootstrap.Modal('#performanceModal');
        taskDetailsModal = new bootstrap.Modal('#taskDetailsModal');

        // Load teams from PHP
        const teams = <?= json_encode($my_teams) ?>;
        displayTeams(teams);

        // Handle task assignment form
        $('#assignTaskForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'assign_task');

            fetch('/hrms/api/api_manager.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        assignTaskModal.hide();
                        $('#assignTaskForm')[0].reset();
                        loadTeamMembers(currentTeamId);
                    } else {
                        showToast(result.message, 'error');
                    }
                });
        });

        // Handle performance review form
        $('#performanceForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_edit_performance');

            fetch('/hrms/api/api_performance.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        performanceModal.hide();
                        $('#performanceForm')[0].reset();
                        loadTeamMembers(currentTeamId);
                    } else {
                        showToast(result.message, 'error');
                    }
                });
        });
    });

    function loadStats() {
        // Load quick stats
        fetch(`/hrms/api/api_manager.php?action=get_team_report`)
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data) {
                    $('#statMembers').text(result.data.total_members || 0);
                    $('#statPending').text(result.data.pending_tasks || 0);
                    $('#statCompleted').text(result.data.completed_tasks || 0);
                }
            });
    }

    function loadTeams() {
        // Get teams where user is a leader
        fetch(`/hrms/api/api_manager.php?action=get_teams`)
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data) {
                    // Filter teams where current user is a leader
                    const myTeams = result.data.filter(team => {
                        // This would need modification in api - for now show all
                        return true;
                    });

                    $('#statTeams').text(myTeams.length);
                    displayTeams(myTeams);
                } else {
                    $('#teamsContainer').html('<p class="text-center text-muted">No teams found</p>');
                }
            });
    }

    function displayTeams(teams) {
        if (teams.length === 0) {
            $('#teamsContainer').html('<p class="text-center text-muted">You are not a leader of any team</p>');
            return;
        }

        let html = '<div class="row">';
        teams.forEach(team => {
            html += `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 team-card" style="cursor: pointer;" onclick="selectTeam(${team.id}, '${escapeHTML(team.name)}', '${escapeHTML(team.description || '')}')">
                        <div class="card-body">
                            <h6 class="card-title">${escapeHTML(team.name)}</h6>
                            <p class="card-text text-muted small">${escapeHTML(team.description || 'No description')}</p>
                            <div class="d-flex align-items-center text-muted small">
                                <i class="ti ti-users me-1"></i>
                                <span>${team.member_count || 0} members</span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <button class="btn btn-sm btn-primary w-100">
                                <i class="ti ti-eye me-1"></i>View Team
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        $('#teamsContainer').html(html);
    }

    function selectTeam(teamId, teamName, teamDesc) {
        currentTeamId = teamId;
        $('#selectedTeamName').text(teamName);
        $('#selectedTeamDesc').text(teamDesc);
        $('#teamsContainer').parent().addClass('d-none');
        $('#teamMembersCard').removeClass('d-none');
        loadTeamMembers(teamId);
    }

    function showTeams() {
        currentTeamId = null;
        $('#teamMembersCard').addClass('d-none');
        $('#teamsContainer').parent().removeClass('d-none');
    }

    function loadTeamMembers(teamId) {
        const tbody = $('#membersTable tbody');
        tbody.html('<tr><td colspan="6" class="text-center"><div class="spinner-border spinner-border-sm"></div></td></tr>');

        fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${teamId}`)
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data && result.data.members) {
                    displayTeamMembers(result.data.members);
                } else {
                    tbody.html('<tr><td colspan="6" class="text-center text-muted">No members found</td></tr>');
                }
            });
    }

    function displayTeamMembers(members) {
        const tbody = $('#membersTable tbody');
        tbody.empty();

        members.forEach(member => {
            const isCurrentUser = member.employee_id == employeeId;
            const totalTasks = parseInt(member.total_tasks) || 0;
            const completedTasks = parseInt(member.completed_tasks) || 0;
            const progressPercent = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;

            // Task progress with interactive progress bar
            const taskProgress = totalTasks > 0 ? `
                <div style="cursor: pointer;" onclick="viewMemberTasks(${member.employee_id}, '${escapeHTML(member.first_name + ' ' + member.last_name)}')">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">${completedTasks}/${totalTasks} completed</small>
                        <small class="fw-bold text-${progressPercent >= 80 ? 'success' : progressPercent >= 50 ? 'warning' : 'danger'}">${progressPercent}%</small>
                    </div>
                    <div class="progress" style="height: 8px; cursor: pointer;" title="Click to view tasks">
                        <div class="progress-bar bg-${progressPercent >= 80 ? 'success' : progressPercent >= 50 ? 'warning' : 'danger'}" 
                             style="width: ${progressPercent}%"></div>
                    </div>
                </div>
            ` : '<span class="text-muted small">No tasks</span>';
            const perfScore = member.avg_performance ?
                `<span class="badge bg-${getScoreColor(member.avg_performance)}">${member.avg_performance}</span>` :
                '<span class="text-muted">N/A</span>';

            const actions = isCurrentUser ?
                '<span class="text-muted small">You</span>' :
                `
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="openAssignTask(${member.employee_id}, '${escapeHTML(member.first_name + ' ' + member.last_name)}')" title="Assign Task">
                        <i class="ti ti-checklist"></i>
                    </button>
                    <button class="btn btn-outline-info" onclick="openPerformanceReview(${member.employee_id}, '${escapeHTML(member.first_name + ' ' + member.last_name)}')" title="Add Review">
                        <i class="ti ti-star"></i>
                    </button>
                </div>
                `;

            tbody.append(`
                <tr>
                    <td>
                        <div class="fw-bold">${escapeHTML(member.first_name)} ${escapeHTML(member.last_name)}</div>
                        <small class="text-muted">${escapeHTML(member.employee_code || 'N/A')}</small>
                    </td>
                    <td>${escapeHTML(member.role_in_team || 'Member')}</td>
                    <td>${escapeHTML(member.designation_name || 'N/A')}</td>
                    <td style="min-width: 150px;">${taskProgress}</td>
                    <td>${perfScore}</td>
                    <td>${actions}</td>
                </tr>
            `);
        });
    }

    function openAssignTask(memberId, memberName) {
        $('#task_employee_id').val(memberId);
        $('#task_employee_name').text(memberName);
        assignTaskModal.show();
    }

    function openPerformanceReview(memberId, memberName) {
        $('#perf_employee_id').val(memberId);
        $('#perf_employee_name').text(memberName);
        performanceModal.show();
    }

    function viewMemberTasks(memberId, memberName) {
        $('#taskModalMemberName').text(memberName);
        $('#taskDetailsContent').html(`
            <div class="text-center py-4">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        taskDetailsModal.show();

        // Fetch member's tasks
        fetch(`/hrms/api/api_manager.php?action=get_member_tasks&employee_id=${memberId}`)
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data && result.data.length > 0) {
                    displayMemberTasks(result.data);
                } else {
                    $('#taskDetailsContent').html(`
                        <div class="text-center text-muted py-4">
                            <i class="ti ti-clipboard-off fs-1 mb-3 d-block"></i>
                            <p>No tasks assigned yet</p>
                        </div>
                    `);
                }
            })
            .catch(error => {
                $('#taskDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="ti ti-alert-circle me-2"></i>
                        Failed to load tasks
                    </div>
                `);
            });
    }

    function displayMemberTasks(tasks) {
        let html = '<div class="list-group">';

        tasks.forEach(task => {
            const statusClass = {
                'pending': 'warning',
                'in_progress': 'info',
                'completed': 'success',
                'cancelled': 'danger'
            }[task.status] || 'secondary';

            const statusIcon = {
                'pending': 'ti-clock',
                'in_progress': 'ti-loader',
                'completed': 'ti-check',
                'cancelled': 'ti-x'
            }[task.status] || 'ti-circle';

            const dueDate = task.due_date ? new Date(task.due_date).toLocaleDateString() : 'No deadline';
            const isOverdue = task.due_date && new Date(task.due_date) < new Date() && task.status !== 'completed';

            html += `
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <i class="ti ${statusIcon} me-1"></i>
                                ${escapeHTML(task.title)}
                            </h6>
                            ${task.description ? `<p class="mb-2 text-muted small">${escapeHTML(task.description)}</p>` : ''}
                            <div class="d-flex gap-3 flex-wrap">
                                <small class="text-muted">
                                    <i class="ti ti-calendar me-1"></i>
                                    Due: ${dueDate}
                                    ${isOverdue ? '<span class="text-danger ms-1">(Overdue!)</span>' : ''}
                                </small>
                                ${task.created_at ? `
                                    <small class="text-muted">
                                        <i class="ti ti-clock me-1"></i>
                                        Assigned: ${new Date(task.created_at).toLocaleDateString()}
                                    </small>
                                ` : ''}
                            </div>
                        </div>
                        <span class="badge bg-${statusClass} ms-2">${task.status.replace('_', ' ').toUpperCase()}</span>
                    </div>
                </div>
            `;
        });

        html += '</div>';

        // Add summary stats at top
        const completed = tasks.filter(t => t.status === 'completed').length;
        const pending = tasks.filter(t => t.status === 'pending').length;
        const inProgress = tasks.filter(t => t.status === 'in_progress').length;

        const summaryHtml = `
            <div class="row mb-3">
                <div class="col-4">
                    <div class="card bg-success text-white">
                        <div class="card-body p-2 text-center">
                            <div class="fs-4 fw-bold">${completed}</div>
                            <small>Completed</small>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body p-2 text-center">
                            <div class="fs-4 fw-bold">${pending}</div>
                            <small>Pending</small>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card bg-info text-white">
                        <div class="card-body p-2 text-center">
                            <div class="fs-4 fw-bold">${inProgress}</div>
                            <small>In Progress</small>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#taskDetailsContent').html(summaryHtml + html);
    }

    function getScoreColor(score) {
        if (score >= 80) return 'success';
        if (score >= 60) return 'warning';
        if (score >= 40) return 'info';
        return 'danger';
    }
</script>

<style>
    .team-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
        transition: all 0.2s;
    }
</style>