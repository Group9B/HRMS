<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

requireAuth();

$title = "Account Settings";
$user_id = $_SESSION['user_id'];

// Get current user details
$userRes = query($mysqli, "SELECT * FROM users WHERE id = ?", [$user_id]);
$user = $userRes['success'] && !empty($userRes['data']) ? $userRes['data'][0] : null;

if (!$user) {
    redirect("/hrms/pages/unauthorized.php");
}

// Get employee info if exists
$employeeRes = query($mysqli, "SELECT * FROM employees WHERE user_id = ?", [$user_id]);
$employee = $employeeRes['success'] && !empty($employeeRes['data']) ? $employeeRes['data'][0] : null;

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>

    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 pt-0">
                        <div class="d-flex align-items-center gap-4">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <div class="avatar rounded-circle"
                                    style="width: 100px; height: 100px; font-size: 40px; font-weight: bold; color: white; display: flex; align-items: center; justify-content: center;"
                                    id="accountAvatar">
                                </div>
                            </div>
                            <!-- User Info -->
                            <div class="flex-grow-1">
                                <h4 class="mb-1"><?= htmlspecialchars($user['username']) ?></h4>
                                <p class="text-muted mb-3">
                                    <i class="ti ti-mail me-1"></i><?= htmlspecialchars($user['email']) ?>
                                </p>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span
                                        class="badge bg-<?= $user['status'] === 'active' ? 'success-subtle text-success-emphasis' : 'danger-subtle text-danger-emphasis' ?>">
                                        <i class="ti ti-circle-filled me-1"
                                            style="font-size: 0.5rem;"></i><?= ucfirst(htmlspecialchars($user['status'])) ?>
                                    </span>
                                    <small class="text-muted">
                                        <i class="ti ti-calendar me-1"></i>Member since
                                        <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Left Column: Account Information -->
            <div class="col-lg-8 mb-4">
                <!-- Account Information Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="ti ti-info-circle me-2"></i>Account Information
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-muted small fw-semibold mb-2">Username</label>
                                    <p class="h6 mb-0"><?= htmlspecialchars($user['username']) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-muted small fw-semibold mb-2">Email Address</label>
                                    <p class="h6 mb-0"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-muted small fw-semibold mb-2">Account Status</label>
                                    <p class="h6 mb-0">
                                        <span
                                            class="badge bg-<?= $user['status'] === 'active' ? 'success-subtle text-success-emphasis' : 'danger-subtle text-danger-emphasis' ?>">
                                            <?= ucfirst(htmlspecialchars($user['status'])) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-muted small fw-semibold mb-2">Member Since</label>
                                    <p class="h6 mb-0"><?= date('M d, Y', strtotime($user['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Profile Link (if applicable) -->
                <?php if ($employee): ?>
                    <div class="card shadow-sm border-info-subtle mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-1 fw-bold d-flex align-items-center">
                                        <i class="ti ti-briefcase me-2 text-info"></i>Employment Details
                                    </h6>
                                    <p class="text-muted small mb-0">View and manage your complete employee profile
                                        including personal information, designation, department, and more.</p>
                                </div>
                                <a href="/hrms/employee/profile.php" class="btn btn-info flex-shrink-0 ms-3">
                                    <i class="ti ti-arrow-right me-1"></i>View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Security & Actions -->
            <div class="col-lg-4 mb-4">
                <!-- Security Settings Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold d-flex align-items-center">
                            <i class="ti ti-shield-lock me-2"></i>Security Settings
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold mb-2">Username</label>
                            <p class="text-muted small mb-3">Update your username to something more personal.</p>
                            <button class="btn btn-outline-info w-100 mb-3" data-bs-toggle="modal"
                                data-bs-target="#changeUsernameModal">
                                <i class="ti ti-edit me-2"></i>Change Username
                            </button>
                        </div>
                        <div>
                            <label class="form-label text-muted small fw-semibold mb-2">Password</label>
                            <p class="text-muted small mb-3">Change your password regularly to keep your account secure.
                            </p>
                            <a href="/hrms/user/change-password.php" class="btn btn-outline-primary w-100">
                                <i class="ti ti-lock-check me-2"></i>Change Password
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips Card -->
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="mb-3 fw-bold d-flex align-items-center">
                            <i class="ti ti-lightbulb me-2 text-warning"></i>Account Tips
                        </h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2 d-flex gap-2">
                                <i class="ti ti-point-filled text-warning flex-shrink-0 mt-1"
                                    style="font-size: 0.5rem;"></i>
                                <span class="text-muted">Keep your password secure and unique</span>
                            </li>
                            <li class="mb-2 d-flex gap-2">
                                <i class="ti ti-point-filled text-warning flex-shrink-0 mt-1"
                                    style="font-size: 0.5rem;"></i>
                                <span class="text-muted">Update password every 3 months</span>
                            </li>
                            <li class="mb-2 d-flex gap-2">
                                <i class="ti ti-point-filled text-warning flex-shrink-0 mt-1"
                                    style="font-size: 0.5rem;"></i>
                                <span class="text-muted">Never share your account details</span>
                            </li>
                            <li class="d-flex gap-2">
                                <i class="ti ti-point-filled text-warning flex-shrink-0 mt-1"
                                    style="font-size: 0.5rem;"></i>
                                <span class="text-muted">Log out after using shared devices</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<!-- Change Username Modal -->
<div class="modal fade" id="changeUsernameModal" tabindex="-1" aria-labelledby="changeUsernameLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeUsernameLabel">
                    <i class="ti ti-edit me-2"></i>Change Username
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Username</label>
                    <input type="text" class="form-control" id="currentUsername" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">New Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newUsername" placeholder="Enter new username">
                    <small class="text-muted d-block mt-2">
                        <i class="ti ti-info-circle me-1"></i>Username must be 3-20 characters, alphanumeric and
                        underscores only
                    </small>
                    <div id="usernameStatus" class="mt-2"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="changeUsernameBtn">
                    <i class="ti ti-device-floppy me-2"></i>Change Username
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let changeUsernameDebounceTimer;
    const currentUserUsername = "<?= htmlspecialchars($user['username']) ?>";
    const currentUserId = <?= json_encode($user['id']) ?>;

    // Initialize modal
    const changeUsernameModal = new bootstrap.Modal(document.getElementById('changeUsernameModal'));
    const changeUsernameModalElement = document.getElementById('changeUsernameModal');

    changeUsernameModalElement.addEventListener('show.bs.modal', function () {
        document.getElementById('currentUsername').value = currentUserUsername;
        document.getElementById('newUsername').value = '';
        document.getElementById('usernameStatus').innerHTML = '';
        document.getElementById('changeUsernameBtn').disabled = false;
    });

    // Debounced username availability check
    document.getElementById('newUsername').addEventListener('input', function () {
        clearTimeout(changeUsernameDebounceTimer);
        const newUsername = this.value.trim();
        const statusDiv = document.getElementById('usernameStatus');

        if (!newUsername) {
            statusDiv.innerHTML = '';
            return;
        }

        // Validation regex: 3-20 characters, alphanumeric and underscores only
        const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
        if (!usernameRegex.test(newUsername)) {
            statusDiv.innerHTML = '<span class="text-danger small"><i class="ti ti-alert-circle me-1"></i>Username must be 3-20 characters, alphanumeric and underscores only</span>';
            return;
        }

        if (newUsername === currentUserUsername) {
            statusDiv.innerHTML = '<span class="text-warning small"><i class="ti ti-alert-circle me-1"></i>New username is the same as current username</span>';
            return;
        }

        // Debounced API call to check availability
        changeUsernameDebounceTimer = setTimeout(() => {
            statusDiv.innerHTML = '<span class="text-muted small"><i class="spinner-border spinner-border-sm me-2"></i>Checking availability...</span>';

            const formData = new FormData();
            formData.append('action', 'check_username');
            formData.append('username', newUsername);
            formData.append('user_id', currentUserId);

            fetch('/hrms/api/api_users.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.available) {
                            statusDiv.innerHTML = '<span class="text-success small"><i class="ti ti-circle-check me-1"></i>Username is available</span>';
                            document.getElementById('changeUsernameBtn').disabled = false;
                        } else {
                            statusDiv.innerHTML = '<span class="text-danger small"><i class="ti ti-alert-circle me-1"></i>Username is already taken</span>';
                            document.getElementById('changeUsernameBtn').disabled = true;
                        }
                    } else {
                        statusDiv.innerHTML = '<span class="text-danger small"><i class="ti ti-alert-circle me-1"></i>Error checking username: ' + data.message + '</span>';
                    }
                })
                .catch(err => {
                    statusDiv.innerHTML = '<span class="text-danger small"><i class="ti ti-alert-circle me-1"></i>Error checking availability</span>';
                });
        }, 500); // 500ms debounce delay
    });

    // Handle username change submission
    document.getElementById('changeUsernameBtn').addEventListener('click', function () {
        const newUsername = document.getElementById('newUsername').value.trim();
        const statusDiv = document.getElementById('usernameStatus');

        if (!newUsername) {
            showToast('Please enter a new username', 'error');
            return;
        }

        const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
        if (!usernameRegex.test(newUsername)) {
            showToast('Invalid username format', 'error');
            return;
        }

        if (newUsername === currentUserUsername) {
            showToast('New username must be different from current username', 'error');
            return;
        }

        // Disable button and show loading
        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Changing...';

        const formData = new FormData();
        formData.append('action', 'update_username');
        formData.append('user_id', currentUserId);
        formData.append('username', newUsername);

        fetch('/hrms/api/api_users.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Username changed successfully!', 'success');

                    // Update the avatar with new username
                    const avatarElement = document.getElementById('accountAvatar');
                    if (avatarElement) {
                        const userData = { id: currentUserId, username: newUsername };
                        const avatarData = generateAvatarData(userData);
                        avatarElement.style.backgroundColor = avatarData.color;
                        avatarElement.textContent = avatarData.initials;
                    }

                    // Update the username display in header
                    document.querySelector('.card-body h4').textContent = newUsername;

                    // Close modal
                    changeUsernameModal.hide();

                    // Reload page after short delay
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('Error changing username', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const avatarElement = document.getElementById('accountAvatar');
        if (avatarElement) {
            const userData = { id: <?= json_encode($user['id']) ?>, username: "<?= htmlspecialchars($user['username']) ?>" };
            const avatarData = generateAvatarData(userData);
            avatarElement.style.backgroundColor = avatarData.color;
            avatarElement.textContent = avatarData.initials;
        }
    });
</script>