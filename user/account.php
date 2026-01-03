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

// Get recent activity logs for the user
$activityRes = query($mysqli, "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", [$user_id]);
$activityLogs = $activityRes['success'] && !empty($activityRes['data']) ? $activityRes['data'] : [];

require_once '../components/layout/header.php';
?>

<div class="d-flex" style="min-height: 100vh;">
    <?php require_once '../components/layout/sidebar.php'; ?>

    <main style="flex: 1; overflow-y: auto;">
        <div class="container-fluid">
            <!-- Page Header with Profile -->
            <div class="py-4 py-md-5 border-bottom">
                <div class="container">
                    <div class="d-flex align-items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="avatar rounded-circle"
                                style="width: 80px; height: 80px; font-size: 32px; font-weight: bold; color: white; display: flex; align-items: center; justify-content: center;"
                                id="accountAvatar">
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1"><?= htmlspecialchars($user['username']) ?></h4>
                            <p class="text-muted mb-2">
                                <i class="ti ti-mail me-1"></i><?= htmlspecialchars($user['email']) ?>
                            </p>
                            <small class="text-muted">
                                <i class="ti ti-calendar me-1"></i>Member since
                                <?= date('M d, Y', strtotime($user['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-0" style="min-height: calc(100vh - 200px);">
                <aside class="col-lg-3 border-end">
                    <nav class="navbar navbar-expand-lg navbar-light d-lg-block">
                        <div class="container-fluid px-3 px-lg-4 py-3">
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                data-bs-target="#settingsNav" aria-controls="settingsNav" aria-expanded="false"
                                aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse" id="settingsNav">
                                <ul class="nav nav-pills flex-column w-100" role="tablist">
                                    <li class="nav-item mb-2">
                                        <button class="nav-link text-body active w-100 text-start" id="profile-nav"
                                            data-bs-toggle="tab" data-bs-target="#profileContent" type="button"
                                            role="tab" aria-controls="profileContent" aria-selected="true">
                                            Account
                                        </button>
                                    </li>
                                    <li class="nav-item mb-2">
                                        <button class="nav-link text-body w-100 text-start" id="privacy-nav"
                                            data-bs-toggle="tab" data-bs-target="#privacyContent" type="button"
                                            role="tab" aria-controls="privacyContent" aria-selected="false">
                                            Privacy
                                        </button>
                                    </li>
                                    <li class="nav-item mb-2">
                                        <button class="nav-link text-body w-100 text-start" id="notifications-nav"
                                            data-bs-toggle="tab" data-bs-target="#notificationsContent" type="button"
                                            role="tab" aria-controls="notificationsContent" aria-selected="false">
                                            Notifications
                                        </button>
                                    </li>
                                    <li class="nav-item mb-2">
                                        <button class="nav-link text-body w-100 text-start" id="security-nav"
                                            data-bs-toggle="tab" data-bs-target="#securityContent" type="button"
                                            role="tab" aria-controls="securityContent" aria-selected="false">
                                            Security
                                        </button>
                                    </li>
                                    <li class="nav-item mb-2">
                                        <button class="nav-link text-body w-100 text-start" id="appearance-nav"
                                            data-bs-toggle="tab" data-bs-target="#appearanceContent" type="button"
                                            role="tab" aria-controls="appearanceContent" aria-selected="false">
                                            Appearance
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link text-body w-100 text-start" id="deactivation-nav"
                                            data-bs-toggle="tab" data-bs-target="#deactivationContent" type="button"
                                            role="tab" aria-controls="deactivationContent" aria-selected="false">
                                            Account Deactivation
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </nav>
                </aside>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <div class="tab-content p-4 p-lg-5">
                        <!-- Account Tab -->
                        <div class="tab-pane fade show active" id="profileContent" role="tabpanel"
                            aria-labelledby="profile-nav">
                            <h5 class="mb-1">Account</h5>
                            <p class="text-muted mb-3">Manage your account information and preferences</p>

                            <div class="bg-secondary-subtle p-4 rounded-2">
                                <!-- Username Section -->
                                <div class="d-flex justify-content-between align-items-center pb-4 mb-4 border-bottom">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">Username</h6>
                                        <p class="text-muted small mb-0">Change your display name</p>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#changeUsernameModal">
                                        Edit
                                    </button>
                                </div>

                                <!-- Email Section -->
                                <div class="d-flex justify-content-between align-items-center pb-4 mb-4 border-bottom">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">Email Address</h6>
                                        <p class="text-muted small mb-0"><?= htmlspecialchars($user['email']) ?></p>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">Verified</span>
                                </div>

                                <!-- Account Status Section -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">Account Status</h6>
                                        <p class="text-muted small mb-0">Current status of your account</p>
                                    </div>
                                    <span
                                        class="badge bg-<?= $user['status'] === 'active' ? 'success-subtle text-success-emphasis' : 'danger-subtle text-danger-emphasis' ?>">
                                        <?= ucfirst(htmlspecialchars($user['status'])) ?>
                                    </span>
                                </div>

                                <!-- Employee Profile Link -->
                                <?php if ($employee): ?>
                                    <hr class="my-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 fw-semibold">Employment Profile</h6>
                                            <p class="text-muted small mb-0">View detailed employee information</p>
                                        </div>
                                        <a href="/hrms/employee/profile.php" class="btn btn-sm btn-primary">
                                            View Profile
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Privacy Tab -->
                        <div class="tab-pane fade" id="privacyContent" role="tabpanel" aria-labelledby="privacy-nav">
                            <h5 class="mb-1">Privacy</h5>
                            <p class="text-muted mb-3">Control your profile visibility to colleagues</p>

                            <div class="alert alert-info mb-4" role="alert">
                                <small><i class="ti ti-info-circle me-2"></i>Some information is always visible to HR
                                    and managers for operational reasons, including your attendance, role, and reporting
                                    structure.</small>
                            </div>

                            <div class="bg-secondary-subtle p-4 rounded-2">
                                <!-- Show Profile to Colleagues Toggle -->
                                <div class="d-flex justify-content-between align-items-start pb-4 mb-4 border-bottom">
                                    <div style="flex: 1;">
                                        <h6 class="mb-1 fw-semibold">Show Profile to Colleagues</h6>
                                        <p class="text-muted small mb-0">Allow other employees to view your profile and
                                            contact information</p>
                                    </div>
                                    <div class="form-check form-switch ms-3 flex-shrink-0">
                                        <input class="form-check-input" type="checkbox" id="profileVisibility" checked>
                                        <label class="form-check-label" for="profileVisibility"></label>
                                    </div>
                                </div>

                                <!-- Show Phone Number Internally Toggle -->
                                <div class="d-flex justify-content-between align-items-start pb-4 mb-4 border-bottom">
                                    <div style="flex: 1;">
                                        <h6 class="mb-1 fw-semibold">Show Phone Number Internally</h6>
                                        <p class="text-muted small mb-0">Display your phone number to colleagues within
                                            the organization</p>
                                    </div>
                                    <div class="form-check form-switch ms-3 flex-shrink-0">
                                        <input class="form-check-input" type="checkbox" id="showPhone" checked>
                                        <label class="form-check-label" for="showPhone"></label>
                                    </div>
                                </div>

                                <!-- Show Email Internally Toggle -->
                                <div class="d-flex justify-content-between align-items-start">
                                    <div style="flex: 1;">
                                        <h6 class="mb-1 fw-semibold">Show Email Address Internally</h6>
                                        <p class="text-muted small mb-0">Display your email on your internal profile</p>
                                    </div>
                                    <div class="form-check form-switch ms-3 flex-shrink-0">
                                        <input class="form-check-input" type="checkbox" id="showEmail" checked>
                                        <label class="form-check-label" for="showEmail"></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications Tab -->
                        <div class="tab-pane fade" id="notificationsContent" role="tabpanel"
                            aria-labelledby="notifications-nav">
                            <h5 class="mb-1">Notifications</h5>
                            <p class="text-muted mb-3">Manage how you receive HR-related notifications</p>

                            <div class="bg-secondary-subtle p-4 rounded-2">
                                <!-- Notification Types Section -->
                                <div class="mb-5">
                                    <h6 class="mb-3 fw-semibold">Notification Preferences</h6>

                                    <!-- Leave Request Status -->
                                    <div
                                        class="d-flex justify-content-between align-items-start pb-3 mb-3 border-bottom">
                                        <div style="flex: 1;">
                                            <p class="small fw-semibold mb-1">Leave Request Status Updates</p>
                                            <p class="text-muted small mb-0">Receive notifications when your leave
                                                requests are approved or rejected</p>
                                        </div>
                                        <div class="form-check form-switch ms-3 flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" id="notifLeaveStatus"
                                                checked>
                                            <label class="form-check-label" for="notifLeaveStatus"></label>
                                        </div>
                                    </div>

                                    <!-- Attendance Alerts -->
                                    <div
                                        class="d-flex justify-content-between align-items-start pb-3 mb-3 border-bottom">
                                        <div style="flex: 1;">
                                            <p class="small fw-semibold mb-1">Attendance Alerts</p>
                                            <p class="text-muted small mb-0">Get notified for late check-ins or missing
                                                punch records</p>
                                        </div>
                                        <div class="form-check form-switch ms-3 flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" id="notifAttendance"
                                                checked>
                                            <label class="form-check-label" for="notifAttendance"></label>
                                        </div>
                                    </div>

                                    <!-- Payslip Availability -->
                                    <div
                                        class="d-flex justify-content-between align-items-start pb-3 mb-3 border-bottom">
                                        <div style="flex: 1;">
                                            <p class="small fw-semibold mb-1">Payslip Availability</p>
                                            <p class="text-muted small mb-0">Receive notifications when your payslip is
                                                ready for download</p>
                                        </div>
                                        <div class="form-check form-switch ms-3 flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" id="notifPayslip" checked>
                                            <label class="form-check-label" for="notifPayslip"></label>
                                        </div>
                                    </div>

                                    <!-- Company Announcements -->
                                    <div
                                        class="d-flex justify-content-between align-items-start pb-3 mb-3 border-bottom">
                                        <div style="flex: 1;">
                                            <p class="small fw-semibold mb-1">Company Announcements</p>
                                            <p class="text-muted small mb-0">Be notified of important company-wide
                                                announcements and updates</p>
                                        </div>
                                        <div class="form-check form-switch ms-3 flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" id="notifAnnouncements"
                                                checked>
                                            <label class="form-check-label" for="notifAnnouncements"></label>
                                        </div>
                                    </div>

                                    <!-- System Alerts -->
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div style="flex: 1;">
                                            <p class="small fw-semibold mb-1">System Alerts</p>
                                            <p class="text-muted small mb-0">Important system messages, maintenance
                                                notices, and security alerts</p>
                                        </div>
                                        <div class="form-check form-switch ms-3 flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" id="notifSystem" checked>
                                            <label class="form-check-label" for="notifSystem"></label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notification Channels Section -->
                                <hr class="my-4">

                                <div>
                                    <h6 class="mb-3 fw-semibold">Notification Channels</h6>

                                    <!-- Email Notifications -->
                                    <div
                                        class="d-flex justify-content-between align-items-start pb-3 mb-3 border-bottom">
                                        <div style="flex: 1;">
                                            <p class="small fw-semibold mb-1">Email Notifications</p>
                                            <p class="text-muted small mb-0">Receive notifications via email</p>
                                        </div>
                                        <div class="form-check form-switch ms-3 flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" id="channelEmail" checked>
                                            <label class="form-check-label" for="channelEmail"></label>
                                        </div>
                                    </div>

                                    <!-- In-App Notifications -->
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div style="flex: 1;">
                                            <p class="small fw-semibold mb-1">In-App Notifications</p>
                                            <p class="text-muted small mb-0">See notifications within the application
                                            </p>
                                        </div>
                                        <div class="form-check form-switch ms-3 flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" id="channelInApp" checked>
                                            <label class="form-check-label" for="channelInApp"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="securityContent" role="tabpanel" aria-labelledby="security-nav">
                            <h5 class="mb-1">Security</h5>
                            <p class="text-muted mb-3">Manage your account security and authentication</p>

                            <div class="bg-secondary-subtle p-4 rounded-2">
                                <!-- Change Password Section -->
                                <div class="d-flex justify-content-between align-items-center pb-4 mb-4 border-bottom">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">Password</h6>
                                        <p class="text-muted small mb-0">Update your password regularly for security</p>
                                    </div>
                                    <a href="/hrms/user/change-password.php" class="btn btn-sm btn-primary">
                                        Change Password
                                    </a>
                                </div>

                                <!-- Recent Activity Section -->
                                <div class="pb-4">
                                    <h6 class="mb-3 fw-semibold">Recent Activity</h6>
                                    <?php if (!empty($activityLogs)): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($activityLogs as $log): ?>
                                                <div class="list-group-item px-0 py-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <p class="small fw-semibold mb-1">
                                                                <?= htmlspecialchars($log['action']) ?>
                                                            </p>
                                                            <p class="text-muted small mb-0">
                                                                <i
                                                                    class="ti ti-map-pin me-1"></i><?= htmlspecialchars($log['ip_address'] ?? 'Unknown IP') ?>
                                                            </p>
                                                        </div>
                                                        <div class="text-end flex-shrink-0">
                                                            <small
                                                                class="text-muted d-block"><?= date('M d, Y', strtotime($log['created_at'])) ?></small>
                                                            <small
                                                                class="text-muted d-block"><?= date('H:i A', strtotime($log['created_at'])) ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted small text-center py-4">No activity logs found</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Appearance Tab -->
                        <div class="tab-pane fade" id="appearanceContent" role="tabpanel"
                            aria-labelledby="appearance-nav">
                            <h5 class="mb-1">Appearance</h5>
                            <p class="text-muted mb-3">Customize how the application looks</p>

                            <div class="bg-secondary-subtle p-4 rounded-2">
                                <div>
                                    <h6 class="mb-3 fw-semibold">Theme</h6>
                                    <p class="text-muted small mb-4">Choose your preferred color scheme</p>

                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-secondary flex-grow-1"
                                            id="lightThemeBtn" title="Light Theme">
                                            <i class="ti ti-sun me-2"></i>Light
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary flex-grow-1"
                                            id="darkThemeBtn" title="Dark Theme">
                                            <i class="ti ti-moon me-2"></i>Dark
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Deactivation Tab -->
                        <div class="tab-pane fade" id="deactivationContent" role="tabpanel"
                            aria-labelledby="deactivation-nav">
                            <h5 class="mb-1">Account Deactivation</h5>
                            <p class="text-muted mb-3">Request to deactivate your account</p>

                            <div class="alert alert-danger mb-4" role="alert">
                                <h6 class="mb-2 fw-semibold">Request Account Deactivation</h6>
                                <small>Deactivating your account requires approval from your HR team. Once approved,
                                    your access to the system may be restricted. All data will be retained according to
                                    company policy.</small>
                            </div>

                            <div class="bg-secondary-subtle p-4 rounded-2">
                                <form id="deactivationForm">
                                    <!-- Reason Dropdown -->
                                    <div class="mb-4">
                                        <label for="deactivationReason" class="form-label fw-semibold">Reason for
                                            Deactivation <span class="text-danger">*</span></label>
                                        <select class="form-select" id="deactivationReason" required>
                                            <option value="">Select a reason</option>
                                            <option value="leaving">Leaving organization</option>
                                            <option value="temporary">Temporary break</option>
                                            <option value="privacy">Privacy concerns</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <!-- Additional Comments -->
                                    <div class="mb-4">
                                        <label for="deactivationComments" class="form-label fw-semibold">Additional
                                            Comments (Optional)</label>
                                        <textarea class="form-control" id="deactivationComments" rows="4"
                                            placeholder="Provide any additional information that might help us..."></textarea>
                                        <small class="text-muted d-block mt-2">Character limit: 500</small>
                                    </div>

                                    <!-- Submit Button -->
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="ti ti-alert-triangle me-2"></i>Request Account Deactivation
                                    </button>
                                    <small class="text-muted d-block mt-3">Your request will be reviewed by the HR team.
                                        You'll receive an email notification once a decision has been made.</small>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
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
        // Initialize avatar
        const avatarElement = document.getElementById('accountAvatar');
        if (avatarElement) {
            const userData = { id: <?= json_encode($user['id']) ?>, username: "<?= htmlspecialchars($user['username']) ?>" };
            const avatarData = generateAvatarData(userData);
            avatarElement.style.backgroundColor = avatarData.color;
            avatarElement.textContent = avatarData.initials;
        }

        // Initialize theme settings
        const currentTheme = localStorage.getItem('theme') || 'light';
        updateThemeDisplay(currentTheme);

        // Theme button listeners
        document.getElementById('lightThemeBtn').addEventListener('click', function () {
            setTheme('light');
        });

        document.getElementById('darkThemeBtn').addEventListener('click', function () {
            setTheme('dark');
        });

        function setTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            updateThemeDisplay(theme);
        }

        function updateThemeDisplay(theme) {
            const lightBtn = document.getElementById('lightThemeBtn');
            const darkBtn = document.getElementById('darkThemeBtn');

            if (theme === 'dark') {
                darkBtn.classList.add('active');
                darkBtn.classList.remove('btn-outline-secondary');
                darkBtn.classList.add('btn-secondary');

                lightBtn.classList.remove('active', 'btn-secondary');
                lightBtn.classList.add('btn-outline-secondary');
            } else {
                lightBtn.classList.add('active');
                lightBtn.classList.remove('btn-outline-secondary');
                lightBtn.classList.add('btn-secondary');

                darkBtn.classList.remove('active', 'btn-secondary');
                darkBtn.classList.add('btn-outline-secondary');
            }
        }

        // Handle account deactivation form submission
        const deactivationForm = document.getElementById('deactivationForm');
        if (deactivationForm) {
            deactivationForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const reason = document.getElementById('deactivationReason').value;
                const comments = document.getElementById('deactivationComments').value;

                if (!reason) {
                    showToast('Please select a reason for deactivation', 'error');
                    return;
                }

                // Show confirmation dialog
                if (confirm('Are you sure you want to request account deactivation? This action requires HR approval.')) {
                    // Stub for backend submission
                    console.log('Deactivation request:', { reason, comments, userId: currentUserId });

                    // Simulate API call (stub)
                    const btn = deactivationForm.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

                    // Placeholder: Replace with actual API call
                    setTimeout(() => {
                        showToast('Deactivation request submitted. HR will review and contact you shortly.', 'success');
                        deactivationForm.reset();
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }, 1000);
                }
            });

            // Limit textarea to 500 characters
            const textarea = document.getElementById('deactivationComments');
            if (textarea) {
                textarea.addEventListener('input', function () {
                    if (this.value.length > 500) {
                        this.value = this.value.substring(0, 500);
                    }
                });
            }
        }
    });
</script>