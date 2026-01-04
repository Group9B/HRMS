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
           COUNT(tm.id) as member_count,
           GROUP_CONCAT(CONCAT(e.first_name, ' ', e.last_name) SEPARATOR ', ') as member_names
    FROM teams t
    LEFT JOIN team_members tm ON t.id = tm.team_id
    LEFT JOIN employees e ON tm.employee_id = e.id
    WHERE t.company_id = ? AND t.created_by = ?
    GROUP BY t.id
    ORDER BY t.created_at DESC
", [$company_id, $user_id]);

$teams = $teams_result['success'] ? $teams_result['data'] : [];

// $all_employees logic moved to API (get_available_employees)
$all_employees = [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 text-gray-800">
                <i class="ti ti-users me-2"></i>Team Management
            </h2>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                    <i class="ti ti-plus me-2"></i>Create New Team
                </button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#assignTeamModal">
                    <i class="ti ti-user-plus me-2"></i>Assign Members
                </button>
            </div>
        </div>

        <!-- Teams Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-primary"><i class="ti ti-users"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Teams</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-teams-count">
                                <?= count($teams) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-success"><i class="ti ti-user-check"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Members</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-members">--</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-info"><i class="ti ti-chart-line"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Teams</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-teams-count">
                                <?= count($teams) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-warning"><i class="ti ti-clock"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Recent Teams</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="recent-teams-count">
                                <?= count(array_filter($teams, function ($team) {
                                    return strtotime($team['created_at']) > strtotime('-7 days');
                                })) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teams List -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">My Teams</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($teams)): ?>
                    <div class="row">
                        <?php foreach ($teams as $team): ?>
                            <div class="col-lg-6 col-xl-4 mb-4" id="team-card-<?= $team['id'] ?>">
                                <div class="card border-left-primary shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="card-title mb-0"><?= htmlspecialchars($team['name']) ?></h6>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="viewTeam(<?= $team['id'] ?>)">
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

                                        <p class="card-text text-muted small mb-3">
                                            <?= htmlspecialchars($team['description'] ?: 'No description provided') ?>
                                        </p>

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-users me-2 text-muted"></i>
                                                <span class="text-muted small"><?= $team['member_count'] ?> member(s)</span>
                                            </div>
                                            <small class="text-muted">
                                                Created <?= date('M j, Y', strtotime($team['created_at'])) ?>
                                            </small>
                                        </div>

                                        <?php if (!empty($team['member_names'])): ?>
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">Members:</small>
                                                <div class="text-truncate" title="<?= htmlspecialchars($team['member_names']) ?>">
                                                    <?= htmlspecialchars($team['member_names']) ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="viewTeam(<?= $team['id'] ?>)">
                                                <i class="ti ti-eye me-1"></i>View
                                            </button>
                                            <button class="btn btn-sm btn-outline-success"
                                                onclick="manageMembers(<?= $team['id'] ?>)">
                                                <i class="ti ti-user-plus me-1"></i>Members
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted p-5">
                        <i class="ti ti-users fa-3x mb-3"></i>
                        <h5>No Teams Created Yet</h5>
                        <p>Create your first team to start organizing your workforce.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                            <i class="ti ti-plus me-2"></i>Create Your First Team
                        </button>
                    </div>
                <?php endif; ?>
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

<!-- Team Details Modal -->
<div class="modal fade" id="teamDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Team Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="teamDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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

<?php require_once '../components/layout/footer.php'; ?>

<style>
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }

    .stat-card .card-body {
        display: flex;
        align-items: center;
    }

    .icon-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: white;
        font-size: 20px;
    }
</style>

<script>
    $(document).ready(function () {
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

        // Load team statistics
        loadTeamStats();

        // Load employees when create team modal opens
        $('#createTeamModal').on('show.bs.modal', function () {
            loadEmployeesForCreateTeam();
        });

        // Handle employee selection to update leader dropdown
        $(document).on('change', 'input[name="create_employee_ids[]"]', function () {
            updateLeaderDropdown();
        });
    });

    function createTeam() {
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

                    // Reload team stats
                    loadTeamStats();

                    // Dynamically fetch and append new team card
                    fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${result.teamId}`)
                        .then(resp => resp.json())
                        .then(details => {
                            if (details.success) {
                                const team = details.data;
                                const teamsContainer = document.querySelector('.card-body .row');

                                // Check if we need to remove the "No Teams" empty state
                                const emptyState = document.querySelector('.text-center.text-muted.p-5');
                                if (emptyState) {
                                    // Replace empty state with row container
                                    const cardBody = document.querySelector('.card .card-body');
                                    cardBody.innerHTML = '<div class="row"></div>';
                                }

                                const finalContainer = document.querySelector('.card-body .row');
                                const memberCount = team.member_count || 0;
                                const memberNames = team.member_names || (team.members ? team.members.map(m => `${m.first_name} ${m.last_name}`).join(', ') : '');

                                const newCardHtml = `
                                    <div class="col-lg-6 col-xl-4 mb-4" id="team-card-${team.id}">
                                        <div class="card border-left-primary shadow-sm h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <h6 class="card-title mb-0">${team.name}</h6>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                            data-bs-toggle="dropdown">
                                                            <i class="ti ti-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#"
                                                                    onclick="viewTeam(${team.id})">
                                                                    <i class="ti ti-eye me-2"></i>View Details
                                                                </a></li>
                                                            <li><a class="dropdown-item" href="#"
                                                                    onclick="editTeam(${team.id})">
                                                                    <i class="ti ti-edit me-2"></i>Edit Team
                                                                </a></li>
                                                            <li><a class="dropdown-item" href="#"
                                                                    onclick="manageMembers(${team.id})">
                                                                    <i class="ti ti-user-plus me-2"></i>Manage Members
                                                                </a></li>
                                                            <li><a class="dropdown-item" href="#"
                                                                    onclick="assignTeamLeader(${team.id})">
                                                                    <i class="ti ti-star me-2"></i>Assign Team Leader
                                                                </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#"
                                                                    onclick="deleteTeam(${team.id})">
                                                                    <i class="ti ti-trash me-2"></i>Delete Team
                                                                </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <p class="card-text text-muted small mb-3">
                                                    ${team.description || 'No description provided'}
                                                </p>
                                                <div class="d-flex align-items-center mb-3">
                                                    <span class="text-muted small">
                                                        <i class="ti ti-users me-1"></i>
                                                        ${memberCount} member(s)
                                                    </span>
                                                </div>
                                                ${memberCount > 0 ? `
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block mb-1">Members:</small>
                                                        <div class="text-truncate" title="${memberNames}">
                                                            ${memberNames}
                                                        </div>
                                                    </div>
                                                ` : ''}
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        onclick="viewTeam(${team.id})">
                                                        <i class="ti ti-eye me-1"></i>View
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success"
                                                        onclick="manageMembers(${team.id})">
                                                        <i class="ti ti-user-plus me-1"></i>Members
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;

                                // Insert as first item
                                finalContainer.insertAdjacentHTML('afterbegin', newCardHtml);
                            }
                        });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (!error.message.includes('message')) {
                    showToast('An error occurred. Please try again.', 'error');
                }
            });
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

                    // Update UI Stats
                    loadTeamStats();

                    // Update the specific team card member count and reload details dynamically
                    const teamId = formData.get('team_id');
                    fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${teamId}`)
                        .then(resp => resp.json())
                        .then(details => {
                            if (details.success) {
                                const team = details.data;
                                const card = document.getElementById(`team-card-${teamId}`);
                                if (card) {
                                    // Update member count
                                    const memberCountSpan = Array.from(card.querySelectorAll('span.text-muted.small'))
                                        .find(el => el.textContent.includes('member(s)'));

                                    if (memberCountSpan) {
                                        memberCountSpan.textContent = `${team.member_count} member(s)`;
                                    }

                                    // Update or add member names display
                                    const names = team.members ? team.members.map(m => `${m.first_name} ${m.last_name}`).join(', ') : '';
                                    let namesDiv = card.querySelector('.text-truncate');

                                    if (namesDiv) {
                                        // Update existing
                                        namesDiv.textContent = names;
                                        namesDiv.title = names;
                                    } else if (team.members && team.members.length > 0) {
                                        // Create new block if it doesn't exist
                                        const buttonsDiv = card.querySelector('.d-flex.gap-2');
                                        if (buttonsDiv) {
                                            const membersHtml = `
                                                <div class="mb-3">
                                                    <small class="text-muted d-block mb-1">Members:</small>
                                                    <div class="text-truncate" title="${names}">
                                                        ${names}
                                                    </div>
                                                </div>
                                            `;
                                            buttonsDiv.insertAdjacentHTML('beforebegin', membersHtml);
                                        }
                                    }
                                }

                                // If team details modal is open, refresh it
                                if ($('#teamDetailsModal').hasClass('show')) {
                                    displayTeamDetails(team);
                                }
                            }
                        });


                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function viewTeam(teamId) {
        fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTeamDetails(data.data);
                    $('#teamDetailsModal').modal('show');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    // Handle team selection change in Assign Members Modal
    const selectTeam = document.getElementById('select_team');
    if (selectTeam) {
        selectTeam.addEventListener('change', function () {
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
                    // Create label for list
                    const label = document.createElement('label');
                    label.className = 'form-label mb-2';
                    label.textContent = 'Select Members *';
                    container.appendChild(label);

                    const rowDiv = document.createElement('div');
                    rowDiv.className = 'row';
                    container.appendChild(rowDiv);

                    data.data.forEach(employee => {
                        const col = document.createElement('div');
                        col.className = 'col-md-6 col-lg-4';
                        col.innerHTML = `
                            <div class="form-check">
                                <input class="form-check-input assign-checkbox" type="checkbox" name="employee_ids[]"
                                    value="${employee.id}" id="emp_${employee.id}">
                                <label class="form-check-label" for="emp_${employee.id}">
                                    <div class="fw-bold">
                                        ${employee.first_name} ${employee.last_name}
                                    </div>
                                    <small class="text-muted">${employee.designation_name || 'N/A'}</small>
                                </label>
                            </div>
                        `;
                        rowDiv.appendChild(col);
                    });
                    // Enable submit button when at least one checkbox is checked
                    $('.assign-checkbox').on('change', function () {
                        const checkedCount = $('.assign-checkbox:checked').length;
                        submitBtn.disabled = checkedCount === 0;
                    });

                } else {
                    noDataMsg.classList.remove('d-none');
                }
            })
            .catch(error => {
                loader.classList.add('d-none');
                showToast('Failed to load employees.', 'error');
            });
    }

    function displayTeamDetails(team) {
        const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Team Information</h6>
                <p><strong>Name:</strong> ${team.name}</p>
                <p><strong>Description:</strong> ${team.description || 'No description provided'}</p>
                <p><strong>Created:</strong> ${new Date(team.created_at).toLocaleString()}</p>
                <p><strong>Members:</strong> ${team.member_count}</p>
            </div>
            <div class="col-md-6">
                <h6>Team Members</h6>
                ${team.members ? team.members.map(member => `
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar-circle me-2" style="width: 30px; height: 30px; font-size: 12px;">
                            ${member.first_name.charAt(0)}${member.last_name.charAt(0)}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">
                                ${member.first_name} ${member.last_name}
                                ${member.role_in_team && (member.role_in_team.toLowerCase().includes('leader') || member.role_in_team.toLowerCase().includes('lead')) ?
                '<i class="ti ti-star text-warning ms-1" title="Team Leader"></i>' : ''}
                            </div>
                            <small class="text-muted">
                                ${member.role_in_team ? member.role_in_team : 'Member'} - ${member.designation_name || 'N/A'}
                            </small>
                        </div>
                    </div>
                `).join('') : '<p class="text-muted">No members assigned yet.</p>'}
            </div>
        </div>
    `;

        $('#teamDetailsContent').html(html);
    }

    function assignTeamLeader(teamId) {
        // Reset form
        $('#assignLeaderForm')[0].reset();
        $('#leader_team_id').val(teamId);

        // Load team members and current leaders
        fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.members) {
                    const members = data.data.members;
                    const leaderSelect = $('#leader_employee_id');
                    leaderSelect.empty();
                    leaderSelect.append('<option value="">Choose a member...</option>');

                    // Find current leaders
                    const currentLeaders = members.filter(m =>
                        m.role_in_team && (m.role_in_team.toLowerCase().includes('leader') || m.role_in_team.toLowerCase().includes('lead'))
                    );

                    // Populate dropdown with all members
                    members.forEach(member => {
                        const isLeader = currentLeaders.some(l => l.employee_id === member.employee_id);
                        leaderSelect.append(
                            `<option value="${member.employee_id}">
                                ${member.first_name} ${member.last_name} ${isLeader ? '(Current Leader)' : ''} - ${member.designation_name || 'N/A'}
                            </option>`
                        );
                    });

                    // Show current leaders info if any
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

                    // Reload the page to reflect changes
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

    function editTeam(teamId) {
        fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const team = data.data;
                    $('#edit_team_name').val(team.name);
                    $('#edit_team_description').val(team.description);

                    // Remove any previous handlers and attach new one with the correct teamId
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

                    // Update DOM
                    const nameInput = document.getElementById('edit_team_name').value;
                    const descInput = document.getElementById('edit_team_description').value;

                    // We need to add IDs to these elements in the PHP loop first, but we can try to find them or just reload if IDs absent?
                    // I added IDs in previous step: team-name-${id} and team-desc-${id} presumably? 
                    // Wait, I only added them in the CreateTeam JS above. I need to add them to the PHP loop too.
                    // Let's assume I will add them to PHP loop in next step if not present.
                    // Actually, looking at previous file view of teams.php, I did NOT add IDs to name/desc in PHP loop.
                    // So I must add them now via another edit or use querySelector.
                    // Using querySelector relative to card ID is safer if I don't want to edit PHP again.
                    // card id is team-card-${teamId}

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
        // Redirect to manage members page
        window.location.href = `/hrms/manager/manage_team_members.php?id=${teamId}`;
    }

    function deleteTeam(teamId) {
        if (confirm('Are you sure you want to delete this team? This action cannot be undone.')) {
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

                        // Remove element from DOM
                        const teamCard = document.getElementById(`team-card-${teamId}`);
                        if (teamCard) {
                            teamCard.remove();
                        }

                        // Update counters
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

                        // If no teams left, show empty state (optional but good UI)
                        const teamsContainer = document.querySelector('.card-body .row');
                        if (teamsContainer && teamsContainer.children.length === 0) {
                            teamsContainer.parentElement.innerHTML = `
                                <div class="text-center text-muted p-5">
                                    <i class="ti ti-users fa-3x mb-3"></i>
                                    <h5>No Teams Created Yet</h5>
                                    <p>Create your first team to start organizing your workforce.</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                                        <i class="ti ti-plus me-2"></i>Create Your First Team
                                    </button>
                                </div>
                            `;
                        }

                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                });
        }
    }

    function loadEmployeesForCreateTeam() {
        const container = document.getElementById('createTeamEmployeesContainer');
        const spinner = container.querySelector('.spinner-border');
        const dropdown = document.getElementById('create_team_leader');

        fetch('/hrms/api/api_manager.php?action=get_available_employees')
            .then(response => response.json())
            .then(data => {
                // Clear container but keep spinner logic if needed, or just rebuild
                container.innerHTML = '';

                // Clear leader dropdown
                dropdown.innerHTML = '<option value="">Select a team leader (Optional)</option>';

                if (data.success && data.data.length > 0) {
                    const rowDiv = document.createElement('div');
                    rowDiv.className = 'row';
                    container.appendChild(rowDiv);

                    data.data.forEach(employee => {
                        // Populate checkbox list
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

        // Save current selection if any
        const currentSelection = dropdown.value;

        // Reset dropdown
        dropdown.innerHTML = '<option value="">Select a team leader (Optional)</option>';

        selectedCheckboxes.forEach(cb => {
            const empId = cb.value;
            const empName = cb.getAttribute('data-name');
            const option = document.createElement('option');
            option.value = empId;
            option.text = empName;
            dropdown.appendChild(option);
        });

        // Restore selection if still valid
        if (currentSelection && document.querySelector(`.create-emp-checkbox[value="${currentSelection}"]:checked`)) {
            dropdown.value = currentSelection;
        }
    }

    function loadTeamStats() {
        fetch('/hrms/api/api_manager.php?action=get_team_stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const stats = data.data;
                    $('#total-teams-count').text(stats.total_teams);
                    $('#total-members').text(stats.total_members);
                    $('#active-teams-count').text(stats.active_teams);
                    $('#recent-teams-count').text(stats.recent_teams);
                }
            })
            .catch(error => {
                console.error('Error loading stats:', error);
            });
    }
</script>