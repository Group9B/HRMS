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

// Get teams created by this manager
$teams_result = query($mysqli, "
    SELECT t.*, 
           COUNT(DISTINCT tm.id) as member_count,
           GROUP_CONCAT(DISTINCT CASE WHEN LOWER(tm.role_in_team) LIKE '%leader%' OR LOWER(tm.role_in_team) LIKE '%lead%' 
                        THEN CONCAT(e.first_name, ' ', e.last_name) END SEPARATOR ', ') as team_leaders
    FROM teams t
    LEFT JOIN team_members tm ON t.id = tm.team_id
    LEFT JOIN employees e ON tm.employee_id = e.id
    WHERE t.company_id = ? AND t.created_by = ?
    GROUP BY t.id
    ORDER BY t.created_at DESC
", [$company_id, $user_id]);

$teams = $teams_result['success'] ? $teams_result['data'] : [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 text-gray-800">
                <i class="ti ti-users me-2"></i>Team Management
            </h2>
            <div>
                <a href="teams.php" class="btn btn-info me-2">
                    <i class="ti ti-settings me-2"></i>Manage Teams Structure
                </a>
            </div>
        </div>

        <!-- Teams List View (Default) -->
        <div id="teamsListView">
            <!-- Team Statistics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body">
                            <div class="icon-circle bg-primary"><i class="ti ti-users"></i></div>
                            <div>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Teams</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($teams) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body">
                            <div class="icon-circle bg-success"><i class="ti ti-user-check"></i></div>
                            <div>
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Members
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-members-count">--</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body">
                            <div class="icon-circle bg-info"><i class="ti ti-checklist"></i></div>
                            <div>
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Teams</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($teams) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body">
                            <div class="icon-circle bg-warning"><i class="ti ti-star"></i></div>
                            <div>
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Team Leaders
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-leaders-count">--</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teams Cards -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Your Teams</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($teams)): ?>
                        <div class="row">
                            <?php foreach ($teams as $team): ?>
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="card border-left-primary shadow-sm h-100 team-card" style="cursor: pointer;"
                                        onclick="viewTeamDetails(<?= $team['id'] ?>)">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="card-title mb-0"><?= htmlspecialchars($team['name']) ?></h6>
                                                <span class="badge bg-primary"><?= $team['member_count'] ?> members</span>
                                            </div>

                                            <p class="card-text text-muted small mb-3">
                                                <?= htmlspecialchars($team['description'] ?: 'No description provided') ?>
                                            </p>

                                            <?php if ($team['team_leaders']): ?>
                                                <div class="mb-3">
                                                    <small class="text-muted d-block mb-1">
                                                        <i class="ti ti-star me-1"></i>Team Leader(s):
                                                    </small>
                                                    <div class="fw-bold text-primary" style="font-size: 0.9rem;">
                                                        <?= htmlspecialchars($team['team_leaders']) ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="mb-3">
                                                    <small class="text-warning">
                                                        <i class="ti ti-alert-circle me-1"></i>No team leader assigned
                                                    </small>
                                                </div>
                                            <?php endif; ?>

                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-primary flex-fill"
                                                    onclick="event.stopPropagation(); viewTeamDetails(<?= $team['id'] ?>)">
                                                    <i class="ti ti-eye me-1"></i>View Team
                                                </button>
                                                <button class="btn btn-sm btn-success"
                                                    onclick="event.stopPropagation(); assignTaskToTeam(<?= $team['id'] ?>, '<?= htmlspecialchars($team['name']) ?>')">
                                                    <i class="ti ti-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted p-5">
                            <i class="ti ti-users fa-3x mb-3" style="font-size: 2rem;"></i>
                            <h5>No Teams Found</h5>
                            <p>Create teams from the Manage Teams Structure page.</p>
                            <a href="teams.php" class="btn btn-primary">
                                <i class="ti ti-plus me-2"></i>Create Teams
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Team Details View (Hidden by default) -->
        <div id="teamDetailsView" style="display: none;">
            <div class="mb-4">
                <button class="btn btn-outline-secondary" onclick="backToTeamsList()">
                    <i class="ti ti-arrow-left me-2"></i>Back to Teams
                </button>
            </div>

            <!-- Team Header -->
            <div class="card shadow-sm mb-4" id="teamHeaderCard">
                <!-- Content will be loaded via JavaScript -->
            </div>

            <!-- Team Members Stats -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Team Members Performance</h6>
                    <button class="btn btn-sm btn-success" id="assignTeamTaskBtn">
                        <i class="ti ti-plus me-2"></i>Assign Task to Team
                    </button>
                </div>
                <div class="card-body">
                    <div id="teamMembersContent">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Task to Team Modal -->
<div class="modal fade" id="assignTeamTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Task to Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="teamTaskForm">
                <input type="hidden" id="task_team_id" name="team_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <span id="teamTaskInfo"></span>
                    </div>
                    <div class="mb-3">
                        <label for="team_task_title" class="form-label">Task Title *</label>
                        <input type="text" class="form-control" id="team_task_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="team_task_description" class="form-label">Description</label>
                        <textarea class="form-control" id="team_task_description" name="description"
                            rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="team_task_due_date" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="team_task_due_date" name="due_date">
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

<!-- Update Member Role Modal -->
<div class="modal fade" id="updateRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Team Member Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateRoleForm">
                <input type="hidden" id="role_team_id" name="team_id">
                <input type="hidden" id="role_employee_id" name="employee_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Member Name</label>
                        <input type="text" class="form-control" id="role_member_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="role_in_team" class="form-label">Role in Team</label>
                        <select class="form-select" id="role_in_team" name="role_in_team">
                            <option value="">Team Member</option>
                            <option value="Team Leader">Team Leader</option>
                            <option value="Developer">Developer</option>
                            <option value="Designer">Designer</option>
                            <option value="Tester">Tester</option>
                            <option value="Analyst">Analyst</option>
                        </select>
                        <small class="text-muted">Use "Team Leader" or "Leader" to designate team leaders</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
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

    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }

    .team-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .team-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
    }

    .progress-sm {
        height: 8px;
    }
</style>

<script>
    let currentTeamId = null;
    let currentTeamName = null;

    $(document).ready(function () {
        // Load initial stats
        loadOverallStats();

        // Handle team task form submission
        $('#teamTaskForm').on('submit', function (e) {
            e.preventDefault();
            submitTeamTask();
        });

        // Handle role update form submission
        $('#updateRoleForm').on('submit', function (e) {
            e.preventDefault();
            submitRoleUpdate();
        });
    });

    function loadOverallStats() {
        fetch('/hrms/api/api_manager.php?action=get_team_stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#total-members-count').text(data.data.total_members || 0);
                }
            })
            .catch(error => console.error('Error loading stats:', error));

        // Count leaders
        let leaderCount = 0;
        <?php foreach ($teams as $team): ?>
            <?php if ($team['team_leaders']): ?>
                leaderCount++;
            <?php endif; ?>
        <?php endforeach; ?>
        $('#total-leaders-count').text(leaderCount);
    }

    function viewTeamDetails(teamId) {
        currentTeamId = teamId;

        // Hide teams list, show details
        $('#teamsListView').hide();
        $('#teamDetailsView').show();

        // Load team details
        loadTeamHeader(teamId);
        loadTeamMembersStats(teamId);
    }

    function loadTeamHeader(teamId) {
        fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const team = data.data;
                    currentTeamName = team.name;

                    const leaderNames = team.members
                        .filter(m => m.role_in_team && (m.role_in_team.toLowerCase().includes('leader') || m.role_in_team.toLowerCase().includes('lead')))
                        .map(m => `${m.first_name} ${m.last_name}`)
                        .join(', ');

                    const html = `
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 class="mb-3">${team.name}</h4>
                                    <p class="text-muted mb-3">${team.description || 'No description provided'}</p>
                                    ${leaderNames ? `
                                        <div class="mb-2">
                                            <i class="ti ti-star text-warning me-2"></i>
                                            <strong>Team Leader(s):</strong> ${leaderNames}
                                        </div>
                                    ` : '<div class="text-warning"><i class="ti ti-alert-circle me-2"></i>No team leader assigned</div>'}
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="mb-2"><strong>Total Members:</strong> ${team.member_count}</div>
                                    <div class="mb-2"><strong>Created:</strong> ${new Date(team.created_at).toLocaleDateString()}</div>
                                    <button class="btn btn-success mt-2" onclick="assignTaskToTeam(${teamId}, '${team.name}')">
                                        <i class="ti ti-plus me-2"></i>Assign Task to Team
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#teamHeaderCard').html(html);

                    // Set up assign button
                    $('#assignTeamTaskBtn').off('click').on('click', function () {
                        assignTaskToTeam(teamId, team.name);
                    });
                }
            })
            .catch(error => console.error('Error loading team header:', error));
    }

    function loadTeamMembersStats(teamId) {
        fetch(`/hrms/api/api_manager.php?action=get_team_members_stats&team_id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    displayTeamMembersStats(data.data, teamId);
                } else {
                    $('#teamMembersContent').html('<div class="text-center text-muted p-4">No team members found</div>');
                }
            })
            .catch(error => {
                console.error('Error loading team members stats:', error);
                $('#teamMembersContent').html('<div class="alert alert-danger">Failed to load team members</div>');
            });
    }

    function displayTeamMembersStats(members, teamId) {
        let html = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Role in Team</th>
                            <th>Designation</th>
                            <th>Tasks</th>
                            <th>Progress</th>
                            <th>Performance</th>
                            <th>Attendance (30d)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        members.forEach(member => {
            const completionRate = member.total_tasks > 0
                ? Math.round((member.completed_tasks / member.total_tasks) * 100)
                : 0;

            const performanceScore = Math.round(member.avg_performance);
            const performanceClass = performanceScore >= 80 ? 'success' : performanceScore >= 60 ? 'warning' : 'danger';

            const isLeader = member.role_in_team && (member.role_in_team.toLowerCase().includes('leader') || member.role_in_team.toLowerCase().includes('lead'));

            html += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3">
                                ${member.first_name.charAt(0)}${member.last_name.charAt(0)}
                            </div>
                            <div>
                                <div class="fw-bold">
                                    ${member.first_name} ${member.last_name}
                                    ${isLeader ? '<i class="ti ti-star text-warning ms-1" title="Team Leader"></i>' : ''}
                                </div>
                                <small class="text-muted">${member.employee_code}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge ${isLeader ? 'bg-warning' : 'bg-secondary'}" style="cursor: pointer;" 
                              onclick="updateMemberRole(${teamId}, ${member.id}, '${member.first_name} ${member.last_name}', '${member.role_in_team || ''}')">
                            ${member.role_in_team || 'Member'}
                        </span>
                    </td>
                    <td>${member.designation_name || 'N/A'}</td>
                    <td>
                        <small class="text-muted">
                            <strong>${member.completed_tasks}</strong> / ${member.total_tasks}
                        </small>
                    </td>
                    <td>
                        <div class="progress progress-sm mb-1" style="width: 100px;">
                            <div class="progress-bar bg-primary" style="width: ${completionRate}%"></div>
                        </div>
                        <small class="text-muted">${completionRate}%</small>
                    </td>
                    <td>
                        <span class="badge bg-${performanceClass}">${performanceScore > 0 ? performanceScore + '%' : 'N/A'}</span>
                    </td>
                    <td>${member.attendance_count} days</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewMemberDetails(${member.id})" title="View Details">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button class="btn btn-outline-success" onclick="assignIndividualTask(${member.id})" title="Assign Task">
                                <i class="ti ti-checklist"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        $('#teamMembersContent').html(html);
    }

    function assignTaskToTeam(teamId, teamName) {
        currentTeamId = teamId;
        currentTeamName = teamName;

        $('#task_team_id').val(teamId);
        $('#teamTaskInfo').text(`This task will be assigned to ${teamName}.`);
        $('#assignTeamTaskModal').modal('show');
    }

    function submitTeamTask() {
        const formData = new FormData(document.getElementById('teamTaskForm'));
        formData.append('action', 'assign_team_task');

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    $('#assignTeamTaskModal').modal('hide');
                    document.getElementById('teamTaskForm').reset();

                    // Refresh stats if viewing team details
                    if (currentTeamId) {
                        loadTeamMembersStats(currentTeamId);
                    }
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function updateMemberRole(teamId, employeeId, memberName, currentRole) {
        $('#role_team_id').val(teamId);
        $('#role_employee_id').val(employeeId);
        $('#role_member_name').val(memberName);
        $('#role_in_team').val(currentRole);
        $('#updateRoleModal').modal('show');
    }

    function submitRoleUpdate() {
        const formData = new FormData(document.getElementById('updateRoleForm'));
        formData.append('action', 'update_team_member_role');

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    $('#updateRoleModal').modal('hide');

                    // Refresh team members stats
                    if (currentTeamId) {
                        loadTeamHeader(currentTeamId);
                        loadTeamMembersStats(currentTeamId);
                    }
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function backToTeamsList() {
        $('#teamDetailsView').hide();
        $('#teamsListView').show();
        currentTeamId = null;
        currentTeamName = null;
    }

    function viewMemberDetails(employeeId) {
        window.location.href = `/hrms/employee/profile.php?employee_id=${employeeId}`;
    }

    function assignIndividualTask(employeeId) {
        window.location.href = `/hrms/manager/task_management.php`;
    }
</script>