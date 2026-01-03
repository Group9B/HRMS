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
    <div class="modal-dialog">
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
    });

    function createTeam() {
        const formData = new FormData(document.getElementById('createTeamForm'));
        formData.append('action', 'create_team');

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    $('#createTeamModal').modal('hide');
                    document.getElementById('createTeamForm').reset();

                    // Fetch the new team details to display
                    fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${data.team_id}`)
                        .then(resp => resp.json())
                        .then(details => {
                            if (details.success) {
                                const team = details.data;
                                const teamsList = document.querySelector('.card-body .row');

                                // Remove empty state if it exists
                                const emptyState = document.querySelector('.text-center.text-muted.p-5');
                                if (emptyState) {
                                    emptyState.parentElement.innerHTML = '<div class="row"></div>';
                                }

                                const newCardHtml = `
                                    <div class="col-lg-6 col-xl-4 mb-4" id="team-card-${team.id}">
                                        <div class="card border-left-primary shadow-sm h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <h6 class="card-title mb-0" id="team-name-${team.id}">${team.name}</h6>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            <i class="ti ti-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="viewTeam(${team.id})"><i class="ti ti-eye me-2"></i>View Details</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="editTeam(${team.id})"><i class="ti ti-edit me-2"></i>Edit Team</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="manageMembers(${team.id})"><i class="ti ti-user-plus me-2"></i>Manage Members</a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteTeam(${team.id})"><i class="ti ti-trash me-2"></i>Delete Team</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <p class="card-text text-muted small mb-3" id="team-desc-${team.id}">
                                                    ${team.description || 'No description provided'}
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-users me-2 text-muted"></i>
                                                        <span class="text-muted small">0 member(s)</span>
                                                    </div>
                                                    <small class="text-muted">Created ${new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</small>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewTeam(${team.id})"><i class="ti ti-eye me-1"></i>View</button>
                                                    <button class="btn btn-sm btn-outline-success" onclick="manageMembers(${team.id})"><i class="ti ti-user-plus me-1"></i>Members</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;

                                // Append to the row
                                const container = document.querySelector('.card-body .row');
                                if (container) {
                                    container.insertAdjacentHTML('afterbegin', newCardHtml);
                                } else {
                                    // If we replaced empty state, finding .row might be tricky if not careful, but we set innerHTML to <div class="row"></div>
                                    document.querySelector('.card-body .row').insertAdjacentHTML('afterbegin', newCardHtml);
                                }

                                // Update counters
                                const totalTeamsEl = document.getElementById('total-teams-count');
                                const activeTeamsEl = document.getElementById('active-teams-count');

                                if (totalTeamsEl) {
                                    let count = parseInt(totalTeamsEl.innerText) || 0;
                                    totalTeamsEl.innerText = count + 1;
                                }

                                if (activeTeamsEl) {
                                    let count = parseInt(activeTeamsEl.innerText) || 0;
                                    activeTeamsEl.innerText = count + 1;
                                }

                                // Update Assign Members Dropdown
                                const teamSelect = document.getElementById('select_team');
                                if (teamSelect) {
                                    const option = document.createElement('option');
                                    option.value = team.id;
                                    option.textContent = team.name;
                                    teamSelect.appendChild(option);
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

                    // Update the specific team card member count
                    const teamId = formData.get('team_id');
                    fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${teamId}`)
                        .then(resp => resp.json())
                        .then(details => {
                            if (details.success) {
                                const team = details.data;
                                const card = document.getElementById(`team-card-${teamId}`);
                                if (card) {
                                    // Update member count text
                                    // Need to find the element containing "X member(s)"
                                    // It's inside .d-flex .align-items-center .text-muted.small
                                    // Let's rely on the structure: <span class="text-muted small"><?= $team['member_count'] ?> member(s)</span>
                                    // It's the only span.text-muted.small that ends with "member(s)" potentially.
                                    // Better to assume structure or find by content.

                                    const memberCountSpan = Array.from(card.querySelectorAll('span.text-muted.small'))
                                        .find(el => el.textContent.includes('member(s)'));

                                    if (memberCountSpan) {
                                        memberCountSpan.textContent = `${team.member_count} member(s)`;
                                    }

                                    // Update member names list
                                    let namesDiv = card.querySelector('.text-truncate');
                                    const names = team.members ? team.members.map(m => `${m.first_name} ${m.last_name}`).join(', ') : '';

                                    if (namesDiv) {
                                        // Update existing
                                        namesDiv.textContent = names;
                                        namesDiv.title = names;
                                        // If names became empty (unlikely in assign, but possible in remove logic if we had it here), remove the block? 
                                        // But here we are assigning, so it grows.
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
                        <div>
                            <div class="fw-bold">${member.first_name} ${member.last_name}</div>
                            <small class="text-muted">${member.designation_name || 'N/A'}</small>
                        </div>
                    </div>
                `).join('') : '<p class="text-muted">No members assigned yet.</p>'}
            </div>
        </div>
    `;

        $('#teamDetailsContent').html(html);
    }

    function editTeam(teamId) {
        fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const team = data.data;
                    $('#edit_team_name').val(team.name);
                    $('#edit_team_description').val(team.description);

                    // Create dynamic form for updating
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