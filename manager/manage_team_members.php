<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Manage Team Members";

if (!isLoggedIn() || $_SESSION['role_id'] !== 6) {
    redirect("/hrms/auth/login.php");
}

$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];
$team_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($team_id <= 0) {
    redirect("/hrms/manager/teams.php");
}

// Get manager's employee record
$manager_result = query($mysqli, "SELECT * FROM employees WHERE user_id = ?", [$user_id]);
$manager = $manager_result['success'] ? $manager_result['data'][0] : null;

if (!$manager) {
    redirect("/hrms/pages/unauthorized.php");
}

// Get team details
$team_result = query($mysqli, "
    SELECT t.*, COUNT(tm.id) as member_count
    FROM teams t
    LEFT JOIN team_members tm ON t.id = tm.team_id
    WHERE t.id = ? AND t.company_id = ? AND t.created_by = ?
    GROUP BY t.id
", [$team_id, $company_id, $user_id]);

if (!$team_result['success'] || empty($team_result['data'])) {
    redirect("/hrms/manager/teams.php");
}

$team = $team_result['data'][0];

// Get current team members
$members_result = query($mysqli, "
    SELECT tm.*, e.first_name, e.last_name, e.employee_code, e.contact,
           des.name as designation_name, d.name as department_name
    FROM team_members tm
    JOIN employees e ON tm.employee_id = e.id
    LEFT JOIN designations des ON e.designation_id = des.id
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE tm.team_id = ?
    ORDER BY tm.assigned_at DESC
", [$team_id]);

$current_members = $members_result['success'] ? $members_result['data'] : [];

// Get all employees not in this team
$available_employees_result = query($mysqli, "
    SELECT e.*, u.email, des.name as designation_name, d.name as department_name
    FROM employees e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN designations des ON e.designation_id = des.id
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE e.status = 'active' 
    AND e.id NOT IN (
        SELECT tm.employee_id 
        FROM team_members tm 
        WHERE tm.team_id = ?
    )
    ORDER BY e.first_name ASC
", [$team_id]);

$available_employees = $available_employees_result['success'] ? $available_employees_result['data'] : [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 text-gray-800">
                    <i class="fas fa-users me-2"></i>Manage Team Members
                </h2>
                <p class="text-muted mb-0">Team: <strong><?= htmlspecialchars($team['name']) ?></strong></p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMembersModal">
                    <i class="fas fa-user-plus me-2"></i>Add Members
                </button>
                <a href="teams.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Teams
                </a>
            </div>
        </div>

        <!-- Team Info Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="card-title"><?= htmlspecialchars($team['name']) ?></h5>
                        <p class="card-text text-muted">
                            <?= htmlspecialchars($team['description'] ?: 'No description provided') ?></p>
                        <div class="d-flex gap-4">
                            <div>
                                <small class="text-muted">Created:</small>
                                <div class="fw-bold"><?= date('M j, Y', strtotime($team['created_at'])) ?></div>
                            </div>
                            <div>
                                <small class="text-muted">Members:</small>
                                <div class="fw-bold"><?= $team['member_count'] ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group">
                            <button class="btn btn-outline-primary" onclick="editTeamInfo()">
                                <i class="fas fa-edit me-1"></i>Edit Team
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteTeam()">
                                <i class="fas fa-trash me-1"></i>Delete Team
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Members -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Current Team Members (<?= count($current_members) ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($current_members)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="membersTable">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Designation</th>
                                    <th>Department</th>
                                    <th>Contact</th>
                                    <th>Joined Team</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($current_members as $member): ?>
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
                                                    <small
                                                        class="text-muted"><?= htmlspecialchars($member['employee_code'] ?? 'N/A') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($member['designation_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($member['department_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($member['contact'] ?? 'N/A') ?></td>
                                        <td><?= date('M j, Y', strtotime($member['assigned_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-info"
                                                    onclick="viewMember(<?= $member['employee_id'] ?>)" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-danger"
                                                    onclick="removeMember(<?= $member['employee_id'] ?>)"
                                                    title="Remove from Team">
                                                    <i class="fas fa-user-minus"></i>
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
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5>No Members in This Team</h5>
                        <p>Add members to start building your team.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMembersModal">
                            <i class="fas fa-user-plus me-2"></i>Add Members
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addMembersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Members to Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addMembersForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Members to Add</label>
                        <div class="row">
                            <?php foreach ($available_employees as $employee): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="employee_ids[]"
                                            value="<?= $employee['id'] ?>" id="add_emp_<?= $employee['id'] ?>">
                                        <label class="form-check-label" for="add_emp_<?= $employee['id'] ?>">
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
                        <?php if (empty($available_employees)): ?>
                            <div class="text-center text-muted p-3">
                                <i class="fas fa-info-circle me-2"></i>
                                All available employees are already in this team.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" <?= empty($available_employees) ? 'disabled' : '' ?>>Add Selected Members</button>
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
                        <input type="text" class="form-control" id="edit_team_name" name="name"
                            value="<?= htmlspecialchars($team['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_team_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_team_description" name="description"
                            rows="3"><?= htmlspecialchars($team['description']) ?></textarea>
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

<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#membersTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[4, 'desc']] // Sort by joined date
        });

        // Handle add members form submission
        $('#addMembersForm').on('submit', function (e) {
            e.preventDefault();
            addMembers();
        });

        // Handle edit team form submission
        $('#editTeamForm').on('submit', function (e) {
            e.preventDefault();
            editTeam();
        });
    });

    function addMembers() {
        const formData = new FormData(document.getElementById('addMembersForm'));
        formData.append('action', 'assign_team_members');
        formData.append('team_id', <?= $team_id ?>);

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
                    $('#addMembersModal').modal('hide');
                    location.reload();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    function editTeamInfo() {
        $('#editTeamModal').modal('show');
    }

    function editTeam() {
        const formData = new FormData(document.getElementById('editTeamForm'));
        formData.append('action', 'update_team');
        formData.append('team_id', <?= $team_id ?>);

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

    function removeMember(employeeId) {
        if (confirm('Are you sure you want to remove this member from the team?')) {
            const formData = new FormData();
            formData.append('action', 'remove_team_member');
            formData.append('team_id', <?= $team_id ?>);
            formData.append('employee_id', employeeId);

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

    function viewMember(employeeId) {
        // Redirect to employee profile or show modal
        window.open(`/hrms/employee/profile.php?employee_id=${employeeId}`, '_self');
    }

    function deleteTeam() {
        if (confirm('Are you sure you want to delete this team? This action cannot be undone and will remove all team members.')) {
            const formData = new FormData();
            formData.append('action', 'delete_team');
            formData.append('team_id', <?= $team_id ?>);

            fetch('/hrms/api/api_manager.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        window.location.href = '/hrms/manager/teams.php';
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                });
        }
    }
</script>