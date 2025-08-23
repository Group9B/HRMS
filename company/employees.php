<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Employee Management";

// --- SECURITY & SESSION ---
if (!isLoggedIn() && in_array($_SESSION['role_id'], [2, 3])) {
    redirect("/hrms/unauthorized.php");
}

$company_id = $_SESSION['company_id'];

// --- INITIAL DATA FETCHING for MODAL DROPDOWNS ---

// Fetch users of the company who are not yet employees
$unassigned_users_result = query($mysqli, "SELECT u.id, u.username FROM users u LEFT JOIN employees e ON u.id = e.user_id WHERE u.company_id = ? AND e.id IS NULL ORDER BY u.username ASC", [$company_id]);
$unassigned_users = $unassigned_users_result['success'] ? $unassigned_users_result['data'] : [];

// Fetch departments of the company
$departments_result = query($mysqli, "SELECT id, name FROM departments WHERE company_id = ? ORDER BY name ASC", [$company_id]);
$departments = $departments_result['success'] ? $departments_result['data'] : [];

// Fetch designations of the company
$designations_result = query($mysqli, "SELECT d.id, d.name FROM designations d JOIN departments dept ON d.department_id = dept.id WHERE dept.company_id = ? ORDER BY d.name ASC", [$company_id]);
$designations = $designations_result['success'] ? $designations_result['data'] : [];

// Fetch roles that a Company Admin can assign (HR Manager and Employee)
$assignable_roles_result = query($mysqli, "SELECT id, name FROM roles WHERE id IN (3, 4) ORDER BY name ASC");
$assignable_roles = $assignable_roles_result['success'] ? $assignable_roles_result['data'] : [];


require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-users-cog me-2"></i>Employee Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#employeeModal"
                onclick="prepareAddModal()">
                <i class="fas fa-plus me-2"></i>Add Employee
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">All Employees</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="employeesTable">
                        <thead class="table">
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Date of Joining</th>
                                <th>Status</th>
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

<!-- Employee Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="employeeForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5><button type="button"
                        class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_edit">
                    <input type="hidden" name="employee_id" id="employeeId" value="0">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label">User Account <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select" name="user_id" id="userId" required>
                                    <option value="">-- Select a User --</option>
                                    <?php foreach ($unassigned_users as $user): ?>
                                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal"
                                    data-bs-target="#createUserModal">Create New</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="first_name" class="form-label">First Name <span
                                    class="text-danger">*</span></label><input type="text" class="form-control"
                                name="first_name" required></div>
                        <div class="col-md-6 mb-3"><label for="last_name" class="form-label">Last Name <span
                                    class="text-danger">*</span></label><input type="text" class="form-control"
                                name="last_name" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="department_id" class="form-label">Department <span
                                    class="text-danger">*</span></label><select class="form-select" name="department_id"
                                required>
                                <option value="">-- Select --</option><?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                        <div class="col-md-6 mb-3"><label for="designation_id"
                                class="form-label">Designation</label><select class="form-select" name="designation_id">
                                <option value="">-- Select --</option><?php foreach ($designations as $des): ?>
                                    <option value="<?= $des['id'] ?>"><?= htmlspecialchars($des['name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="date_of_joining" class="form-label">Date of
                                Joining</label><input type="date" class="form-control" name="date_of_joining"></div>
                        <div class="col-md-6 mb-3"><label for="status" class="form-label">Status</label><select
                                class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save
                        Employee</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createUserForm">
                <div class="modal-header">
                    <h5 class="modal-title">Create New User Account</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_user">
                    <div class="mb-3"><label for="new_username" class="form-label">Username <span
                                class="text-danger">*</span></label><input type="text" class="form-control"
                            id="new_username" name="username" required></div>
                    <div class="mb-3"><label for="new_email" class="form-label">Email <span
                                class="text-danger">*</span></label><input type="email" class="form-control"
                            id="new_email" name="email" required></div>
                    <div class="mb-3"><label for="new_password" class="form-label">Password <span
                                class="text-danger">*</span></label><input type="password" class="form-control"
                            id="new_password" name="password" required></div>
                    <!-- New Role Dropdown -->
                    <div class="mb-3"><label for="new_role_id" class="form-label">Role <span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="role_id" id="new_role_id" required>
                            <?php foreach ($assignable_roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create
                        User</button></div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
        let employeesTable;
        const employeeModal = new bootstrap.Modal(document.getElementById('employeeModal'));
        const createUserModal = new bootstrap.Modal(document.getElementById('createUserModal'));

        employeesTable = $('#employeesTable').DataTable({
            processing: true,
            ajax: { url: '/hrms/api/api_employees.php?action=get_employees', dataSrc: 'data' },
            columns: [
                { data: null, render: (data, type, row) => `<strong>${escapeHTML(row.first_name)} ${escapeHTML(row.last_name)}</strong>` },
                { data: 'department_name' },
                { data: 'designation_name', defaultContent: 'N/A' },
                { data: 'date_of_joining', render: (d) => d ? new Date(d).toLocaleDateString() : 'N/A' },
                { data: 'status', render: (d) => `<span class="badge text-bg-${d === 'active' ? 'success' : 'danger'}">${capitalize(d)}</span>` },
                {
                    data: null, orderable: false, render: (data, type, row) => `
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" onclick='prepareEditModal(${JSON.stringify(row)})'><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteEmployee(${row.id})"><i class="fas fa-trash"></i></button>
                </div>`
                }
            ],
            order: [[0, 'asc']]
        });

        $('#employeeForm').on('submit', function (e) {
            e.preventDefault();
            // Re-enable the user dropdown before submitting so its value is included
            $('#userId').prop('disabled', false);
            const formData = new FormData(this);
            fetch('/hrms/api/api_employees.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        employeeModal.hide();
                        employeesTable.ajax.reload();
                    } else {
                        showToast(data.message, 'error');
                    }
                });
        });

        $('#createUserForm').on('submit', function (e) {
            e.preventDefault();
            fetch('/hrms/api/api_company_users.php', { method: 'POST', body: new FormData(this) })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        createUserModal.hide();
                        $('#createUserForm').trigger("reset");

                        const newUser = data.user;
                        const newOption = new Option(newUser.username, newUser.id, true, true);
                        $('#userId').append(newOption).trigger('change');
                    } else {
                        showToast(data.message, 'error');
                    }
                });
        });
    });

    function prepareAddModal() {
        $('#employeeForm').trigger("reset");
        $('#employeeModalLabel').text('Add Employee');
        $('#employeeId').val('0');
        $('#userId').prop('disabled', false).parent().show();
    }

    function prepareEditModal(employee) {
        $('#employeeForm').trigger("reset");
        $('#employeeModalLabel').text(`Edit ${escapeHTML(employee.first_name)} ${escapeHTML(employee.last_name)}`);

        $('#employeeId').val(employee.id);
        $('form#employeeForm [name="first_name"]').val(employee.first_name);
        $('form#employeeForm [name="last_name"]').val(employee.last_name);
        $('form#employeeForm [name="department_id"]').val(employee.department_id);
        $('form#employeeForm [name="designation_id"]').val(employee.designation_id);
        $('form#employeeForm [name="date_of_joining"]').val(employee.date_of_joining);
        $('form#employeeForm [name="status"]').val(employee.status);

        // Hide the user account dropdown for editing
        $('#userId').val(employee.user_id).prop('disabled', true).parent().hide();

        new bootstrap.Modal('#employeeModal').show();
    }

    function deleteEmployee(employeeId) {
        if (confirm('Are you sure you want to delete this employee?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('employee_id', employeeId);
            fetch('/hrms/api/api_employees.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        employeesTable.ajax.reload();
                    } else {
                        showToast(data.message, 'error');
                    }
                });
        }
    }

    function capitalize(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
</script>
