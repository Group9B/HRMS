<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Profile";

if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}

$roleId = $_SESSION['role_id'] ?? 0;
$userId = $_SESSION['user_id'];
$isEmployee = ($roleId === 4);
$isManager = ($roleId === 6);
$isHR = ($roleId === 3);
$isOwner = ($roleId === 2);

if (!$isEmployee && !$isManager && !$isHR && !$isOwner) {
    redirect("/hrms/pages/unauthorized.php");
}

// Logic to determine if viewing own profile is moved to JS or simple check
$targetEmpId = isset($_GET['emp_id']) ? (int) $_GET['emp_id'] : null;
$currentUserId = $_SESSION['user_id'];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">

        <!-- Display Mode (Read-Only View) -->
        <div id="viewMode" style="display: block;">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex flex-column flex-md-row align-items-center gap-4 p-4 pt-0">
                            <!-- Avatar -->
                            <div class="avatar-container flex-shrink-0 fs-1">
                                <div class="avatar skeleton skeleton-circle" style="width: 80px; height: 80px;"
                                    id="profileAvatar">
                                </div>
                            </div>
                            <!-- Profile Summary -->
                            <div class="flex-grow-1 text-center text-md-start">
                                <h4 class="mb-1 skeleton skeleton-text" style="width: 200px;" id="profileName"></h4>
                                <p class="text-muted mb-2 skeleton skeleton-text" style="width: 150px;"
                                    id="profileDesignation"></p>
                                <div class="d-flex gap-3 flex-wrap justify-content-center justify-content-md-start">
                                    <span class="badge skeleton skeleton-rect" style="width: 80px;"
                                        id="profileStatus"></span>
                                    <small class="text-muted skeleton skeleton-text" style="width: 120px;"
                                        id="profileJoined"></small>
                                </div>
                            </div>
                            <div class="flex-shrink-0" id="editProfileBtnContainer" style="display: none;">
                                <button class="btn btn-primary" id="editProfileBtnHeader">
                                    <i class="ti ti-edit me-2"></i>Edit Profile
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold d-flex align-items-center">
                                <i class="ti ti-user-circle me-2"></i>Personal Information
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-muted small fw-semibold">First Name</label>
                                    <p class="h6 mb-0 skeleton skeleton-text" id="viewFirstName"></p>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-muted small fw-semibold">Last Name</label>
                                    <p class="h6 mb-0 skeleton skeleton-text" id="viewLastName"></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-muted small fw-semibold">Date of Birth</label>
                                    <p class="h6 mb-0 skeleton skeleton-text" id="viewDob"></p>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-muted small fw-semibold">Gender</label>
                                    <p class="h6 mb-0 skeleton skeleton-text" id="viewGender"></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-muted small fw-semibold">Contact Number</label>
                                    <p class="h6 mb-0 skeleton skeleton-text" id="viewContact"></p>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label text-muted small fw-semibold">Email Address</label>
                                    <p class="h6 mb-0 skeleton skeleton-text" id="viewEmail"></p>
                                </div>
                            </div>
                            <div>
                                <label class="form-label text-muted small fw-semibold">Address</label>
                                <p class="h6 mb-0 skeleton skeleton-text" id="viewAddress"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold d-flex align-items-center">
                                <i class="ti ti-briefcase me-2"></i>Employment Details
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold">Employee Code</label>
                                <p class="h6 mb-0 skeleton skeleton-text" id="viewEmpCode"></p>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold">Username</label>
                                <p class="h6 mb-0 skeleton skeleton-text" id="viewUsername"></p>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold">Department</label>
                                <p class="h6 mb-0 skeleton skeleton-text" id="viewDepartment"></p>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold">Designation</label>
                                <p class="h6 mb-0 skeleton skeleton-text" id="viewDesignation"></p>
                            </div>
                            <div>
                                <label class="form-label text-muted small fw-semibold">Shift</label>
                                <p class="h6 mb-0 skeleton skeleton-text" id="viewShift"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="editMode" style="display: none;">
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold d-flex align-items-center">
                                <i class="ti ti-edit me-2"></i>Edit Personal Information
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <form id="editProfileForm">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold text-muted">First Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" name="first_name"
                                            required>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold text-muted">Last Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" name="last_name"
                                            required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold text-muted">Date of Birth</label>
                                        <input type="date" class="form-control form-control-lg" name="dob">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold text-muted">Gender</label>
                                        <select class="form-select form-select-lg" name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold text-muted">Contact Number</label>
                                        <input type="tel" class="form-control form-control-lg" name="contact">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold text-muted">Email Address</label>
                                        <input type="email" class="form-control form-control-lg" name="email" readonly>
                                        <small class="text-muted d-block mt-2">Contact admin to change email</small>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-muted">Address</label>
                                    <textarea class="form-control" name="address" rows="3"
                                        style="resize: vertical;"></textarea>
                                </div>
                                <div class="d-flex gap-2 pt-3 border-top justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary" id="cancelEditBtn">
                                        <i class="ti ti-x me-2"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <!-- Edit Info Card -->
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="mb-3 fw-bold d-flex align-items-center">
                                <i class="ti ti-info-circle me-2"></i>Editing Tips
                            </h6>
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="ti ti-point-filled text-muted me-2"></i>
                                    <span class="text-muted">Fill in accurate personal information</span>
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-point-filled text-muted me-2"></i>
                                    <span class="text-muted">Email cannot be changed directly</span>
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-point-filled text-muted me-2"></i>
                                    <span class="text-muted">Fields marked * are required</span>
                                </li>
                                <li>
                                    <i class="ti ti-point-filled text-muted me-2"></i>
                                    <span class="text-muted">Changes will be saved immediately</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    let originalProfileData = {};

    $(function () {
        loadProfileData();

        // Real-time validation for first name
        $('#editProfileForm [name="first_name"]').on('input', function () {
            const value = $(this).val();
            if (/\d/.test(value)) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Real-time validation for last name
        $('#editProfileForm [name="last_name"]').on('input', function () {
            const value = $(this).val();
            if (/\d/.test(value)) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Edit button - toggle to edit mode
        $('#editProfileBtnHeader').on('click', function () {
            $('#viewMode').css('display', 'none');
            $('#editMode').css('display', 'block');
            $(this).html('<i class="ti ti-arrow-left me-2"></i>Back to View');
            $(this).css('position', 'fixed').css('top', '100px').css('right', '20px').css('z-index', '999');
            $('html, body').animate({ scrollTop: 0 }, 300);
        });

        // Cancel button - toggle back to view mode
        $('#cancelEditBtn').on('click', function () {
            // Reset form to original data
            populateFormFromData(originalProfileData);

            // Remove any validation error classes
            $('#editProfileForm').find('input, select, textarea').removeClass('is-invalid');

            // Switch back to view mode
            $('#editMode').css('display', 'none');
            $('#viewMode').css('display', 'block');
            $('#editProfileBtnHeader').html('<i class="ti ti-edit me-2"></i>Edit Profile');
            $('#editProfileBtnHeader').css('position', 'relative').css('top', 'auto').css('right', 'auto').css('z-index', 'auto');
        });

        // Form submission
        $('#editProfileForm').on('submit', function (e) {
            e.preventDefault();

            // Validate first name
            const firstName = $('#editProfileForm [name="first_name"]').val().trim();
            if (!firstName) {
                showToast('First name is required.', 'error');
                return;
            }
            if (/\d/.test(firstName)) {
                showToast('First name cannot contain numbers.', 'error');
                return;
            }

            // Validate last name
            const lastName = $('#editProfileForm [name="last_name"]').val().trim();
            if (!lastName) {
                showToast('Last name is required.', 'error');
                return;
            }
            if (/\d/.test(lastName)) {
                showToast('Last name cannot contain numbers.', 'error');
                return;
            }

            // Validate gender
            const gender = $('#editProfileForm [name="gender"]').val();
            if (gender && !['male', 'female', 'other'].includes(gender)) {
                showToast('Gender must be Male, Female, or Other.', 'error');
                return;
            }

            const formData = new FormData(this);
            formData.append('action', 'update_personal_info');

            $(this).find('button[type="submit"]').prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2"></span>Saving...'
            );

            fetch('/hrms/api/api_profile.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');

                        // Reload data to ensure consistency
                        loadProfileData();

                        // Switch back to view mode
                        $('#editMode').css('display', 'none');
                        $('#viewMode').css('display', 'block');
                        $('#editProfileBtnHeader').html('<i class="ti ti-edit me-2"></i>Edit Profile');
                        $('#editProfileBtnHeader').css('position', 'relative').css('top', 'auto').css('right', 'auto').css('z-index', 'auto');
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(err => {
                    showToast('An error occurred while saving.', 'error');
                })
                .finally(() => {
                    $(this).find('button[type="submit"]').prop('disabled', false).html(
                        '<i class="ti ti-device-floppy me-2"></i>Save Changes'
                    );
                });
        });
    });

    function loadProfileData() {
        // Construct API URL based on emp_id param if present
        const urlParams = new URLSearchParams(window.location.search);
        const empId = urlParams.get('emp_id');
        const apiUrl = empId
            ? `/hrms/api/api_profile.php?action=get_profile_data&emp_id=${empId}`
            : `/hrms/api/api_profile.php?action=get_profile_data`;

        fetch(apiUrl)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const profile = data.data;
                    originalProfileData = profile; // Store for cancel functionality

                    // Populate View
                    renderProfileView(profile);

                    // Populate Form
                    populateFormFromData(profile);

                    // Show/Hide Edit Button: Only show if viewing own profile
                    // We compare JS variables or check if user_id matches session (if available in global scope)
                    // Or simpler: The API returns `user_id`. We need current logged-in user id.
                    // We can embed current user id in a JS variable.
                    const currentUserId = <?= json_encode($_SESSION['user_id']) ?>;
                    if (profile.user_id == currentUserId) {
                        $('#editProfileBtnContainer').show();
                    } else {
                        $('#editProfileBtnContainer').hide();
                    }

                    // Avatar
                    initializeAvatar({
                        id: profile.user_id,
                        username: profile.username || (profile.first_name + ' ' + profile.last_name)
                    });
                } else {
                    showToast('Failed to load profile.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error loading profile.', 'error');
            });
    }

    function renderProfileView(profile) {
        // Remove skeleton classes
        $('.skeleton').removeClass('skeleton skeleton-text skeleton-circle skeleton-rect').removeAttr('style');

        // Header
        $('#profileName').text(profile.first_name + ' ' + profile.last_name);
        $('#profileDesignation').html(`<i class="ti ti-briefcase me-2"></i>${profile.designation_name || 'N/A'}`);

        const statusClass = profile.status === 'active' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis';
        $('#profileStatus')
            .removeClass('skeleton skeleton-rect')
            .attr('class', `badge ${statusClass}`)
            .html(`<i class="ti ti-circle-filled me-1" style="font-size: 0.5rem;"></i>${capitalize(profile.status)}`);

        const joinDate = new Date(profile.date_of_joining).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        $('#profileJoined').html(`<i class="ti ti-calendar me-1"></i>Joined: ${joinDate}`);

        // Personal Info
        $('#viewFirstName').text(profile.first_name);
        $('#viewLastName').text(profile.last_name);
        $('#viewDob').html(profile.dob ? new Date(profile.dob).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '<span class="text-muted">Not provided</span>');
        $('#viewGender').html(profile.gender ? capitalize(profile.gender) : '<span class="text-muted">Not provided</span>');
        $('#viewContact').html(profile.contact || '<span class="text-muted">Not provided</span>');
        $('#viewEmail').html(`<a href="mailto:${profile.email}" class="text-decoration-none">${profile.email}</a>`);
        $('#viewAddress').html(profile.address || '<span class="text-muted">Not provided</span>');

        // Employment Info
        $('#viewEmpCode').text(profile.employee_code || 'N/A');
        $('#viewUsername').text(profile.username);
        $('#viewDepartment').text(profile.department_name || 'N/A');
        $('#viewDesignation').text(profile.designation_name || 'N/A');

        let shiftInfo = profile.shift_name || 'N/A';
        if (profile.shift_name) {
            // Helper to format time
            const formatTime = (time) => {
                return new Date('1970-01-01T' + time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            };
            shiftInfo += `<br><small class="text-muted">${formatTime(profile.start_time)} - ${formatTime(profile.end_time)}</small>`;
        }
        $('#viewShift').html(shiftInfo);
    }

    function populateFormFromData(profileData) {
        $('#editProfileForm [name="first_name"]').val(profileData.first_name);
        $('#editProfileForm [name="last_name"]').val(profileData.last_name);
        $('#editProfileForm [name="dob"]').val(profileData.dob || '');
        $('#editProfileForm [name="gender"]').val(profileData.gender || '');
        $('#editProfileForm [name="contact"]').val(profileData.contact || '');
        $('#editProfileForm [name="email"]').val(profileData.email);
        $('#editProfileForm [name="address"]').val(profileData.address || '');
    }

    function capitalize(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function initializeAvatar(user) {
        // Assuming generateAvatarData is globally available in main.js
        if (typeof generateAvatarData === 'function') {
            const avatarData = generateAvatarData(user);
            const avatarEl = document.getElementById('profileAvatar');
            if (avatarEl) {
                avatarEl.textContent = avatarData.initials;
                avatarEl.style.backgroundColor = avatarData.color;
                avatarEl.classList.remove('skeleton', 'skeleton-circle');
                avatarEl.style.width = '80px';
                avatarEl.style.height = '80px';
            }
        }
    }
</script>