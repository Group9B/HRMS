<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Employee Management";
if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3,])) {
    redirect("/hrms/pages/unauthorized.php");
}
$company_id = $_SESSION['company_id'];
$is_c_admin = $_SESSION['role_id'] === 2;

$all_users = query($mysqli, "SELECT id, username FROM users WHERE company_id = ? AND id != ? ORDER BY username ASC", [$company_id, $_SESSION['user_id']])['data'] ?? [];
$departments = query($mysqli, "SELECT id, name FROM departments WHERE company_id = ? ORDER BY name ASC", [$company_id])['data'] ?? [];
$shifts = query($mysqli, "SELECT id, name, start_time, end_time FROM shifts WHERE company_id = ? ORDER BY name ASC", [$company_id])['data'] ?? [];
$assignable_roles = $is_c_admin ? query($mysqli, "SELECT id, name FROM roles WHERE id IN (3, 4, 6) ORDER BY name ASC")['data'] ?? [] : query($mysqli, "SELECT id, name FROM roles WHERE id IN (3, 6) ORDER BY name ASC")['data'] ?? [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="card shadow-sm">
            <div class="card-header justify-content-between d-flex align-items-center">
                <h6 class="m-0 font-weight-bold">All Employees</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-success" onclick="exportEmployees()">
                        <i class="ti ti-file-spreadsheet me-1"></i>Export to Excel
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="validateAndOpenAddModal()">
                        <i class="ti ti-plus me-2"></i>Add Employee
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered dtr-inline nowrap" id="employeesTable"
                        style="width:100%">
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
                    <div id="emptyState" class="text-center text-muted p-5" style="display: none;">
                        <i class="ti ti-users"
                            style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                        <p>No employees found.</p>
                        <button class="btn btn-primary btn-sm" onclick="validateAndOpenAddModal()">
                            <i class="ti ti-plus me-2"></i>Add First Employee
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                                class="form-select" name="designation_id" id="designationIdSelect" disabled>
                                <option value="">-- Select Department First --</option>
                            </select></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Shift</label><select class="form-select"
                                name="shift_id" id="shiftSelect">
                                <option value="">-- Select --</option><?php foreach ($shifts as $shift): ?>
                                    <option value="<?= $shift['id'] ?>" data-start-time="<?= $shift['start_time'] ?>"
                                        data-end-time="<?= $shift['end_time'] ?>"><?= htmlspecialchars($shift['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Date of Joining</label>
                            <div id="joiningDateContainer">
                                <input type="date" class="form-control" name="date_of_joining" id="dateOfJoining">
                                <small class="form-text text-muted" id="joiningDateHelp">Valid range: yesterday to
                                    upcoming 3 months</small>
                            </div>
                            <div id="joiningDateDisabledContainer" style="display: none;">
                                <input type="date" class="form-control" name="date_of_joining" id="dateOfJoiningLocked"
                                    disabled>
                                <small class="form-text text-muted d-block mt-2">Joined in past - Date is locked</small>
                            </div>
                        </div>
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
                    <div class="mb-3"><label class="form-label">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="username" id="usernameInput" required>
                            <button class="btn btn-outline-secondary" type="button" id="checkUsernameBtn">Check</button>
                        </div>
                        <small id="usernameStatus" class="d-block mt-2"></small>
                    </div>
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

<!-- IoT Credentials Modal -->
<div class="modal fade" id="credentialsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="credentialsModalLabel">
                    <i class="ti ti-fingerprint me-2"></i>IoT Credentials
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="credentialEmployeeId" value="0">

                <!-- Add Credential Form -->
                <div class="card mb-4">
                    <div class="card-header py-2">
                        <h6 class="m-0"><i class="ti ti-plus me-1"></i>Add New Credential</h6>
                    </div>
                    <div class="card-body">
                        <form id="addCredentialForm">
                            <div class="col align-items-end">
                                <div class="row-md-3 mb-2">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="credential_type" id="credentialType" required>
                                        <option value="rfid" selected>RFID Card</option>
                                        <option value="fingerprint">Fingerprint</option>
                                        <option value="face_id">Face ID</option>
                                    </select>
                                </div>
                                <div class="row-md-6 mb-2">
                                    <label class="form-label">Identifier Value <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="identifier_value" id="identifierValue"
                                        placeholder="e.g., 0A8F8005 (tap card on reader to get UID)" required>
                                    <small class="form-text text-muted" id="credentialHelp">
                                        Enter the RFID card UID (hex, e.g. 0A8F8005). Check Serial Monitor for the UID
                                        when tapping a card.
                                    </small>
                                </div>
                                <div class="row-md-3 mb-2">
                                    <button type="submit" class="btn btn-primary w-100" id="addCredentialBtn">
                                        <i class="ti ti-plus me-1"></i>Add
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h6 class="m-0"><i class="ti ti-list me-1"></i>Registered Credentials</h6>
                        <span class="badge bg-secondary" id="credentialCount">0</span>
                    </div>
                    <div class="card-body p-0">
                        <div id="credentialsLoading" class="text-center p-4">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span class="ms-2 text-muted">Loading credentials...</span>
                        </div>
                        <div id="credentialsEmpty" class="text-center text-muted p-4" style="display: none;">
                            <i class="ti ti-id-badge-off"
                                style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                            <p class="mb-0">No credentials registered for this employee.</p>
                            <small>Add an RFID card UID above to enable IoT attendance.</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="credentialsTable" style="display: none;">
                                <thead class="table">
                                    <tr>
                                        <th>Type</th>
                                        <th>Identifier</th>
                                        <th>Registered On</th>
                                        <th style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="credentialsTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    const allUsers = <?= json_encode($all_users) ?>;
    const hasDepartments = <?= !empty($departments) ? 'true' : 'false' ?>;
    const hasShifts = <?= !empty($shifts) ? 'true' : 'false' ?>;

    let employeesTable, employeeModal, createUserModal, credentialsModal;

    $(function () {
        employeeModal = new bootstrap.Modal('#employeeModal');
        createUserModal = new bootstrap.Modal('#createUserModal');
        credentialsModal = new bootstrap.Modal('#credentialsModal');

        // Show skeleton
        SkeletonFactory.showTable('employeesTable', 5, 6);

        employeesTable = $('#employeesTable').DataTable({
            autoWidth: false,
            initComplete: function () {
                SkeletonFactory.hideTable('employeesTable');
                toggleEmptyState();
            },

            responsive: true,
            processing: true,
            ajax: {
                url: '/hrms/api/api_employees.php?action=get_employees',
                dataSrc: 'data',
                complete: function () {
                    toggleEmptyState();
                }
            },
            columns: [
                { data: null, render: (d, t, r) => `<strong>${escapeHTML(r.first_name)} ${escapeHTML(r.last_name)}</strong>` },
                { data: 'department_name' }, { data: 'designation_name', defaultContent: 'N/A' },
                {
                    data: null, render: (d, t, r) => {
                        if (!r.shift_name) return 'N/A';
                        if (!r.start_time || !r.end_time) return escapeHTML(r.shift_name);
                        const shiftTimes = `${formatTime(r.start_time)} → ${formatTime(r.end_time)}`;
                        return `<span data-bs-toggle="tooltip" title="${shiftTimes}">${escapeHTML(r.shift_name)}</span>`;
                    }
                },
                { data: 'status', render: (d) => `<span class="badge bg-${d === 'active' ? 'success-subtle text-success-emphasis' : 'danger-subtle text-danger-emphasis'}">${capitalize(d)}</span>` },
                {
                    data: null,
                    orderable: false,
                    render: (d, t, r) => {
                        const actions = {
                            onManage: () => openCredentialsModal(r),
                            onEdit: () => prepareEditModal(r)
                        };
                        <?php if ($is_c_admin): ?>
                            actions.onDelete = () => deleteEmployee(r.id);
                        <?php endif; ?>

                        return createActionDropdown(actions, {
                            manageTooltip: 'IoT Credentials',
                            manageIcon: 'ti ti-fingerprint',
                            editTooltip: 'Edit',
                            deleteTooltip: 'Delete'
                        });
                    }
                }
            ],
            order: [[0, 'asc']],
            drawCallback: function () {
                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                tooltipTriggerList.forEach(tooltipTriggerEl => {
                    if (!bootstrap.Tooltip.getInstance(tooltipTriggerEl)) {
                        new bootstrap.Tooltip(tooltipTriggerEl);
                    }
                });
            }
        });

        $('#employeeForm').on('submit', handleEmployeeFormSubmit);
        $('#createUserForm').on('submit', handleCreateUserFormSubmit);
        $('#checkUsernameBtn').on('click', checkUsernameAvailability);

        // Add debounced username availability checker for create user modal
        $('#usernameInput').on('input', function () {
            debounceUsernameCheck();
        });

        // Add event listener for department change to populate designations
        $('[name="department_id"]').on('change', function () {
            populateDesignations();
        });
    });

    /**
     * Toggle empty state visibility based on table data
     */
    function toggleEmptyState() {
        // If skeleton is still visible, skip toggling to prevent both showing
        if (document.getElementById('employeesTable-skeleton')) return;

        const rowCount = employeesTable.rows().count();
        const emptyState = $('#emptyState');
        const tableResponsive = $('.table-responsive');
        const datatablesWrapper = $('#employeesTable_wrapper');

        if (rowCount === 0) {
            tableResponsive.find('table').hide();
            datatablesWrapper.find('.dataTables_length, .dataTables_filter, .dataTables_paginate').hide();
            emptyState.show();
        } else {
            tableResponsive.find('table').show();
            datatablesWrapper.find('.dataTables_length, .dataTables_filter, .dataTables_paginate').show();
            emptyState.hide();
        }
    }

    /**
     * Validate the employee form with comprehensive frontend validations
     */
    function validateEmployeeForm() {
        const errors = [];
        const form = $('#employeeForm');

        // Get form values
        const firstName = form.find('[name="first_name"]').val().trim();
        const lastName = form.find('[name="last_name"]').val().trim();
        const userId = form.find('[name="user_id_select"]').val() || $('#hiddenUserId').val();
        const departmentId = form.find('[name="department_id"]').val();
        const designationId = form.find('[name="designation_id"]').val();
        const dateOfJoining = form.find('[name="date_of_joining"]').val();

        // Regex for names (only letters, spaces, hyphens, and apostrophes - no numbers)
        const nameRegex = /^[a-zA-Z\s\-']+$/;

        // Validation rules
        if (!firstName) {
            errors.push('First name is required.');
        } else if (firstName.length < 2) {
            errors.push('First name must be at least 2 characters long.');
        } else if (!nameRegex.test(firstName)) {
            errors.push('First name cannot contain numbers or special characters.');
        }

        if (!lastName) {
            errors.push('Last name is required.');
        } else if (lastName.length < 2) {
            errors.push('Last name must be at least 2 characters long.');
        } else if (!nameRegex.test(lastName)) {
            errors.push('Last name cannot contain numbers or special characters.');
        }

        if (!userId) {
            errors.push('User account is required.');
        }

        if (!departmentId) {
            errors.push('Department is required.');
        }

        if (departmentId && !designationId) {
            errors.push('Designation is required once a department is selected.');
        }

        if (dateOfJoining) {
            const joiningDate = new Date(dateOfJoining);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);

            const maxDate = new Date(today);
            maxDate.setMonth(maxDate.getMonth() + 3);

            const isDateLocked = $('#joiningDateDisabledContainer').is(':visible');

            if (!isDateLocked) {
                if (joiningDate < yesterday) {
                    errors.push('Date of joining cannot be in the past (before yesterday).');
                } else if (joiningDate > maxDate) {
                    errors.push('Date of joining cannot be more than 3 months from today.');
                }
            }
        }

        // Show errors if any
        if (errors.length > 0) {
            showToast(errors.join(' '), 'error');
            return false;
        }

        return true;
    }

    /**
     * Populate designations based on selected department
     */
    function populateDesignations() {
        const departmentId = $('[name="department_id"]').val();
        const designationSelect = $('#designationIdSelect');

        // Reset designation dropdown
        designationSelect.html('<option value="">-- Select Designation --</option>');
        designationSelect.prop('disabled', true);

        if (!departmentId) {
            designationSelect.html('<option value="">-- Select Department First --</option>');
            return;
        }

        // Fetch designations for selected department
        fetch(`/hrms/api/api_employees.php?action=get_designations_by_department&department_id=${departmentId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    designationSelect.prop('disabled', false);
                    data.data.forEach(des => {
                        designationSelect.append(`<option value="${des.id}">${escapeHTML(des.name)}</option>`);
                    });
                } else {
                    designationSelect.html('<option value="">No designations available for this department</option>');
                    designationSelect.prop('disabled', true);
                }
            })
            .catch(error => {
                console.error('Error fetching designations:', error);
                showToast('Failed to load designations. Please try again.', 'error');
                designationSelect.html('<option value="">Error loading designations</option>');
            });
    }

    /**
     * Format time from 24-hour format to 12-hour format
     */
    function formatTime(timeStr) {
        if (!timeStr) return '';
        const [h, m] = timeStr.split(':');
        let hours = parseInt(h, 10);
        const minutes = parseInt(m, 10);
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        return `${hours}:${minutes < 10 ? '0' + minutes : minutes} ${ampm}`;
    }

    /**
     * Initialize tooltips for shift select dropdown - shows shift times on hover
     */
    function initializeShiftTooltips() {
        const shiftSelect = $('#shiftSelect');
        const shiftOptions = shiftSelect.find('option[data-start-time]');

        const shiftTimesMap = {};
        shiftOptions.each(function () {
            const value = $(this).val();
            const startTime = $(this).data('start-time');
            const endTime = $(this).data('end-time');
            if (value && startTime && endTime) {
                shiftTimesMap[value] = `${formatTime(startTime)} → ${formatTime(endTime)}`;
            }
        });

        let tooltipElement;
        shiftSelect.on('focus click', function () {
            const selectedValue = $(this).val();
            const shiftTime = shiftTimesMap[selectedValue];
            if (shiftTime) {
                if (!tooltipElement) {
                    tooltipElement = $('<div class="shift-time-tooltip"></div>')
                        .css({
                            'position': 'absolute',
                            'background': '#222',
                            'color': '#fff',
                            'padding': '6px 10px',
                            'border-radius': '4px',
                            'font-size': '12px',
                            'z-index': '10000',
                            'pointer-events': 'none',
                            'white-space': 'nowrap',
                            'box-shadow': '0 2px 8px rgba(0,0,0,0.15)'
                        })
                        .appendTo('body');
                }
                tooltipElement.text(shiftTime).show();
                const offset = shiftSelect.offset();
                tooltipElement.css({
                    'left': offset.left + 'px',
                    'top': (offset.top + shiftSelect.outerHeight() + 5) + 'px'
                });
            }
        });

        shiftSelect.on('blur change', function () {
            if (tooltipElement) {
                tooltipElement.hide();
            }
        });
    }

    function validateAndOpenAddModal() {
        let missing = [];
        if (!hasDepartments) missing.push('departments');
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

        // Frontend validation
        if (!validateEmployeeForm()) {
            return;
        }

        const formData = new FormData(this);

        // Ensure employee_id is set correctly
        formData.set('employee_id', $('#employeeId').val());

        if ($('#userIdSelect').is(':disabled')) {
            formData.set('user_id', $('#hiddenUserId').val());
        } else {
            formData.set('user_id', $('#userIdSelect').val());
        }
        formData.delete('user_id_select');

        const submitBtn = $(this).find('button[type="submit"]');
        const restoreBtn = UIController.showButtonLoading(submitBtn[0], 'Saving...');

        fetch('/hrms/api/api_employees.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    employeeModal.hide();
                    employeesTable.ajax.reload();
                    toggleEmptyState();
                } else { showToast(data.message, 'error'); }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while saving the employee.', 'error');
            })
            .finally(() => restoreBtn());
    }

    /**
     * Check if username is available
     */
    function checkUsernameAvailability() {
        const username = $('#usernameInput').val().trim();
        const statusElement = $('#usernameStatus');

        if (!username) {
            statusElement.html('<span class="text-warning">Please enter a username</span>');
            return;
        }

        if (username.length < 3) {
            statusElement.html('<span class="text-danger">Username must be at least 3 characters</span>');
            return;
        }

        // Check username availability
        fetch(`/hrms/api/api_company_users.php?action=check_username&username=${encodeURIComponent(username)}`)
            .then(res => res.json())
            .then(data => {
                if (data.available) {
                    statusElement.html('<span class="text-success"><i class="ti ti-check"></i> Username is available</span>');
                } else {
                    statusElement.html('<span class="text-danger"><i class="ti ti-x"></i> Username is already taken</span>');
                }
            })
            .catch(error => {
                console.error('Error checking username:', error);
                statusElement.html('<span class="text-danger">Error checking username availability</span>');
            });
    }

    /**
     * Check password complexity requirements (client-side validation)
     * Must match server-side requirements in includes/functions.php
     */
    function checkPasswordComplexity(password) {
        const errors = [];

        if (password.length < 8) {
            errors.push('Password must be at least 8 characters long');
        }

        if (!/[A-Z]/.test(password)) {
            errors.push('Password must contain at least one uppercase letter');
        }

        if (!/[a-z]/.test(password)) {
            errors.push('Password must contain at least one lowercase letter');
        }

        if (!/[0-9]/.test(password)) {
            errors.push('Password must contain at least one number');
        }

        if (!/[!@#$%^&*]/.test(password)) {
            errors.push('Password must contain at least one special character (!@#$%^&*)');
        }

        return errors;
    }

    /**
     * Debounced username availability checker for create user modal
     */
    let usernameCheckTimeout;
    function debounceUsernameCheck() {
        clearTimeout(usernameCheckTimeout);
        const username = $('#usernameInput').val().trim();

        // Don't check if username is empty or too short
        if (!username || username.length < 3) {
            return;
        }

        // Debounce the check - wait 500ms before making the API call
        usernameCheckTimeout = setTimeout(function () {
            checkUsernameAvailability();
        }, 500);
    }

    function handleCreateUserFormSubmit(e) {
        e.preventDefault();

        // Validate username availability status
        const usernameStatus = $('#usernameStatus').text();
        if (!usernameStatus.includes('available')) {
            showToast('Please verify that username is available before creating user.', 'error');
            return;
        }

        // Validate password complexity
        const password = $('form#createUserForm [name="password"]').val();
        if (!password) {
            showToast('Password is required.', 'error');
            return;
        }

        const passwordErrors = checkPasswordComplexity(password);
        if (passwordErrors.length > 0) {
            showToast('Password requirements not met: ' + passwordErrors.join(', '), 'error');
            return;
        }

        const submitBtn = $(this).find('button[type="submit"]');
        const restoreBtn = UIController.showButtonLoading(submitBtn[0], 'Creating...');

        fetch('/hrms/api/api_company_users.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    createUserModal.hide();
                    employeeModal.show();
                    this.reset();
                    $('#usernameStatus').html('');
                    const newUser = data.user;
                    allUsers.push(newUser);
                    const newOption = new Option(newUser.username, newUser.id, true, true);
                    $('#userIdSelect').append(newOption).trigger('change');
                } else { showToast(data.message, 'error'); }
            })
            .finally(() => restoreBtn());
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

        // Reset designation dropdown
        $('#designationIdSelect').html('<option value="">-- Select Department First --</option>').prop('disabled', true);

        // Clear username status
        $('#usernameStatus').html('');
        $('#usernameInput').val('');

        // Set date constraints for joining date
        setJoiningDateConstraints();

        // Initialize shift tooltips
        initializeShiftTooltips();
    }

    /**
     * Set date constraints for joining date (yesterday to 3 months ahead)
     */
    function setJoiningDateConstraints() {
        const today = new Date();

        // Min date: yesterday
        const minDate = new Date(today);
        minDate.setDate(minDate.getDate() - 1);

        // Max date: 3 months from today
        const maxDate = new Date(today);
        maxDate.setMonth(maxDate.getMonth() + 3);

        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        // Ensure normal date input is shown and locked one is hidden
        $('#joiningDateContainer').show();
        $('#joiningDateDisabledContainer').hide();

        // Set min and max attributes on the input
        const dateInput = $('#dateOfJoining');
        dateInput.attr('min', formatDate(minDate));
        dateInput.attr('max', formatDate(maxDate));
    }

    function prepareEditModal(employee) {
        $('#employeeForm').trigger("reset");
        $('#employeeId').val(employee.id); // Set ID first to ensure it's not missed

        $('#employeeModalLabel').text(`Edit ${escapeHTML(employee.first_name)} ${escapeHTML(employee.last_name)}`);

        $('form#employeeForm [name="first_name"]').val(employee.first_name);
        $('form#employeeForm [name="last_name"]').val(employee.last_name);
        $('form#employeeForm [name="department_id"]').val(employee.department_id);
        $('form#employeeForm [name="shift_id"]').val(employee.shift_id);
        $('form#employeeForm [name="date_of_joining"]').val(employee.date_of_joining);
        $('form#employeeForm [name="status"]').val(employee.status);

        const userDropdown = $('#userIdSelect');
        // For edit mode, hide the user account selector since it shouldn't be changed
        userDropdown.empty();
        userDropdown.append(`<option value="${employee.user_id}" selected>Assigned User</option>`);
        userDropdown.prop('disabled', true);
        $('#hiddenUserId').val(employee.user_id);
        $('#userDropdownContainer').hide();

        // Populate designations for the selected department
        populateDesignations();

        // Set the designation after a short delay to ensure options are loaded
        setTimeout(() => {
            $('form#employeeForm [name="designation_id"]').val(employee.designation_id);
        }, 300);

        // Set date constraints based on whether date is in past
        setEditJoiningDateConstraints(employee.date_of_joining);

        // Initialize shift tooltips
        initializeShiftTooltips();

        employeeModal.show();
    }

    /**
     * Set date constraints for editing - if date is in past, disable field and show actions dropdown
     * If date is future, allow normal editing
     */
    function setEditJoiningDateConstraints(currentDate) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const currentJoiningDate = new Date(currentDate);
        currentJoiningDate.setHours(0, 0, 0, 0);

        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        const maxDate = new Date(today);
        maxDate.setMonth(maxDate.getMonth() + 3);

        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const dateInput = $('#dateOfJoining');
        const dateContainer = $('#joiningDateContainer');
        const disabledContainer = $('#joiningDateDisabledContainer');
        const dateDisplay = $('#dateOfJoiningLocked');

        // If current joining date is in the past (before today)
        if (currentJoiningDate < today) {
            // Hide the normal date input and show the locked version
            dateContainer.hide();
            disabledContainer.show();

            // Set the locked date input with the current value
            dateDisplay.val(currentDate);

            // Remove constraints from hidden input to prevent validation error
            dateInput.removeAttr('min');
            dateInput.removeAttr('max');
        } else {
            // Show normal date input for future dates
            dateContainer.show();
            disabledContainer.hide();
            dateInput.attr('min', formatDate(yesterday));
            dateInput.attr('max', formatDate(maxDate));
        }
    }

    function deleteEmployee(employeeId) {
        showConfirmationModal(
            'Are you sure you want to delete this employee? This action cannot be undone.',
            () => {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('employee_id', employeeId);
                fetch('/hrms/api/api_employees.php', { method: 'POST', body: formData })
                    .then(res => res.json()).then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            employeesTable.ajax.reload();
                            toggleEmptyState();
                        } else { showToast(data.message, 'error'); }
                    });
            },
            'Delete Employee',
            'Delete',
            'btn-danger'
        );
    }

    function exportEmployees() {
        window.open('/hrms/api/api_export_employees.php', '_blank');
    }

    // ═════════════════════════════════════════════════════════════
    //  IoT Credential Management
    // ═════════════════════════════════════════════════════════════

    function openCredentialsModal(employee) {
        const empName = `${escapeHTML(employee.first_name)} ${escapeHTML(employee.last_name)}`;
        $('#credentialsModalLabel').html(`<i class="ti ti-fingerprint me-2"></i>IoT Credentials - ${empName}`);
        $('#credentialEmployeeId').val(employee.id);
        $('#addCredentialForm').trigger('reset');
        loadCredentials(employee.id);
        credentialsModal.show();
    }

    function loadCredentials(employeeId) {
        // Show loading, hide other states
        $('#credentialsLoading').show();
        $('#credentialsEmpty').hide();
        $('#credentialsTable').hide();

        fetch(`/hrms/api/api_employees.php?action=get_credentials&employee_id=${employeeId}`)
            .then(res => res.json())
            .then(data => {
                $('#credentialsLoading').hide();

                if (data.success && data.data && data.data.length > 0) {
                    renderCredentials(data.data);
                    $('#credentialsTable').show();
                    $('#credentialCount').text(data.data.length);
                } else {
                    $('#credentialsEmpty').show();
                    $('#credentialCount').text('0');
                }
            })
            .catch(error => {
                console.error('Error loading credentials:', error);
                $('#credentialsLoading').hide();
                $('#credentialsEmpty').show();
                showToast('Failed to load credentials.', 'error');
            });
    }

    function renderCredentials(credentials) {
        const tbody = $('#credentialsTableBody');
        tbody.empty();

        const typeIcons = {
            'rfid': 'ti ti-id-badge',
            'fingerprint': 'ti ti-fingerprint',
            'face_id': 'ti ti-face-id'
        };
        const typeLabels = {
            'rfid': 'RFID Card',
            'fingerprint': 'Fingerprint',
            'face_id': 'Face ID'
        };

        credentials.forEach(cred => {
            const icon = typeIcons[cred.type] || 'ti ti-key';
            const label = typeLabels[cred.type] || cred.type;
            const date = new Date(cred.created_at).toLocaleDateString('en-IN', {
                day: '2-digit', month: 'short', year: 'numeric'
            });

            tbody.append(`
                <tr>
                    <td><i class="${icon} me-1"></i>${escapeHTML(label)}</td>
                    <td><code class="user-select-all">${escapeHTML(cred.identifier_value)}</code></td>
                    <td><small class="text-muted">${date}</small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCredential(${cred.id})" title="Remove">
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
    }

    // Handle credential type change — update help text
    $('#credentialType').on('change', function () {
        const type = $(this).val();
        const helpTexts = {
            'rfid': 'Enter the RFID card UID (hex, e.g. 0A8F8005). Check Serial Monitor for the UID when tapping a card.',
            'fingerprint': 'Enter the fingerprint template ID from the sensor.',
            'face_id': 'Enter the face recognition ID from the camera module.'
        };
        const placeholders = {
            'rfid': 'e.g., 0A8F8005 (tap card on reader to get UID)',
            'fingerprint': 'e.g., FP_001',
            'face_id': 'e.g., FACE_001'
        };
        $('#credentialHelp').text(helpTexts[type] || '');
        $('#identifierValue').attr('placeholder', placeholders[type] || '');
    });

    // Handle add credential form submit
    $('#addCredentialForm').on('submit', function (e) {
        e.preventDefault();

        const employeeId = $('#credentialEmployeeId').val();
        const type = $('#credentialType').val();
        const identifierValue = $('#identifierValue').val().trim();

        if (!identifierValue) {
            showToast('Identifier value is required.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_credential');
        formData.append('employee_id', employeeId);
        formData.append('credential_type', type);
        formData.append('identifier_value', identifierValue);

        const submitBtn = $('#addCredentialBtn');
        const restoreBtn = UIController.showButtonLoading(submitBtn[0], 'Adding...');

        fetch('/hrms/api/api_employees.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    $('#addCredentialForm').trigger('reset');
                    loadCredentials(employeeId);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error adding credential:', error);
                showToast('Failed to add credential.', 'error');
            })
            .finally(() => restoreBtn());
    });

    function deleteCredential(credentialId) {
        showConfirmationModal(
            'Are you sure you want to remove this credential? The employee will no longer be able to use it for attendance.',
            () => {
                const formData = new FormData();
                formData.append('action', 'delete_credential');
                formData.append('credential_id', credentialId);

                fetch('/hrms/api/api_employees.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            const employeeId = $('#credentialEmployeeId').val();
                            loadCredentials(employeeId);
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting credential:', error);
                        showToast('Failed to remove credential.', 'error');
                    });
            },
            'Remove Credential',
            'Remove',
            'btn-danger'
        );
    }
</script>