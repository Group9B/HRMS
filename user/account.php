<?php
require_once '../config/db.php';
require_once '../config/preferences_config.php';
require_once '../includes/functions.php';
require_once '../includes/preferences.php';

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

// Group preferences by category for tab generation
$preferencesGrouped = [
    'notifications' => [],
    'privacy' => [],
    'deactivation' => []
];

foreach ($PREFERENCES as $key => $config) {
    if (strpos($key, 'notif_') === 0) {
        $preferencesGrouped['notifications'][$key] = $config;
    } elseif (strpos($key, 'privacy_') === 0) {
        $preferencesGrouped['privacy'][$key] = $config;
    } elseif (strpos($key, 'deactivation_') === 0) {
        $preferencesGrouped['deactivation'][$key] = $config;
    }
}

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
                                id="userAvatar">
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1 username-display"><?= htmlspecialchars($user['username']) ?></h4>
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
                                    <button class="btn btn-sm btn-outline-primary" id="editUsernameBtn"
                                        data-bs-toggle="modal" data-bs-target="#usernameModal">
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
                                <small><i class="ti ti-info-circle me-2"></i><strong>HR & Manager Access:</strong> HR
                                    team and your direct manager can always access your information regardless of these
                                    settings.</small>
                            </div>

                            <div class="bg-secondary-subtle p-4 rounded-2">
                                <!-- Privacy Toggles - Generated Dynamically -->
                                <?php
                                $privacyArray = array_values($preferencesGrouped['privacy']);
                                foreach ($privacyArray as $index => $prefConfig):
                                    // Get the key from the config value
                                    $prefKey = '';
                                    foreach ($preferencesGrouped['privacy'] as $k => $c) {
                                        if ($c === $prefConfig) {
                                            $prefKey = $k;
                                            break;
                                        }
                                    }
                                    $elementId = 'privacy' . implode('', array_map('ucfirst', explode('_', substr($prefKey, 8, -8))));
                                    ?>
                                    <div
                                        class="d-flex justify-content-between align-items-start <?= $index < count($privacyArray) - 1 ? 'pb-4 mb-4 border-bottom' : '' ?>">
                                        <div style="flex: 1;">
                                            <h6 class="mb-1 fw-semibold">
                                                <?= htmlspecialchars($prefConfig['label']) ?>
                                            </h6>
                                            <p class="text-muted small mb-0">
                                                <?= htmlspecialchars($prefConfig['description']) ?>
                                            </p>
                                        </div>
                                        <div class="form-check form-switch ms-3 flex-shrink-0">
                                            <input class="form-check-input preference-toggle" type="checkbox"
                                                id="<?= $elementId ?>" data-pref-key="<?= $prefKey ?>" checked>
                                            <label class="form-check-label" for="<?= $elementId ?>"></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Notifications Tab -->
                        <div class="tab-pane fade" id="notificationsContent" role="tabpanel"
                            aria-labelledby="notifications-nav">
                            <h5 class="mb-1">Notifications</h5>
                            <p class="text-muted mb-3">Manage how you receive HR-related notifications</p>

                            <div class="bg-secondary-subtle p-4 rounded-2">
                                <!-- Notification Types Section - Generated Dynamically -->
                                <div class="mb-2">
                                    <h6 class="mb-3 fw-semibold">Notification Preferences</h6>

                                    <?php foreach ($preferencesGrouped['notifications'] as $prefKey => $prefConfig):
                                        $elementId = 'notif' . implode('', array_map('ucfirst', explode('_', substr($prefKey, 6))));
                                        ?>
                                        <div
                                            class="d-flex justify-content-between align-items-start pb-3 mb-3 border-bottom">
                                            <div style="flex: 1;">
                                                <p class="small fw-semibold mb-1">
                                                    <?= htmlspecialchars($prefConfig['label']) ?>
                                                </p>
                                                <p class="text-muted small mb-0">
                                                    <?= htmlspecialchars($prefConfig['description']) ?>
                                                </p>
                                            </div>
                                            <div class="form-check form-switch ms-3 flex-shrink-0">
                                                <input class="form-check-input preference-toggle" type="checkbox"
                                                    id="<?= $elementId ?>" data-pref-key="<?= $prefKey ?>" checked>
                                                <label class="form-check-label" for="<?= $elementId ?>"></label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
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
                                                <div class="list-group-item px-0 py-3 bg-secondary-subtle">
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
                            <p class="text-muted mb-3">Customize how the application looks (stored locally)</p>

                            <div class="bg-secondary-subtle p-4 rounded-2">
                                <div>
                                    <h6 class="mb-3 fw-semibold">Theme</h6>
                                    <p class="text-muted small mb-4">Choose your preferred color scheme</p>

                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-secondary flex-grow-1"
                                            id="lightThemeBtn" data-theme="light" title="Light Theme">
                                            <i class="ti ti-sun me-2"></i>Light
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary flex-grow-1"
                                            id="darkThemeBtn" data-theme="dark" title="Dark Theme">
                                            <i class="ti ti-moon me-2"></i>Dark
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-3"><i class="ti ti-info-circle me-1"></i>Theme
                                        preference is stored locally on your device</small>
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

<!-- Username Modal -->
<div class="modal fade" id="usernameModal" tabindex="-1" aria-labelledby="usernameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="usernameModalLabel">
                    <i class="ti ti-edit me-2"></i>Change Username
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">New Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="usernameInput" placeholder="Enter new username">
                    <small class="text-muted d-block mt-2">
                        <i class="ti ti-info-circle me-1"></i>Username must be 3-20 characters, alphanumeric and
                        underscores only
                    </small>
                    <div id="usernameAvailability" class="mt-2"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveUsernameBtn" disabled>
                    <i class="ti ti-device-floppy me-2"></i>Change Username
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Get current user ID and username from PHP
    const currentUserId = <?= json_encode($user['id']) ?>;
    const currentUserUsername = "<?= htmlspecialchars($user['username']) ?>";
    let changeUsernameDebounceTimer;

    // Preference batch update system - reduces API calls from one per toggle to batched requests
    let pendingPreferences = {};
    let batchUpdateTimer = null;
    const BATCH_UPDATE_DELAY = 500; // ms - delay before sending batch

    // Avatar Initialization and Load Preferences
    document.addEventListener('DOMContentLoaded', function () {
        const avatarElement = document.getElementById('userAvatar');
        if (avatarElement && typeof generateAvatarData !== 'undefined') {
            const avatarData = generateAvatarData({ id: currentUserId, username: currentUserUsername });
            avatarElement.style.backgroundColor = avatarData.color;
            avatarElement.textContent = avatarData.initials;
        }

        // Load all user preferences from API
        loadUserPreferences();
        initializeThemeHandler();
        initializeUsernameModal();
        initializeDeactivationForm();
    });

    /**
     * Load all user preferences from the API
     */
    function loadUserPreferences() {
        fetch('/HRMS/api/api_preferences.php?action=read')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    applyLoadedPreferences(data.data);
                    // Attach listeners only once
                    attachPreferenceEventListeners();
                }
            })
            .catch(err => console.error('Failed to load preferences:', err));
    }

    /**
     * Apply loaded preferences to the UI
     */
    function applyLoadedPreferences(preferences) {
        // Apply all preferences dynamically based on data attributes
        const toggles = document.querySelectorAll('.preference-toggle');
        toggles.forEach(toggle => {
            const prefKey = toggle.dataset.prefKey;
            if (preferences[prefKey] !== undefined) {
                toggle.checked = preferences[prefKey] === '1';
            }
        });
    }

    /**
     * Attach event listeners to all preference toggles (called only once)
     */
    function attachPreferenceEventListeners() {
        // All preference toggles use data-pref-key attribute
        const toggles = document.querySelectorAll('.preference-toggle');
        toggles.forEach(toggle => {
            // Remove any existing listeners by cloning and replacing
            if (!toggle.dataset.listenerAttached) {
                const newToggle = toggle.cloneNode(true);
                toggle.parentNode.replaceChild(newToggle, toggle);

                newToggle.addEventListener('change', function () {
                    const prefKey = this.dataset.prefKey;
                    updatePreference(prefKey, this.checked ? '1' : '0');
                });
                newToggle.dataset.listenerAttached = 'true';
            }
        });
    }

    /**
     * Convert element ID to preference key
     */
    function getPrefKeyFromElementId(id) {
        const mapping = {
            'notifLeaveStatus': 'notif_leave_status',
            'notifAttendance': 'notif_attendance',
            'notifPayslip': 'notif_payslip',
            'notifAnnouncements': 'notif_announcements',
            'notifSystem': 'notif_system',
            'privacyProfile': 'privacy_profile_visible',
            'privacyPhone': 'privacy_phone_visible',
            'privacyEmail': 'privacy_email_visible'
        };
        return mapping[id] || id;
    }

    /**
     * Queue preference update (batched with debouncing)
     * Collects multiple changes and sends one request after 500ms inactivity
     */
    function updatePreference(prefKey, value) {
        // Add to pending batch
        pendingPreferences[prefKey] = value;

        // Clear existing timer
        if (batchUpdateTimer) {
            clearTimeout(batchUpdateTimer);
        }

        // Set new timer for batch update
        batchUpdateTimer = setTimeout(() => {
            if (Object.keys(pendingPreferences).length > 0) {
                sendBatchUpdate();
            }
        }, BATCH_UPDATE_DELAY);
    }

    /**
     * Send all pending preference updates in one API call
     */
    function sendBatchUpdate() {
        if (Object.keys(pendingPreferences).length === 0) return;

        const batch = { ...pendingPreferences };
        pendingPreferences = {}; // Clear pending

        fetch('/HRMS/api/api_preferences.php?action=update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                preferences: batch
            })
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    console.error('Preference batch update failed:', data.message);
                    // Re-queue failed preferences for retry
                    Object.assign(pendingPreferences, batch);
                }
            })
            .catch(err => {
                console.error('Preference batch update error:', err);
                // Re-queue failed preferences for retry
                Object.assign(pendingPreferences, batch);
            });
    }

    /**
     * Initialize theme handler (localStorage only, no database storage)
     */
    function initializeThemeHandler() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        setTheme(savedTheme);

        const lightThemeBtn = document.getElementById('lightThemeBtn');
        const darkThemeBtn = document.getElementById('darkThemeBtn');

        if (lightThemeBtn) {
            lightThemeBtn.addEventListener('click', function () {
                setTheme('light');
            });
        }

        if (darkThemeBtn) {
            darkThemeBtn.addEventListener('click', function () {
                setTheme('dark');
            });
        }
    }

    /**
     * Apply theme to UI and localStorage
     */
    function setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);

        const lightBtn = document.getElementById('lightThemeBtn');
        const darkBtn = document.getElementById('darkThemeBtn');

        if (theme === 'dark') {
            darkBtn.classList.add('active', 'btn-secondary');
            darkBtn.classList.remove('btn-outline-secondary');

            lightBtn.classList.remove('active', 'btn-secondary');
            lightBtn.classList.add('btn-outline-secondary');
        } else {
            lightBtn.classList.add('active', 'btn-secondary');
            lightBtn.classList.remove('btn-outline-secondary');

            darkBtn.classList.remove('active', 'btn-secondary');
            darkBtn.classList.add('btn-outline-secondary');
        }
    }

    /**
     * Initialize username modal and handlers
     */
    function initializeUsernameModal() {
        const usernameModal = new bootstrap.Modal(document.getElementById('usernameModal'));
        const editUsernameBtn = document.getElementById('editUsernameBtn');
        const usernameInput = document.getElementById('usernameInput');
        const saveUsernameBtn = document.getElementById('saveUsernameBtn');
        const usernameModalElement = document.getElementById('usernameModal');

        if (editUsernameBtn) {
            editUsernameBtn.addEventListener('click', function () {
                usernameInput.value = '';
                document.getElementById('usernameAvailability').innerHTML = '';
                saveUsernameBtn.disabled = true;
            });
        }

        // Debounced username availability check
        if (usernameInput) {
            usernameInput.addEventListener('input', function () {
                clearTimeout(changeUsernameDebounceTimer);
                const newUsername = this.value.trim();
                const statusDiv = document.getElementById('usernameAvailability');

                if (!newUsername) {
                    statusDiv.innerHTML = '';
                    saveUsernameBtn.disabled = true;
                    return;
                }

                const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
                if (!usernameRegex.test(newUsername)) {
                    statusDiv.innerHTML = '<span class="text-danger small"><i class="ti ti-alert-circle me-1"></i>Username must be 3-20 characters</span>';
                    saveUsernameBtn.disabled = true;
                    return;
                }

                if (newUsername === currentUserUsername) {
                    statusDiv.innerHTML = '<span class="text-warning small"><i class="ti ti-alert-circle me-1"></i>Same as current username</span>';
                    saveUsernameBtn.disabled = true;
                    return;
                }

                // Debounced check
                changeUsernameDebounceTimer = setTimeout(() => {
                    statusDiv.innerHTML = '<span class="text-muted small"><i class="spinner-border spinner-border-sm me-2"></i>Checking...</span>';

                    const formData = new FormData();
                    formData.append('action', 'check_username');
                    formData.append('username', newUsername);
                    formData.append('user_id', currentUserId);

                    fetch('/hrms/api/api_users.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.available) {
                                statusDiv.innerHTML = '<span class="text-success small"><i class="ti ti-circle-check me-1"></i>Available</span>';
                                saveUsernameBtn.disabled = false;
                            } else {
                                statusDiv.innerHTML = '<span class="text-danger small"><i class="ti ti-alert-circle me-1"></i>Taken</span>';
                                saveUsernameBtn.disabled = true;
                            }
                        })
                        .catch(() => {
                            statusDiv.innerHTML = '<span class="text-danger small">Error checking</span>';
                            saveUsernameBtn.disabled = true;
                        });
                }, 500);
            });
        }

        // Save username
        if (saveUsernameBtn) {
            saveUsernameBtn.addEventListener('click', function () {
                const newUsername = usernameInput.value.trim();
                const btn = this;
                const originalText = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

                const formData = new FormData();
                formData.append('action', 'update_username');
                formData.append('user_id', currentUserId);
                formData.append('username', newUsername);

                fetch('/hrms/api/api_users.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Username updated successfully', 'success');
                            document.querySelector('.username-display').textContent = newUsername;
                            usernameModal.hide();
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showToast(data.message || 'Failed to update username', 'error');
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    })
                    .catch(err => {
                        showToast('Error: ' + err.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
            });
        }
    }

    /**
     * Initialize account deactivation form
     */
    function initializeDeactivationForm() {
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

                if (confirm('Are you sure you want to request account deactivation? This action requires HR approval.')) {
                    const btn = deactivationForm.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

                    fetch('/HRMS/api/api_preferences.php?action=request_deactivation', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            reason: reason,
                            comments: comments
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            btn.disabled = false;
                            btn.innerHTML = originalText;

                            if (data.success) {
                                showToast('Deactivation request submitted. HR will review shortly.', 'success');
                                deactivationForm.reset();
                            } else {
                                showToast(data.message || 'Failed to submit request', 'error');
                            }
                        })
                        .catch(err => {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                            showToast('Error: ' + err.message, 'error');
                        });
                }
            });

            const textarea = document.getElementById('deactivationComments');
            if (textarea) {
                textarea.addEventListener('input', function () {
                    if (this.value.length > 500) {
                        this.value = this.value.substring(0, 500);
                    }
                });
            }
        }
    }
</script>