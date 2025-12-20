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
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="h3 mb-0 text-gray-800"><i class="ti ti-users me-2"></i>User Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal"
                onclick="prepareAddModal()">
                <i class="ti ti-plus me-2"></i>Add User
            </button>
        </div>

        <div class="card shadow-sm">
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
                                <th>Actions</th>
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
                                            class="badge text-bg-<?= $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?= ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td class="d-none d-xl-table-cell">
                                        <?= date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary"
                                                onclick='prepareEditModal(<?= json_encode($user); ?>)'
                                                data-bs-toggle="modal" data-bs-target="#userModal">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="deleteUser(<?= $user['id']; ?>)">
                                                <i class="ti ti-trash"></i>
                                            </button>
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
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="username" class="form-label">Username</label><input
                                type="text" class="form-control" id="username" name="username" required></div>
                        <div class="col-md-6 mb-3"><label for="email" class="form-label">Email</label><input
                                type="email" class="form-control" id="email" name="email" required></div>
                    </div>
                    <div class="mb-3"><label for="password" class="form-label">Password</label><input type="password"
                            class="form-control" id="password" name="password"><small id="passwordHelp"
                            class="form-text text-muted">Leave blank to keep current password.</small></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="company_id" class="form-label">Company</label><select
                                class="form-select" id="company_id" name="company_id">
                                <option value="">-- Select Company --</option><?php foreach ($companies as $company): ?>
                                    <option value="<?= $company['id'] ?>"><?= htmlspecialchars($company['name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                        <div class="col-md-6 mb-3"><label for="role_id" class="form-label">Role</label><select
                                class="form-select" id="role_id" name="role_id" required>
                                <option value="">-- Select Role --</option><?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                    </div>
                    <div class="mb-3"><label for="status" class="form-label">Status</label><select class="form-select"
                            id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select></div>
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

    $(function () {
        usersTable = $('#usersTable').DataTable({ order: [[5, 'desc']] });
        userModal = new bootstrap.Modal(document.getElementById('userModal'));

        $('#userForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('/hrms/api/api_users.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success'); // This now calls the global function
                        userModal.hide();
                        if ($('#formAction').val() === 'add') {
                            addUserRow(data.user);
                        } else {
                            updateUserRow(data.user);
                        }
                    } else {
                        showToast(data.message, 'error'); // This now calls the global function
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

    function prepareAddModal() {
        $('#userForm').trigger("reset");
        $('#userModalLabel').text('Add User');
        $('#formAction').val('add');
        $('#userId').val('0');
        $('#password').prop('required', true);
        $('#passwordHelp').hide();
    }

    function prepareEditModal(user) {
        $('#userForm').trigger("reset");
        $('#userModalLabel').text('Edit User');
        $('#formAction').val('edit');
        $('#userId').val(user.id);
        $('#username').val(user.username);
        $('#email').val(user.email);
        $('#company_id').val(user.company_id);
        $('#role_id').val(user.role_id);
        $('#status').val(user.status);
        $('#password').prop('required', false);
        $('#passwordHelp').show();
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
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
        }
    }



    function createUserRowHTML(user) {
        const joinedDate = new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const statusClass = user.status === 'active' ? 'text-bg-success' : 'text-bg-danger';
        return `
            <td><strong>${escapeHTML(user.username)}</strong></td>
            <td class="d-none d-md-table-cell">${escapeHTML(user.email)}</td>
            <td class="d-none d-lg-table-cell">${escapeHTML(user.company_name) || 'N/A'}</td>
            <td class="d-none d-lg-table-cell">${escapeHTML(user.role_name)}</td>
            <td><span class="badge ${statusClass}">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</span></td>
            <td class="d-none d-xl-table-cell">${joinedDate}</td>
            <td>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick='prepareEditModal(${JSON.stringify(user)})' data-bs-toggle="modal" data-bs-target="#userModal"><i class="ti ti-edit"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteUser(${user.id})"><i class="ti ti-trash"></i></button>
                </div>
            </td>
        `;
    }

    function addUserRow(user) {
        const newRowNode = usersTable.row.add($(`<tr id="user-row-${user.id}">${createUserRowHTML(user)}</tr>`)).draw().node();
        $(newRowNode).addClass('table-success').delay(2000).queue(function (next) { $(this).removeClass('table-success'); next(); });
    }

    function updateUserRow(user) {
        const row = usersTable.row(`#user-row-${user.id}`);
        if (row.length) {
            const node = row.node();
            $(node).html(createUserRowHTML(user));
            usersTable.columns.adjust().draw(false);
            $(node).addClass('table-info').delay(2000).queue(function (next) { $(this).removeClass('table-info'); next(); });
        }
    }
</script>