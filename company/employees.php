<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Employee Management";

// --- SECURITY & SESSION ---
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) { // Company Admin or HR Manager
    redirect("/hrms/pages/unauthorized.php");
}
$company_id = $_SESSION['company_id'];

// --- INITIAL DATA FETCHING for MODAL DROPDOWNS ---
$all_users = query($mysqli, "SELECT id, username FROM users WHERE company_id = ? ORDER BY username ASC", [$company_id])['data'] ?? [];
$departments = query($mysqli, "SELECT id, name FROM departments WHERE company_id = ? ORDER BY name ASC", [$company_id])['data'] ?? [];
$designations = query($mysqli, "SELECT d.id, d.name FROM designations d JOIN departments dept ON d.department_id = dept.id WHERE dept.company_id = ? ORDER BY d.name ASC", [$company_id])['data'] ?? [];
$shifts = query($mysqli, "SELECT id, name FROM shifts WHERE company_id = ? ORDER BY name ASC", [$company_id])['data'] ?? [];
$assignable_roles = query($mysqli, "SELECT id, name FROM roles WHERE id IN (3, 4) ORDER BY name ASC")['data'] ?? [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-users-cog me-2"></i>Employee Management</h2>
            <button class="btn btn-primary" onclick="validateAndOpenAddModal()">
                <i class="fas fa-plus me-2"></i>Add Employee
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">All Employees</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover dtr-inline nowrap" id="employeesTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Shift</th>
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
<div class="modal fade" id="employeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="employeeForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5><button type="button"
                        class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_edit"><input type="hidden" name="employee_id"
                        id="employeeId" value="0"><input type="hidden" name="user_id" id="hiddenUserId">
                    <div class="row">
                        <div class="col-md-6 mb-3" id="userDropdownContainer"><label for="userIdSelect"
                                class="form-label">User Account <span class="text-danger">*</span></label>
                            <div class="input-group"><select class="form-select" name="user_id_select" id="userIdSelect"
                                    required></select><button class="btn btn-outline-secondary" type="button"
                                    data-bs-toggle="modal" data-bs-target="#createUserModal">Create New</button></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">First Name <span
                                    class="text-danger">*</span></label><input type="text" class="form-control"
                                name="first_name" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Last Name <span
                                    class="text-danger">*</span></label><input type="text" class="form-control"
                                name="last_name" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Department <span
                                    class="text-danger">*</span></label><select class="form-select" name="department_id"
                                required>
                                <option value="">-- Select --</option><?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Designation</label><select
                                class="form-select" name="designation_id">
                                <option value="">-- Select --</option><?php foreach ($designations as $des): ?>
                                    <option value="<?= $des['id'] ?>"><?= htmlspecialchars($des['name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Shift</label><select class="form-select"
                                name="shift_id">
                                <option value="">-- Select --</option><?php foreach ($shifts as $shift): ?>
                                    <option value="<?= $shift['id'] ?>"><?= htmlspecialchars($shift['name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Date of Joining</label><input type="date"
                                class="form-control" name="date_of_joining"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save
                        Employee</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createUserForm">
                <div class="modal-header">
                    <h5 class="modal-title">Create New User Account</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_user">
                    <div class="mb-3"><label class="form-label">Username <span
                                class="text-danger">*</span></label><input type="text" class="form-control"
                            name="username" required></div>
                    <div class="mb-3"><label class="form-label">Email <span class="text-danger">*</span></label><input
                            type="email" class="form-control" name="email" required></div>
                    <div class="mb-3"><label class="form-label">Password <span
                                class="text-danger">*</span></label><input type="password" class="form-control"
                            name="password" required></div>
                    <div class="mb-3"><label class="form-label">Role <span class="text-danger">*</span></label><select
                            class="form-select" name="role_id" required><?php foreach ($assignable_roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
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
    const allUsers = <?= json_encode($all_users) ?>;
    const hasDepartments = <?= !empty($departments) ? 'true' : 'false' ?>;
    const hasDesignations = <?= !empty($designations) ? 'true' : 'false' ?>;
    const hasShifts = <?= !empty($shifts) ? 'true' : 'false' ?>;

    let employeesTable, employeeModal, createUserModal;

    $(function () {
        employeeModal = new bootstrap.Modal('#employeeModal');
        createUserModal = new bootstrap.Modal('#createUserModal');

        employeesTable = $('#employeesTable').DataTable({
            responsive: true,
            processing: true,
            ajax: { url: '/hrms/api/api_employees.php?action=get_employees', dataSrc: 'data' },
            columns: [
                { data: null, render: (d, t, r) => `<strong>${escapeHTML(r.first_name)} ${escapeHTML(r.last_name)}</strong>` },
                { data: 'department_name' }, { data: 'designation_name', defaultContent: 'N/A' },
                { data: 'shift_name', defaultContent: 'N/A' },
                { data: 'status', render: (d) => `<span class="badge text-bg-${d === 'active' ? 'success' : 'danger'}">${capitalize(d)}</span>` },
                { data: null, orderable: false, render: (d, t, r) => `<div class="btn-group"><button class="btn btn-sm btn-outline-primary" onclick='prepareEditModal(${JSON.stringify(r)})'><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-outline-danger" onclick="deleteEmployee(${r.id})"><i class="fas fa-trash"></i></button></div>` }
            ],
            order: [[0, 'asc']]
        });

        $('#employeeForm').on('submit', handleEmployeeFormSubmit);
        $('#createUserForm').on('submit', handleCreateUserFormSubmit);
    });

    function validateAndOpenAddModal() {
        let missing = [];
        if (!hasDepartments) missing.push('departments');
        if (!hasDesignations) missing.push('designations');
        if (!hasShifts) missing.push('shifts');

        if (missing.length > 0) {
            showToast(`Please add ${missing.join(', ')} in the Organization page before adding an employee.`, 'error');
        } else {
            prepareAddModal();
            employeeModal.show();
        }
    }

    function handleEmployeeFormSubmit(e) {
        e.preventDefault();
        const formData = new FormData(this);
        if ($('#userIdSelect').is(':disabled')) {
            formData.set('user_id', $('#hiddenUserId').val());
        } else {
            formData.set('user_id', $('#userIdSelect').val());
        }
        formData.delete('user_id_select');
        fetch('/hrms/api/api_employees.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    employeeModal.hide();
                    employeesTable.ajax.reload();
                } else { showToast(data.message, 'error'); }
            });
    }

    function handleCreateUserFormSubmit(e) {
        e.preventDefault();
        fetch('/hrms/api/api_company_users.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    createUserModal.hide();
                    this.reset();
                    const newUser = data.user;
                    allUsers.push(newUser);
                    const newOption = new Option(newUser.username, newUser.id, true, true);
                    $('#userIdSelect').append(newOption).trigger('change');
                } else { showToast(data.message, 'error'); }
            });
    }

    function prepareAddModal() {
        $('#employeeForm').trigger("reset");
        $('#employeeModalLabel').text('Add Employee');
        $('#employeeId').val('0');

        const existingEmployeeUserIds = employeesTable.rows().data().toArray().map(emp => emp.user_id);
        const unassignedUsers = allUsers.filter(user => !existingEmployeeUserIds.includes(user.id));

        const userDropdown = $('#userIdSelect');
        userDropdown.empty().append('<option value="">-- Select a User --</option>');
        unassignedUsers.forEach(user => {
            userDropdown.append(`<option value="${user.id}">${escapeHTML(user.username)}</option>`);
        });
        userDropdown.prop('disabled', false);
        $('#userDropdownContainer').show();
    }

    function prepareEditModal(employee) {
        $('#employeeForm').trigger("reset");
        $('#employeeModalLabel').text(`Edit ${escapeHTML(employee.first_name)} ${escapeHTML(employee.last_name)}`);

        $('#employeeId').val(employee.id);
        $('form#employeeForm [name="first_name"]').val(employee.first_name);
        $('form#employeeForm [name="last_name"]').val(employee.last_name);
        $('form#employeeForm [name="department_id"]').val(employee.department_id);
        $('form#employeeForm [name="designation_id"]').val(employee.designation_id);
        $('form#employeeForm [name="shift_id"]').val(employee.shift_id);
        $('form#employeeForm [name="date_of_joining"]').val(employee.date_of_joining);
        $('form#employeeForm [name="status"]').val(employee.status);

        const userDropdown = $('#userIdSelect');
        const currentUser = allUsers.find(user => user.id == employee.user_id);
        userDropdown.empty();
        if (currentUser) {
            userDropdown.append(`<option value="${currentUser.id}" selected>${escapeHTML(currentUser.username)}</option>`);
        }
        userDropdown.prop('disabled', true);
        $('#hiddenUserId').val(employee.user_id);
        $('#userDropdownContainer').show();

        employeeModal.show();
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
                    } else { showToast(data.message, 'error'); }
                });
        }
    }
</script>