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

// Get all employees in the company for team assignment
$all_employees_result = query($mysqli, "
    SELECT e.*, u.email, d.name as department_name, des.name as designation_name
    FROM employees e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN designations des ON e.designation_id = des.id
    WHERE e.status = 'active'
    ORDER BY e.first_name ASC
", []);

$all_employees = $all_employees_result['success'] ? $all_employees_result['data'] : [];

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
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($teams) ?></div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
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
                            <div class="col-lg-6 col-xl-4 mb-4">
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
                    <div class="mb-3">
                        <label class="form-label">Select Members *</label>
                        <div class="row">
                            <?php foreach ($all_employees as $employee): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="employee_ids[]"
                                            value="<?= $employee['id'] ?>" id="emp_<?= $employee['id'] ?>">
                                        <label class="form-check-label" for="emp_<?= $employee['id'] ?>">
                                            <div class="fw-bold">
                                                <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                            </div>
                                            <small
                                                class="text-muted"><?= htmlspecialchars($employee['designation_name'] ?? 'N/A') ?></small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Members</button>
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
                    location.reload();
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
                    location.reload();
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
                    location.reload();
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

    function loadTeamStats() {
        // Calculate total members across all teams
        let totalMembers = 0;
        <?php foreach ($teams as $team): ?>
            totalMembers += <?= $team['member_count'] ?>;
        <?php endforeach; ?>
        $('#total-members').text(totalMembers);
    }
</script>