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
                        THEN CONCAT(e.first_name, ' ', e.last_name) END SEPARATOR ', ') as team_leaders,
           GROUP_CONCAT(DISTINCT CONCAT(e.first_name, ' ', e.last_name) SEPARATOR ', ') as member_names
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
        <div id="teamsListView">
            <div class="row mb-4" id="teamStatsContainer">
                <!-- Stats will be rendered dynamically by renderStatCards() -->
            </div>

            <!-- Teams Cards -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="ti ti-users me-2"></i>Your Teams
                    </h6>
                    <div>
                        <button class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal"
                            data-bs-target="#createTeamModal">
                            <i class="ti ti-plus me-1"></i>Create Team
                        </button>
                        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                            data-bs-target="#assignTeamModal">
                            <i class="ti ti-user-plus me-1"></i>Assign Members
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($teams)): ?>
                                <div class="row">
                                    <?php foreach ($teams as $team): ?>
                                                <div class="col-lg-6 col-xl-4 mb-4" id="team-card-<?= $team['id'] ?>">
                                                    <div class="card border-left-primary shadow-sm h-100" style="cursor: pointer;"
                                                        onclick="viewTeamDetails(<?= $team['id'] ?>)">
                                                        <div class="card-body overflow-visible">
                                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                                <h6 class="card-title mb-0"><?= htmlspecialchars($team['name']) ?></h6>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <span class="badge bg-primary"><?= $team['member_count'] ?> members</span>
                                                                    <div class="dropdown" onclick="event.stopPropagation();">
                                                                        <button class="btn action dropdown-toggle" type="button"
                                                                            data-bs-toggle="dropdown">
                                                                            <i class="ti ti-dots-vertical"></i>
                                                                        </button>
                                                                        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 180px;">
                                                                            <li><a class="dropdown-item" href="#"
                                                                                    onclick="viewTeamDetails(<?= $team['id'] ?>)">
                                                                                    <i class="ti ti-eye me-2"></i>View Details
                                                                                </a></li>
                                                                            <li><a class="dropdown-item" href="#"
                                                                                    onclick="editTeam(<?= $team['id'] ?>)">
                                                                                    <i class="ti ti-edit me-2"></i>Edit Team
                                                                                </a></li>
                                                                            <li><a class="dropdown-item" href="#"
                                                                                    onclick="manageMembers(<?= $team['id'] ?>)">
                                                                                    <i class="ti ti-user-plus me-2"></i>Manage Members
                                                                                </a></li>
                                                                            <li><a class="dropdown-item" href="#"
                                                                                    onclick="assignTeamLeader(<?= $team['id'] ?>)">
                                                                                    <i class="ti ti-star me-2"></i>Assign Team Leader
                                                                                </a></li>
                                                                            <li>
                                                                                <hr class="dropdown-divider">
                                                                            </li>
                                                                            <li><a class="dropdown-item text-danger" href="#"
                                                                                    onclick="deleteTeam(<?= $team['id'] ?>)">
                                                                                    <i class="ti ti-trash me-2"></i>Delete Team
                                                                                </a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
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

                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <small class="text-muted">
                                                                    Created <?= date('M j, Y', strtotime($team['created_at'])) ?>
                                                                </small>
                                                            </div>

                                                            <div class="d-flex gap-2">
                                                                <button class="btn btn-sm btn-primary flex-fill"
                                                                    onclick="event.stopPropagation(); viewTeamDetails(<?= $team['id'] ?>)">
                                                                    <i class="ti ti-eye me-1"></i>View Team
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-warning"
                                                                    onclick="event.stopPropagation(); assignTeamLeader(<?= $team['id'] ?>)"
                                                                    title="Assign Team Leader">
                                                                    <i class="ti ti-user-star"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-success"
                                                                    onclick="event.stopPropagation(); assignTaskToTeam(<?= $team['id'] ?>, '<?= htmlspecialchars($team['name']) ?>')"
                                                                    title="Assign Task">
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
                                    <p>Create your first team to start organizing your workforce.</p>
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#createTeamModal">
                                        <i class="ti ti-plus me-2"></i>Create Your First Team
                                    </button>
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
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Team Members Performance</h6>
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

<!-- Create Team Modal -->
<div class="modal fade" id="createTeamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createTeamForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="team_name" class="form-label">Team Name *</label>
                        <input type="text" class="form-control" id="team_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="team_description" class="form-label">Description</label>
                        <textarea class="form-control" id="team_description" name="description" rows="3"
                            placeholder="Describe the team's purpose and objectives..."></textarea>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Add Team Members (Optional)</label>
                        <div id="createTeamEmployeesContainer">
                            <div class="text-center p-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2">Loading employees...</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="teamLeaderSection" style="display: none;">
                        <label for="create_team_leader" class="form-label">
                            <i class="ti ti-star text-warning me-1"></i>Designate Team Leader (Optional)
                        </label>
                        <select class="form-select" id="create_team_leader" name="team_leader_id">
                            <option value="">No leader (assign later)</option>
                        </select>
                        <small class="text-muted">Select from chosen team members above</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Members Modal -->
<div class="modal fade" id="assignTeamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Team Members</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignMembersForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="select_team" class="form-label">Select Team *</label>
                        <select class="form-select" id="select_team" name="team_id" required>
                            <option value="">Choose a team...</option>
                            <?php foreach ($teams as $team): ?>
                                        <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="loadingAssignEmployees" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="assignEmployeesContainer" class="row">
                        <div class="text-center text-muted p-3">
                            <i class="ti ti-arrow-up me-2"></i>
                            Please select a team first.
                        </div>
                    </div>
                    <div id="noAssignEmployeesMessage" class="text-center text-muted p-3 d-none">
                        <i class="ti ti-info-circle me-2"></i>
                        All eligible employees are already in this team.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="assignMembersBtn" disabled>Assign Members</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Team Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTeamForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_team_name" class="form-label">Team Name *</label>
                        <input type="text" class="form-control" id="edit_team_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_team_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_team_description" name="description"
                            rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Team Leader Modal -->
<div class="modal fade" id="assignLeaderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Team Leader</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignLeaderForm">
                <input type="hidden" id="leader_team_id" name="team_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Select a team member to designate as the team leader. Team leaders will receive tasks assigned
                        to the entire team.
                    </div>
                    <div class="mb-3">
                        <label for="leader_employee_id" class="form-label">Select Team Member *</label>
                        <select class="form-select" id="leader_employee_id" name="employee_id" required>
                            <option value="">Choose a member...</option>
                            <!-- Options will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="leader_role" class="form-label">Leader Role</label>
                        <select class="form-select" id="leader_role" name="role_in_team">
                            <option value="Team Leader">Team Leader</option>
                            <option value="Team Lead">Team Lead</option>
                            <option value="Project Lead">Project Lead</option>
                            <option value="Technical Lead">Technical Lead</option>
                        </select>
                    </div>
                    <div id="currentLeadersInfo" class="alert alert-warning d-none">
                        <i class="ti ti-alert-circle me-2"></i>
                        <span id="currentLeadersText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign as Leader</button>
                </div>
            </form>
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

<script>
    let currentTeamId = null;
    let currentTeamName = null;

    $(document).ready(function () {
        // Load initial stats
        loadOverallStats();

        // Handle create team form submission
        $('#createTeamForm').on('submit', function (e) {
            e.preventDefault();
            createTeam();
        });

        // Handle assign members form submission
        $('#assignMembersForm').on('submit', function (e) {
            e.preventDefault();
            assignMembers();
        });

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

        // Load employees when create team modal opens
        $('#createTeamModal').on('show.bs.modal', function () {
            loadEmployeesForCreateTeam();
        });

        // Handle employee selection to update leader dropdown
        $(document).on('change', 'input[name="create_employee_ids[]"]', function () {
            updateLeaderDropdown();
        });

        // Handle team selection change in Assign Members Modal
        $('#select_team').on('change', function () {
            const teamId = this.value;
            if (teamId) {
                loadEmployeesForTeam(teamId);
            } else {
                document.getElementById('assignEmployeesContainer').innerHTML = `
                    <div class="text-center text-muted p-3">
                        <i class="ti ti-arrow-up me-2"></i>
                        Please select a team first.
                    </div>
                `;
            }
        });

        // Handle assign leader form submission
        $('#assignLeaderForm').on('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'update_team_member_role');

            fetch('/hrms/api/api_manager.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        $('#assignLeaderModal').modal('hide');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                });
        });
    });

    function loadOverallStats() {
        // Count leaders from PHP teams data
        let leaderCount = 0;
        <?php foreach ($teams as $team): ?>
                    <?php if ($team['team_leaders']): ?>
                                leaderCount++;
                    <?php endif; ?>
        <?php endforeach; ?>

        fetch('/hrms/api/api_manager.php?action=get_team_stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.data;

                    // Use renderStatCards from main.js
                    renderStatCards('teamStatsContainer', [
                        { label: 'Total Teams', value: stats.total_teams || 0, color: 'primary', icon: 'users-group' },
                        { label: 'Total Members', value: stats.total_members || 0, color: 'success', icon: 'users' },
                        { label: 'Active Teams', value: stats.active_teams || 0, color: 'info', icon: 'circle-check' },
                        { label: 'Team Leaders', value: leaderCount, color: 'warning', icon: 'star' }
                    ]);
                }
            })
            .catch(error => console.error('Error loading stats:', error));
    }

    function createTeam() {
        const nameInput = document.getElementById('team_name');
        const teamName = nameInput.value;
        const formData = new FormData(document.getElementById('createTeamForm'));
        formData.append('action', 'create_team');

        // Get selected employee IDs
        const selectedEmployees = [];
        document.querySelectorAll('input[name="create_employee_ids[]"]:checked').forEach(checkbox => {
            selectedEmployees.push(checkbox.value);
        });

        const teamLeaderId = $('#create_team_leader').val();

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const teamId = data.team_id;

                    // If members selected, assign them
                    if (selectedEmployees.length > 0) {
                        const memberFormData = new FormData();
                        memberFormData.append('action', 'assign_team_members');
                        memberFormData.append('team_id', teamId);
                        selectedEmployees.forEach(empId => {
                            memberFormData.append('employee_ids[]', empId);
                        });

                        return fetch('/hrms/api/api_manager.php', {
                            method: 'POST',
                            body: memberFormData
                        }).then(resp => resp.json()).then(memberData => {
                            // If team leader selected, assign leader role
                            if (teamLeaderId) {
                                const leaderFormData = new FormData();
                                leaderFormData.append('action', 'update_team_member_role');
                                leaderFormData.append('team_id', teamId);
                                leaderFormData.append('employee_id', teamLeaderId);
                                leaderFormData.append('role_in_team', 'Team Leader');

                                return fetch('/hrms/api/api_manager.php', {
                                    method: 'POST',
                                    body: leaderFormData
                                }).then(resp => resp.json()).then(() => {
                                    return { teamId, success: true };
                                });
                            }
                            return { teamId, success: true };
                        });
                    }
                    return { teamId, success: true };
                } else {
                    showToast(data.message, 'error');
                    throw new Error(data.message);
                }
            })
            .then(result => {
                if (result.success) {
                    showToast('Team created successfully!', 'success');
                    $('#createTeamModal').modal('hide');
                    document.getElementById('createTeamForm').reset();

                    // Add to Assign Members dropdown
                    const selectTeam = document.getElementById('select_team');
                    const newOption = new Option(teamName, result.teamId);
                    selectTeam.add(newOption);

                    // Add card to view
                    addTeamCardToView(result.teamId);

                    // Update stats
                    updateStatsUI(1); // Increment count
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (!error.message.includes('message')) {
                    showToast('An error occurred. Please try again.', 'error');
                }
            });
    }

    function addTeamCardToView(teamId) {
        fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const team = data.data;
                    const container = document.querySelector('.card-body .row');
                    const noTeamsDiv = document.querySelector('.card-body .text-center.text-muted');

                    // Remove "No Teams" message if it exists
                    if (noTeamsDiv) {
                        noTeamsDiv.remove();
                        // Create row if it doesn't exist
                        if (!container) {
                            const cardBody = document.querySelector('#teamsListView .card-body');
                            const row = document.createElement('div');
                            row.className = 'row';
                            cardBody.appendChild(row);
                        }
                    }

                    const targetContainer = document.querySelector('.card-body .row');
                    if (targetContainer) {
                        // Generate leader names string
                        const leaders = team.members
                            .filter(m => m.role_in_team && (m.role_in_team.toLowerCase().includes('leader') || m.role_in_team.toLowerCase().includes('lead')))
                            .map(m => `${m.first_name} ${m.last_name}`)
                            .join(', ');

                        team.team_leaders = leaders; // Add for template use

                        const cardHTML = generateTeamCardHTML(team);

                        targetContainer.insertAdjacentHTML('afterbegin', cardHTML);
                    }
                }
            });
    }

    function generateTeamCardHTML(team) {
        return `
            <div class="col-lg-6 col-xl-4 mb-4" id="team-card-${team.id}">
                <div class="card border-left-primary shadow-sm h-100" style="cursor: pointer;" onclick="viewTeamDetails(${team.id})">
                    <div class="card-body overflow-visible">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="card-title mb-0">${escapeHtml(team.name)}</h6>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary-emphasis">${team.member_count} members</span>
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-sm action dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 180px;">
                                        <li><a class="dropdown-item" href="#" onclick="viewTeamDetails(${team.id})"><i class="ti ti-eye me-2"></i>View Details</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="editTeam(${team.id})"><i class="ti ti-edit me-2"></i>Edit Team</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="manageMembers(${team.id})"><i class="ti ti-user-plus me-2"></i>Manage Members</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="assignTeamLeader(${team.id})"><i class="ti ti-star me-2"></i>Assign Team Leader</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteTeam(${team.id})"><i class="ti ti-trash me-2"></i>Delete Team</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <p class="card-text text-muted small mb-3">${escapeHtml(team.description || 'No description provided')}</p>
                        ${team.team_leaders ? `
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="ti ti-star me-1"></i>Team Leader(s):</small>
                                <div class="fw-bold text-primary" style="font-size: 0.9rem;">${escapeHtml(team.team_leaders)}</div>
                            </div>
                        ` : `
                            <div class="mb-3">
                                <small class="text-warning"><i class="ti ti-alert-circle me-1"></i>No team leader assigned</small>
                            </div>
                        `}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">Created ${new Date(team.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary flex-fill" onclick="event.stopPropagation(); viewTeamDetails(${team.id})">
                                <i class="ti ti-eye me-1"></i>View Team
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="event.stopPropagation(); assignTeamLeader(${team.id})" title="Assign Team Leader">
                                <i class="ti ti-user-star"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="event.stopPropagation(); assignTaskToTeam(${team.id}, '${escapeHtml(team.name).replace(/'/g, "\\'")}')" title="Assign Task">
                                <i class="ti ti-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function updateStatsUI(increment) {
        loadOverallStats();
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function assignMembers() {
        const formData = new FormData(document.getElementById('assignMembersForm'));
        formData.append('action', 'assign_team_members');

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
                    $('#assignTeamModal').modal('hide');
                    document.getElementById('assignMembersForm').reset();
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function loadEmployeesForTeam(teamId) {
        const container = document.getElementById('assignEmployeesContainer');
        const loader = document.getElementById('loadingAssignEmployees');
        const noDataMsg = document.getElementById('noAssignEmployeesMessage');
        const submitBtn = document.getElementById('assignMembersBtn');

        container.innerHTML = '';
        loader.classList.remove('d-none');
        noDataMsg.classList.add('d-none');
        submitBtn.disabled = true;

        fetch(`/hrms/api/api_manager.php?action=get_available_employees&team_id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                loader.classList.add('d-none');
                if (data.success && data.data.length > 0) {

                    // Add Search Bar
                    const searchBar = document.createElement('div');
                    searchBar.className = 'mb-3';
                    searchBar.innerHTML = `
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input type="text" id="employeeSearchInput" class="form-control" placeholder="Search by name or code...">
                        </div>
                    `;
                    container.appendChild(searchBar);

                    const scrollableContainer = document.createElement('div');
                    scrollableContainer.style.maxHeight = '400px';
                    scrollableContainer.style.overflowY = 'auto';
                    scrollableContainer.className = 'p-1'; // Add padding for shadow/outline visibility
                    container.appendChild(scrollableContainer);

                    // Separate Recent Hires
                    const today = new Date();
                    const recentHires = data.data.filter(emp => {
                        if (!emp.date_of_joining) return false;
                        const joinDate = new Date(emp.date_of_joining);
                        const diffTime = Math.abs(today - joinDate);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        return diffDays <= 30;
                    });

                    if (recentHires.length > 0) {
                        const recentLabel = document.createElement('h6');
                        recentLabel.className = 'text-primary mb-2 sticky-top py-1';
                        recentLabel.style.backgroundColor = 'var(--bs-modal-bg)';
                        recentLabel.style.zIndex = '5';
                        recentLabel.innerHTML = '<i class="ti ti-sparkles me-1"></i>Recent Hires';
                        scrollableContainer.appendChild(recentLabel);

                        const recentRow = document.createElement('div');
                        recentRow.className = 'row mb-4 g-2';
                        scrollableContainer.appendChild(recentRow);

                        renderEmployeeCards(recentHires, recentRow);
                    }

                    // All Employees Section
                    const allLabel = document.createElement('h6');
                    allLabel.className = 'text-muted mb-2 sticky-top py-1';
                    allLabel.style.backgroundColor = 'var(--bs-modal-bg)';
                    allLabel.style.zIndex = '5';
                    allLabel.textContent = 'All Employees';
                    scrollableContainer.appendChild(allLabel);

                    const allRow = document.createElement('div');
                    allRow.className = 'row g-2';
                    scrollableContainer.appendChild(allRow);

                    renderEmployeeCards(data.data, allRow);

                    // Add Search Functionality
                    const searchInput = document.getElementById('employeeSearchInput');
                    searchInput.addEventListener('keyup', function () {
                        const filter = this.value.toLowerCase();
                        const cards = scrollableContainer.querySelectorAll('.col-md-6'); // Selector matches the col div

                        cards.forEach(card => {
                            const rect = card.getBoundingClientRect(); // check visibility if needed, but display none is enough
                            const text = card.textContent.toLowerCase();
                            if (text.includes(filter)) {
                                card.style.display = '';
                            } else {
                                card.style.display = 'none';
                            }
                        });

                        // Hide section titles if no visible items (optional optimization)
                    });

                    // Event delegation for checkboxes
                    $(scrollableContainer).on('change', '.assign-checkbox', function () {
                        const checkedCount = $('.assign-checkbox:checked').length;
                        submitBtn.disabled = checkedCount === 0;
                    });

                } else {
                    noDataMsg.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error loading employees:', error);
                loader.classList.add('d-none');
                showToast('Failed to load employees', 'error');
            });
    }

    function renderEmployeeCards(employees, container) {
        employees.forEach(employee => {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4';
            col.innerHTML = `
                <div class="card h-100 border shadow-sm">
                    <div class="card-body p-2">
                        <div class="form-check d-flex align-items-center mb-0">
                            <input class="form-check-input assign-checkbox me-2 mt-0" type="checkbox" name="employee_ids[]"
                                value="${employee.id}" id="emp_${employee.id}_${Math.random().toString(36).substr(2, 5)}">
                            <label class="form-check-label w-100 cursor-pointer" for="emp_${employee.id}">
                                <div class="fw-bold text-truncate" style="max-width: 150px;" title="${employee.first_name} ${employee.last_name}">
                                    ${employee.first_name} ${employee.last_name}
                                </div>
                                <small class="text-muted d-block text-truncate" style="max-width: 150px;">
                                    ${employee.designation_name || 'N/A'}
                                </small>
                            </label>
                        </div>
                    </div>
                </div>
            `;
            // Fix ID collision in label 'for' attribute by using the generated ID
            const input = col.querySelector('input');
            const label = col.querySelector('label');
            label.setAttribute('for', input.id);

            container.appendChild(col);
        });
    }


    function loadEmployeesForCreateTeam() {
        const container = document.getElementById('createTeamEmployeesContainer');
        const dropdown = document.getElementById('create_team_leader');

        fetch('/hrms/api/api_manager.php?action=get_available_employees')
            .then(response => response.json())
            .then(data => {
                container.innerHTML = '';
                dropdown.innerHTML = '<option value="">Select a team leader (Optional)</option>';

                if (data.success && data.data.length > 0) {
                    const rowDiv = document.createElement('div');
                    rowDiv.className = 'row';
                    container.appendChild(rowDiv);

                    data.data.forEach(employee => {
                        const col = document.createElement('div');
                        col.className = 'col-md-6 col-lg-4 mb-2';
                        col.innerHTML = `
                            <div class="form-check">
                                <input class="form-check-input create-emp-checkbox" type="checkbox" name="create_employee_ids[]"
                                    value="${employee.id}" id="create_emp_${employee.id}" data-name="${employee.first_name} ${employee.last_name}">
                                <label class="form-check-label" for="create_emp_${employee.id}">
                                    <div class="fw-bold text-truncate" style="max-width: 150px;">
                                        ${employee.first_name} ${employee.last_name}
                                    </div>
                                    <small class="text-muted">${employee.designation_name || 'N/A'}</small>
                                </label>
                            </div>
                        `;
                        rowDiv.appendChild(col);
                    });
                } else {
                    container.innerHTML = '<div class="text-center text-muted">No employees available to assign.</div>';
                }
            })
            .catch(error => {
                container.innerHTML = '<div class="text-danger text-center">Failed to load employees.</div>';
            });
    }

    function updateLeaderDropdown() {
        const dropdown = document.getElementById('create_team_leader');
        const selectedCheckboxes = document.querySelectorAll('.create-emp-checkbox:checked');

        const currentSelection = dropdown.value;
        dropdown.innerHTML = '<option value="">Select a team leader (Optional)</option>';

        selectedCheckboxes.forEach(cb => {
            const empId = cb.value;
            const empName = cb.getAttribute('data-name');
            const option = document.createElement('option');
            option.value = empId;
            option.text = empName;
            dropdown.appendChild(option);
        });

        if (currentSelection && document.querySelector(`.create-emp-checkbox[value="${currentSelection}"]:checked`)) {
            dropdown.value = currentSelection;
        }

        // Show/hide leader section based on selection
        const leaderSection = document.getElementById('teamLeaderSection');
        leaderSection.style.display = selectedCheckboxes.length > 0 ? 'block' : 'none';
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

            const avatarData = generateAvatarData({ id: member.id, username: member.employee_code });

            html += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3" style="background-color: ${avatarData.color};">
                                ${avatarData.initials}
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
                        <span class="badge ${isLeader ? 'bg-warning-subtle text-warning-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'}" style="cursor: pointer;" 
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
                        <span class="badge bg-${performanceClass}-subtle text-${performanceClass}-emphasis">${performanceScore > 0 ? performanceScore + '%' : 'N/A'}</span>
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

    function editTeam(teamId) {
        fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const team = data.data;
                    $('#edit_team_name').val(team.name);
                    $('#edit_team_description').val(team.description);

                    $('#editTeamForm').off('submit').on('submit', function (e) {
                        e.preventDefault();
                        updateTeam(teamId);
                    });

                    $('#editTeamModal').modal('show');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error loading team:', error);
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function updateTeam(teamId) {
        const formData = new FormData(document.getElementById('editTeamForm'));
        formData.append('action', 'update_team');
        formData.append('team_id', teamId);

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    $('#editTeamModal').modal('hide');

                    const nameInput = document.getElementById('edit_team_name').value;
                    const descInput = document.getElementById('edit_team_description').value;

                    const card = document.getElementById(`team-card-${teamId}`);
                    if (card) {
                        const titleEl = card.querySelector('.card-title');
                        const descEl = card.querySelector('.card-text');
                        if (titleEl) titleEl.textContent = nameInput;
                        if (descEl) descEl.textContent = descInput || 'No description provided';
                    }

                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function manageMembers(teamId) {
        window.location.href = `/hrms/manager/manage_team_members.php?id=${teamId}`;
    }

    function deleteTeam(teamId) {
        showConfirmationModal(
            'Are you sure you want to delete this team? This action cannot be undone.',
            function () {
                const formData = new FormData();
                formData.append('action', 'delete_team');
                formData.append('team_id', teamId);

                fetch('/hrms/api/api_manager.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');

                            const teamCard = document.getElementById(`team-card-${teamId}`);
                            if (teamCard) {
                                teamCard.remove();
                            }

                            const totalTeamsEl = document.getElementById('total-teams-count');
                            const activeTeamsEl = document.getElementById('active-teams-count');

                            if (totalTeamsEl) {
                                let count = parseInt(totalTeamsEl.innerText) || 0;
                                totalTeamsEl.innerText = Math.max(0, count - 1);
                            }

                            if (activeTeamsEl) {
                                let count = parseInt(activeTeamsEl.innerText) || 0;
                                activeTeamsEl.innerText = Math.max(0, count - 1);
                            }

                            const teamsContainer = document.querySelector('.card-body .row');
                            if (teamsContainer && teamsContainer.children.length === 0) {
                                const listView = document.getElementById('teamsListView');
                                if (listView && listView.style.display !== 'none') {
                                    const cardBody = teamsContainer.closest('.card-body');
                                    if (cardBody) {
                                        cardBody.innerHTML = `
                                            <div class="text-center text-muted p-5">
                                                <i class="ti ti-users fa-3x mb-3" style="font-size: 2rem;"></i>
                                                <h5>No Teams Created Yet</h5>
                                                <p>Create your first team to start organizing your workforce.</p>
                                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                                                    <i class="ti ti-plus me-2"></i>Create Your First Team
                                                </button>
                                            </div>
                                        `;
                                    }
                                }
                            }

                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showToast('An error occurred. Please try again.', 'error');
                    });
            },
            'Delete Team',
            'Delete'
        );
    }

    function assignTeamLeader(teamId) {
        $('#assignLeaderForm')[0].reset();
        $('#leader_team_id').val(teamId);

        fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.members) {
                    const members = data.data.members;
                    const leaderSelect = $('#leader_employee_id');
                    leaderSelect.empty();
                    leaderSelect.append('<option value="">Choose a member...</option>');

                    const currentLeaders = members.filter(m =>
                        m.role_in_team && (m.role_in_team.toLowerCase().includes('leader') || m.role_in_team.toLowerCase().includes('lead'))
                    );

                    members.forEach(member => {
                        const isLeader = currentLeaders.some(l => l.employee_id === member.employee_id);
                        leaderSelect.append(
                            `<option value="${member.employee_id}">
                                ${member.first_name} ${member.last_name} ${isLeader ? '(Current Leader)' : ''} - ${member.designation_name || 'N/A'}
                            </option>`
                        );
                    });

                    if (currentLeaders.length > 0) {
                        const leaderNames = currentLeaders.map(l => `${l.first_name} ${l.last_name}`).join(', ');
                        $('#currentLeadersText').text(`Current Team Leader(s): ${leaderNames}`);
                        $('#currentLeadersInfo').removeClass('d-none');
                    } else {
                        $('#currentLeadersInfo').addClass('d-none');
                    }

                    $('#assignLeaderModal').modal('show');
                } else {
                    showToast('Failed to load team members', 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred while loading team members', 'error');
            });
    }

    function viewMemberDetails(employeeId) {
        window.location.href = `/hrms/employee/profile.php?employee_id=${employeeId}`;
    }

    function assignIndividualTask(employeeId) {
        window.location.href = `/hrms/manager/task_management.php`;
    }

    function removeMemberFromTeam(teamId, employeeId, memberName) {
        showConfirmationModal(
            `Are you sure you want to remove <strong>${escapeHTML(memberName)}</strong> from this team?`,
            function () {
                const formData = new FormData();
                formData.append('action', 'remove_team_member');
                formData.append('team_id', teamId);
                formData.append('employee_id', employeeId);

                fetch('/hrms/api/api_manager.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Member removed successfully', 'success');
                            loadTeamMembersStats(teamId);
                            loadOverallStats();
                        } else {
                            showToast(data.message || 'Failed to remove member', 'error');
                        }
                    })
                    .catch(error => {
                        showToast('An error occurred. Please try again.', 'error');
                    });
            },
            'Remove Team Member',
            'Remove',
            'btn-danger'
        );
    }
</script>