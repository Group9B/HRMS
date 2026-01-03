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

// $available_employees logic moved to API (get_available_employees)
$available_employees = []; // Initialize empty array to prevent PHP undefined variable error in initial render if referenced elsewhere before JS loads

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 text-gray-800">
                    <i class="ti ti-users me-2"></i>Manage Team Members
                </h2>
                <p class="text-muted mb-0">Team: <strong><?= htmlspecialchars($team['name']) ?></strong></p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMembersModal">
                    <i class="ti ti-user-plus me-2"></i>Add Members
                </button>
                <a href="teams.php" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-2"></i>Back to Teams
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
                            <?= htmlspecialchars($team['description'] ?: 'No description provided') ?>
                        </p>
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
                                <i class="ti ti-edit me-1"></i>Edit Team
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteTeam()">
                                <i class="ti ti-trash me-1"></i>Delete Team
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
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-danger"
                                                    onclick="removeMember(<?= $member['employee_id'] ?>)"
                                                    title="Remove from Team">
                                                    <i class="ti ti-user-minus"></i>
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
                        <i class="ti ti-users" style="font-size: 3rem;"></i>
                        <div class="mb-3"></div>
                        <h5>No Members in This Team</h5>
                        <p>Add members to start building your team.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMembersModal">
                            <i class="ti ti-user-plus me-2"></i>Add Members
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
                        <div id="loadingEmployees" class="text-center py-4 d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="row" id="availableEmployeesContainer">
                            <!-- Employees will be loaded here via JS -->
                        </div>
                        <div id="noEmployeesMessage" class="text-center text-muted p-3 d-none">
                            <i class="ti ti-info-circle me-2"></i>
                            All available employees are already in this team or no eligible employees found.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addMembersBtn" disabled>Add Selected
                        Members</button>
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

        const submitBtn = document.getElementById('addMembersBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Adding...';

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Add Selected Members';

                if (data.success) {
                    showToast(data.message, 'success');
                    $('#addMembersModal').modal('hide');
                    refreshMembersTable();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Add Selected Members';
                showToast('An error occurred. Please try again.', 'error');
            });
    }

    // Load available employees when modal is shown
    const addMembersModal = document.getElementById('addMembersModal');
    if (addMembersModal) {
        addMembersModal.addEventListener('show.bs.modal', function () {
            loadAvailableEmployees();
        });
    }

    function loadAvailableEmployees() {
        const container = document.getElementById('availableEmployeesContainer');
        const loader = document.getElementById('loadingEmployees');
        const noDataMsg = document.getElementById('noEmployeesMessage');
        const submitBtn = document.getElementById('addMembersBtn');

        container.innerHTML = '';
        loader.classList.remove('d-none');
        noDataMsg.classList.add('d-none');
        submitBtn.disabled = true;

        fetch(`/hrms/api/api_manager.php?action=get_available_employees&team_id=<?= $team_id ?>`)
            .then(response => response.json())
            .then(data => {
                loader.classList.add('d-none');
                if (data.success && data.data.length > 0) {
                    data.data.forEach(employee => {
                        const col = document.createElement('div');
                        col.className = 'col-md-6 col-lg-4';
                        col.innerHTML = `
                            <div class="form-check">
                                <input class="form-check-input employee-checkbox" type="checkbox" name="employee_ids[]"
                                    value="${employee.id}" id="add_emp_${employee.id}">
                                <label class="form-check-label" for="add_emp_${employee.id}">
                                    <div class="fw-bold">
                                        ${employee.first_name} ${employee.last_name}
                                    </div>
                                    <small class="text-muted">${employee.designation_name || 'N/A'}</small>
                                </label>
                            </div>
                        `;
                        container.appendChild(col);
                    });

                    // Enable submit button when at least one checkbox is checked
                    $('.employee-checkbox').on('change', function () {
                        const checkedCount = $('.employee-checkbox:checked').length;
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
                        refreshMembersTable();
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                });
        }
    }

    function refreshMembersTable() {
        fetch(`/hrms/api/api_manager.php?action=get_team_details&team_id=<?= $team_id ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.members) {
                    const members = data.data.members;
                    const table = $('#membersTable').DataTable();
                    table.clear();

                    if (members.length > 0) {
                        members.forEach(member => {
                            const avatar = member.first_name.charAt(0).toUpperCase() + member.last_name.charAt(0).toUpperCase();
                            const memberInfo = `
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3">${avatar}</div>
                                    <div>
                                        <div class="fw-bold">${member.first_name} ${member.last_name}</div>
                                        <small class="text-muted">${member.employee_code || 'N/A'}</small>
                                    </div>
                                </div>
                            `;

                            const actions = `
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info" onclick="viewMember(${member.employee_id})" title="View Details">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="removeMember(${member.employee_id})" title="Remove from Team">
                                        <i class="ti ti-user-minus"></i>
                                    </button>
                                </div>
                            `;

                            table.row.add([
                                memberInfo,
                                member.designation_name || 'N/A',
                                member.department_name || 'N/A',
                                member.contact || 'N/A',
                                new Date(member.assigned_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
                                actions
                            ]);
                        });
                    }
                    table.draw();
                }
            })
            .catch(error => {
                console.error('Error refreshing table:', error);
            });
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