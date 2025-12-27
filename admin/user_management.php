<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "User Management";

if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}

if ($_SESSION['role_id'] !== 1) {
    redirect("/hrms/pages/unauthorized.php");
}

// --- INITIAL DATA FETCHING ---

// Fetch all users with their company and role names for the initial page load
$users_result = query($mysqli, "
    SELECT u.*, c.name as company_name, r.name as role_name 
    FROM users u
    LEFT JOIN companies c ON u.company_id = c.id
    LEFT JOIN roles r ON u.role_id = r.id
    ORDER BY u.created_at DESC
");
$users = $users_result['success'] ? $users_result['data'] : [];

// Fetch companies and roles for the modal dropdowns
$companies_result = query($mysqli, "SELECT id, name FROM companies ORDER BY name ASC");
$companies = $companies_result['success'] ? $companies_result['data'] : [];

$roles_result = query($mysqli, "SELECT id, name FROM roles ORDER BY name ASC");
$roles = $roles_result['success'] ? $roles_result['data'] : [];


require_once '../components/layout/header.php';
?>
<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Users</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal"
                    onclick="prepareAddModal()">
                    <i class="ti ti-plus me-2"></i>Add User
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive overflow-auto" style="max-height: 60vh;">
                    <table class="table table-hover table-sm align-middle" id="usersTable">
                        <thead class="">
                            <tr>
                                <th>Username</th>
                                <th class="d-none d-md-table-cell">Email</th>
                                <th class="d-none d-lg-table-cell">Company</th>
                                <th class="d-none d-lg-table-cell">Role</th>
                                <th>Status</th>
                                <th class="d-none d-xl-table-cell">Joined On</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr id="user-row-<?= $user['id']; ?>">
                                    <td><strong><?= htmlspecialchars($user['username']); ?></strong></td>
                                    <td class="d-none d-md-table-cell"><?= htmlspecialchars($user['email']); ?></td>
                                    <td class="d-none d-lg-table-cell">
                                        <?= htmlspecialchars($user['company_name'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell"><?= htmlspecialchars($user['role_name']); ?></td>
                                    <td>
                                        <span
                                            class="badge bg-<?= $user['status'] === 'active' ? 'success-subtle text-success-emphasis' : 'danger-subtle text-danger-emphasis'; ?>">
                                            <?= ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td class="d-none d-xl-table-cell">
                                        <?= date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="action-dropdown text-end" data-user='<?= json_encode($user); ?>'>
                                            <!-- Dropdown will be inserted by JavaScript -->
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="userForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="user_id" id="userId" value="0">

                    <!-- Validation errors -->
                    <div id="validationErrors" class="alert alert-danger d-none" role="alert"></div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="username" name="username" required
                                    maxlength="50" placeholder="Enter username">
                                <span class="input-group-text" id="usernameStatus">
                                    <i class="ti ti-circle-check text-success" style="display: none;"
                                        id="usernameAvailableIcon"></i>
                                    <i class="ti ti-circle-x text-danger" style="display: none;"
                                        id="usernameUnavailableIcon"></i>
                                    <span class="spinner-border spinner-border-sm text-primary" role="status"
                                        style="display: none;" id="usernameCheckingSpinner" aria-hidden="true"></span>
                                </span>
                            </div>
                            <small class="form-text text-muted d-block">3-50 characters, alphanumeric and
                                underscore</small>
                            <small id="usernameAvailabilityMessage" class="d-block"></small>
                            <div class="invalid-feedback" id="usernameError"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required maxlength="100"
                                placeholder="user@example.com">
                            <small class="form-text text-muted">Valid email format required</small>
                            <div class="invalid-feedback" id="emailError"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" maxlength="255">
                        <small id="passwordHelp" class="form-text text-muted">Leave blank to keep current password. Min
                            8 characters with uppercase, lowercase, number, and special char.</small>
                        <div class="invalid-feedback" id="passwordError"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_id" class="form-label">Company</label>
                            <select class="form-select" id="company_id" name="company_id">
                                <option value="">-- Select Company --</option><?php foreach ($companies as $company): ?>
                                    <option value="<?= $company['id'] ?>"><?= htmlspecialchars($company['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role_id" name="role_id" required>
                                <option value="">-- Select Role --</option><?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once '../components/layout/footer.php'; ?>

<script>

    let userModal;
    let usersTable;
    let usernameCheckTimeout;
    let currentEditingUserId = 0;

    $(function () {
        usersTable = $('#usersTable').DataTable({
            order: [[5, 'desc']],
            columnDefs: [
                {
                    targets: 6, // Actions column (0-indexed)
                    sortable: false,
                    searchable: false
                }
            ]
        });
        userModal = new bootstrap.Modal(document.getElementById('userModal'));

        // Initialize action dropdowns for existing rows
        initializeActionDropdowns();

        // Add debounced username availability checker
        $('#username').on('input', function () {
            debounceUsernameCheck();
        });

        $('#userForm').on('submit', function (e) {
            e.preventDefault();

            // Clear previous validation errors
            $('#validationErrors').addClass('d-none').html('');
            $('#userForm').removeClass('was-validated');

            // Validate form
            if (!validateUserForm()) {
                return;
            }

            const formData = new FormData(this);
            fetch('/hrms/api/api_users.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        userModal.hide();
                        if ($('#formAction').val() === 'add') {
                            addUserRow(data.user);
                        } else {
                            updateUserRow(data.user);
                        }
                    } else {
                        // Display backend validation errors
                        if (data.errors && typeof data.errors === 'object') {
                            displayValidationErrors(data.errors);
                        } else {
                            showToast(data.message, 'error');
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

    function initializeActionDropdowns() {
        $('.action-dropdown').each(function () {
            const user = JSON.parse($(this).attr('data-user'));
            const dropdownHTML = createActionDropdown(
                {
                    onEdit: function () {
                        prepareEditModal(user);
                        userModal.show();
                    },
                    onDelete: function () {
                        deleteUser(user.id);
                    }
                },
                {
                    editTooltip: 'Edit User',
                    deleteTooltip: 'Delete User'
                }
            );
            $(this).html(dropdownHTML);
        });
    }

    function prepareAddModal() {
        $('#userForm').trigger("reset");
        $('#userModalLabel').text('Add User');
        $('#formAction').val('add');
        $('#userId').val('0');
        currentEditingUserId = 0;
        $('#password').prop('required', true);
        $('#passwordHelp').hide();
        clearUsernameStatus();
    }

    function prepareEditModal(user) {
        $('#userForm').trigger("reset");
        $('#userModalLabel').text('Edit User');
        $('#formAction').val('edit');
        $('#userId').val(user.id);
        currentEditingUserId = user.id;
        $('#username').val(user.username);
        $('#email').val(user.email);
        $('#company_id').val(user.company_id);
        $('#role_id').val(user.role_id);
        $('#status').val(user.status);
        $('#password').prop('required', false);
        $('#passwordHelp').show();
        clearUsernameStatus();
    }

    function deleteUser(userId) {
        showConfirmationModal(
            'Are you sure you want to delete this user? This action cannot be undone.',
            function () {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('user_id', userId);
                fetch('/hrms/api/api_users.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            usersTable.row(`#user-row-${userId}`).remove().draw();
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            },
            'Delete User',
            'Delete',
            'btn-danger'
        );
    }



    function createUserRowHTML(user) {
        const joinedDate = new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const statusClass = user.status === 'active' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis';
        return `
            <td><strong>${escapeHTML(user.username)}</strong></td>
            <td class="d-none d-md-table-cell">${escapeHTML(user.email)}</td>
            <td class="d-none d-lg-table-cell">${escapeHTML(user.company_name) || 'N/A'}</td>
            <td class="d-none d-lg-table-cell">${escapeHTML(user.role_name)}</td>
            <td><span class="badge ${statusClass}">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</span></td>
            <td class="d-none d-xl-table-cell">${joinedDate}</td>
            <td>
                <div class="action-dropdown text-end" data-user='${JSON.stringify(user)}'>
                </div>
            </td>
        `;
    }

    function addUserRow(user) {
        const newRowNode = usersTable.row.add($(`<tr id="user-row-${user.id}">${createUserRowHTML(user)}</tr>`)).draw().node();
        initializeActionDropdownForRow($(newRowNode).find('.action-dropdown'));
        $(newRowNode).addClass('table-success').delay(2000).queue(function (next) { $(this).removeClass('table-success'); next(); });
    }

    function updateUserRow(user) {
        const row = usersTable.row(`#user-row-${user.id}`);
        if (row.length) {
            const node = row.node();
            $(node).html(createUserRowHTML(user));
            initializeActionDropdownForRow($(node).find('.action-dropdown'));
            usersTable.columns.adjust().draw(false);
            $(node).addClass('table-info').delay(2000).queue(function (next) { $(this).removeClass('table-info'); next(); });
        }
    }

    function initializeActionDropdownForRow(dropdownElement) {
        const user = JSON.parse(dropdownElement.attr('data-user'));
        const dropdownHTML = createActionDropdown(
            {
                onEdit: function () {
                    prepareEditModal(user);
                    userModal.show();
                },
                onDelete: function () {
                    deleteUser(user.id);
                }
            },
            {
                editTooltip: 'Edit User',
                deleteTooltip: 'Delete User'
            }
        );
        dropdownElement.html(dropdownHTML);
    }

    // --- VALIDATION FUNCTIONS ---

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

    function validateUserForm() {
        const errors = {};
        const username = $('#username').val().trim();
        const email = $('#email').val().trim();
        const password = $('#password').val();
        const roleId = $('#role_id').val();

        // Username validation
        if (!username) {
            errors.username = 'Username is required.';
        } else if (username.length < 3) {
            errors.username = 'Username must be at least 3 characters.';
        } else if (username.length > 50) {
            errors.username = 'Username must not exceed 50 characters.';
        } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            errors.username = 'Username can only contain letters, numbers, and underscores.';
        } else if ($('#username').hasClass('is-invalid')) {
            // Username is marked as unavailable
            errors.username = 'Username is already taken.';
        }

        // Email validation
        if (!email) {
            errors.email = 'Email is required.';
        } else if (!isValidEmail(email)) {
            errors.email = 'Please enter a valid email address.';
        } else if (email.length > 100) {
            errors.email = 'Email must not exceed 100 characters.';
        }

        // Password validation (only if not empty)
        if (password) {
            const passwordErrors = checkPasswordComplexity(password);
            if (passwordErrors.length > 0) {
                errors.password = passwordErrors.join(' ');
            } else if (password.length > 255) {
                errors.password = 'Password must not exceed 255 characters.';
            }
        }

        // Role validation
        if (!roleId) {
            errors.role_id = 'Role is required.';
        }

        if (Object.keys(errors).length > 0) {
            displayValidationErrors(errors);
            return false;
        }

        return true;
    }

    function displayValidationErrors(errors) {
        const errorContainer = $('#validationErrors');
        let errorHTML = '<strong>Please fix the following errors:</strong><ul class="mb-0">';

        Object.keys(errors).forEach(field => {
            errorHTML += `<li>${escapeHTML(errors[field])}</li>`;

            // Also add to individual field feedback
            if (field === 'username') {
                $('#usernameError').text(errors[field]);
                $('#username').addClass('is-invalid');
            } else if (field === 'email') {
                $('#emailError').text(errors[field]);
                $('#email').addClass('is-invalid');
            } else if (field === 'password') {
                $('#passwordError').text(errors[field]);
                $('#password').addClass('is-invalid');
            }
        });

        errorHTML += '</ul>';
        errorContainer.html(errorHTML).removeClass('d-none');

        // Scroll to error
        errorContainer[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function clearValidationErrors() {
        $('#validationErrors').addClass('d-none').html('');
        $('#usernameError, #emailError, #passwordError').text('');
        $('#username, #email, #password').removeClass('is-invalid');
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Clear errors when modal opens
    $('#userModal').on('show.bs.modal', function () {
        clearValidationErrors();
    });

    // --- USERNAME AVAILABILITY CHECKING ---

    function debounceUsernameCheck() {
        clearTimeout(usernameCheckTimeout);
        const username = $('#username').val().trim();

        // Clear previous status
        clearUsernameStatus();

        // Don't check if username is empty or too short
        if (!username || username.length < 3) {
            return;
        }

        // Show checking indicator
        $('#usernameCheckingSpinner').show();

        // Debounce the check - wait 500ms before making the API call
        usernameCheckTimeout = setTimeout(function () {
            checkUsernameAvailability(username);
        }, 500);
    }

    function checkUsernameAvailability(username) {
        const formData = new FormData();
        formData.append('action', 'check_username');
        formData.append('username', username);
        formData.append('user_id', currentEditingUserId);

        fetch('/hrms/api/api_users.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                $('#usernameCheckingSpinner').hide();

                if (data.success) {
                    if (data.available) {
                        // Username is available
                        $('#username').removeClass('is-invalid').addClass('is-valid');
                        $('#usernameAvailableIcon').show();
                        $('#usernameUnavailableIcon').hide();
                        $('#usernameAvailabilityMessage').html('<small class="text-success"><i class="ti ti-check me-1"></i>Username is available</small>');
                    } else {
                        // Username is taken
                        $('#username').removeClass('is-valid').addClass('is-invalid');
                        $('#usernameAvailableIcon').hide();
                        $('#usernameUnavailableIcon').show();
                        $('#usernameAvailabilityMessage').html('<small class="text-danger"><i class="ti ti-x me-1"></i>Username is already taken</small>');
                    }
                } else {
                    // Error checking availability
                    $('#usernameCheckingSpinner').hide();
                    $('#usernameAvailabilityMessage').html(`<small class="text-warning"><i class="ti ti-alert-triangle me-1"></i>${escapeHTML(data.message)}</small>`);
                }
            })
            .catch(error => {
                console.error('Error checking username:', error);
                $('#usernameCheckingSpinner').hide();
                $('#usernameAvailabilityMessage').html('<small class="text-danger"><i class="ti ti-x me-1"></i>Error checking username availability</small>');
            });
    }

    function clearUsernameStatus() {
        $('#username').removeClass('is-valid is-invalid');
        $('#usernameAvailableIcon, #usernameUnavailableIcon, #usernameCheckingSpinner').hide();
        $('#usernameAvailabilityMessage').html('');
    }
</script>