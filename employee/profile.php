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

if (!$isEmployee && !$isManager && !$isHR) {
    redirect("/hrms/pages/unauthorized.php");
}

$viewEmployeeId = null;
$isOwner = false;

if (($isManager || $isHR) && isset($_GET['employee_id'])) {
    $viewEmployeeId = (int) $_GET['employee_id'];
} else {
    $emp_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$userId]);
    if ($emp_result['success'] && !empty($emp_result['data'])) {
        $viewEmployeeId = $emp_result['data'][0]['id'];
        $isOwner = true;
    }
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <?php
        $profile_query = "SELECT e.*, u.username, u.email, u.id as user_id, d.name AS department_name, g.name AS designation_name, s.name AS shift_name, s.start_time, s.end_time
                         FROM employees e
                         JOIN users u ON e.user_id = u.id
                         LEFT JOIN departments d ON e.department_id = d.id
                         LEFT JOIN designations g ON e.designation_id = g.id
                         LEFT JOIN shifts s ON e.shift_id = s.id
                         WHERE e.id = ?";
        $profile_res = query($mysqli, $profile_query, [$viewEmployeeId]);
        $profile = ($profile_res['success'] && !empty($profile_res['data'])) ? $profile_res['data'][0] : null;

        if (!$profile):
            ?>
            <div class="alert alert-warning" role="alert">
                <i class="ti ti-alert-circle me-2"></i>Employee profile not found.
            </div>
        <?php else: ?>
            <!-- Display Mode (Read-Only View) -->
            <div id="viewMode" style="display: block;">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex flex-column flex-md-row align-items-center gap-4 p-4 pt-0">
                                <!-- Avatar -->
                                <div class="avatar-container flex-shrink-0">
                                    <div class="avatar rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 80px; height: 80px; font-size: 32px; font-weight: bold; color: white; flex-shrink: 0;">
                                    </div>
                                </div>
                                <!-- Profile Summary -->
                                <div class="flex-grow-1 text-center text-md-start">
                                    <h4 class="mb-1">
                                        <?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?>
                                    </h4>
                                    <p class="text-muted mb-2">
                                        <i
                                            class="ti ti-briefcase me-2"></i><?= htmlspecialchars($profile['designation_name'] ?? 'N/A') ?>
                                    </p>
                                    <div class="d-flex gap-3 flex-wrap justify-content-center justify-content-md-start">
                                        <span
                                            class="badge <?= $profile['status'] === 'active' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' ?>">
                                            <i class="ti ti-circle-filled me-1"
                                                style="font-size: 0.5rem;\"></i><?= ucfirst($profile['status']) ?>
                                        </span>
                                        <small class="text-muted">
                                            <i class="ti ti-calendar me-1"></i>Joined:
                                            <?= date('M d, Y', strtotime($profile['date_of_joining'])) ?>
                                        </small>
                                    </div>
                                </div>
                                <?php if ($isOwner): ?>
                                    <div class="flex-shrink-0">
                                        <button class="btn btn-primary" id="editProfileBtnHeader">
                                            <i class="ti ti-edit me-2"></i>Edit Profile
                                        </button>
                                    </div>
                                <?php endif; ?>
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
                                        <p class="h6 mb-0"><?= htmlspecialchars($profile['first_name']) ?></p>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-muted small fw-semibold">Last Name</label>
                                        <p class="h6 mb-0"><?= htmlspecialchars($profile['last_name']) ?></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-muted small fw-semibold">Date of Birth</label>
                                        <p class="h6 mb-0">
                                            <?= $profile['dob'] ? date('F d, Y', strtotime($profile['dob'])) : '<span class="text-muted">Not provided</span>' ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-muted small fw-semibold">Gender</label>
                                        <p class="h6 mb-0">
                                            <?= $profile['gender'] ? ucfirst($profile['gender']) : '<span class="text-muted">Not provided</span>' ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-muted small fw-semibold">Contact Number</label>
                                        <p class="h6 mb-0">
                                            <?= $profile['contact'] ? htmlspecialchars($profile['contact']) : '<span class="text-muted">Not provided</span>' ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label text-muted small fw-semibold">Email Address</label>
                                        <p class="h6 mb-0"><a href="mailto:<?= htmlspecialchars($profile['email']) ?>"
                                                class="text-decoration-none">
                                                <?= htmlspecialchars($profile['email']) ?></a>
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label text-muted small fw-semibold">Address</label>
                                    <p class="h6 mb-0">
                                        <?= $profile['address'] ? htmlspecialchars($profile['address']) : '<span class="text-muted">Not provided</span>' ?>
                                    </p>
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
                                    <p class="h6 mb-0"><?= htmlspecialchars($profile['employee_code'] ?? 'N/A') ?></p>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label text-muted small fw-semibold">Username</label>
                                    <p class="h6 mb-0"><?= htmlspecialchars($profile['username']) ?></p>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label text-muted small fw-semibold">Department</label>
                                    <p class="h6 mb-0"><?= htmlspecialchars($profile['department_name'] ?? 'N/A') ?></p>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label text-muted small fw-semibold">Designation</label>
                                    <p class="h6 mb-0"><?= htmlspecialchars($profile['designation_name'] ?? 'N/A') ?></p>
                                </div>
                                <div>
                                    <label class="form-label text-muted small fw-semibold">Shift</label>
                                    <p class="h6 mb-0"><?= htmlspecialchars($profile['shift_name'] ?? 'N/A') ?>
                                        <?php if ($profile['shift_name']): ?>
                                            <br><small
                                                class="text-muted"><?= date('h:i A', strtotime($profile['start_time'])) ?> -
                                                <?= date('h:i A', strtotime($profile['end_time'])) ?></small>
                                        <?php endif; ?>
                                    </p>
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
        <?php endif; ?>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    // Store original profile data for cancel functionality
    let originalProfileData = {
        first_name: <?= json_encode($profile['first_name'] ?? '') ?>,
        last_name: <?= json_encode($profile['last_name'] ?? '') ?>,
        dob: <?= json_encode($profile['dob'] ?? '') ?>,
        gender: <?= json_encode($profile['gender'] ?? '') ?>,
        contact: <?= json_encode($profile['contact'] ?? '') ?>,
        email: <?= json_encode($profile['email'] ?? '') ?>,
        address: <?= json_encode($profile['address'] ?? '') ?>
    };

    $(function () {
        // Initialize avatar on page load
        initializeAvatar({
            id: <?= json_encode($profile['user_id'] ?? null) ?>,
            username: <?= json_encode($profile['username'] ?? '') ?>
        });

        // Populate form fields on page load from PHP data
        populateFormFromPhpData(originalProfileData);

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
            $(this).css('position', 'fixed').css('top', '20px').css('right', '20px').css('z-index', '999');
            $('html, body').animate({ scrollTop: 0 }, 300);
        });

        // Cancel button - toggle back to view mode
        $('#cancelEditBtn').on('click', function () {
            // Reset form to original data
            populateFormFromPhpData(originalProfileData);

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

                        // Get updated form values and update view mode immediately
                        const updatedData = {
                            first_name: $('#editProfileForm [name="first_name"]').val(),
                            last_name: $('#editProfileForm [name="last_name"]').val(),
                            dob: $('#editProfileForm [name="dob"]').val(),
                            gender: $('#editProfileForm [name="gender"]').val(),
                            contact: $('#editProfileForm [name="contact"]').val(),
                            email: $('#editProfileForm [name="email"]').val(),
                            address: $('#editProfileForm [name="address"]').val()
                        };

                        // Update view mode display with new data
                        updateViewModeDisplay(updatedData);

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

    function updateViewModeDisplay(profileData) {
        // Update name in profile header
        $('#viewMode h4').text(profileData.first_name + ' ' + profileData.last_name);

        // Update personal information fields in view mode
        // First Name
        $('#viewMode').find('.col-lg-8').find('.col-md-6').eq(0).find('p').text(profileData.first_name);

        // Last Name
        $('#viewMode').find('.col-lg-8').find('.col-md-6').eq(1).find('p').text(profileData.last_name);

        // Date of Birth
        let dobDisplay = 'Not provided';
        if (profileData.dob) {
            dobDisplay = new Date(profileData.dob).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        }
        $('#viewMode').find('.col-lg-8').find('.col-md-6').eq(2).find('p').html(dobDisplay);

        // Gender
        let genderDisplay = '<span class="text-muted">Not provided</span>';
        if (profileData.gender) {
            genderDisplay = profileData.gender.charAt(0).toUpperCase() + profileData.gender.slice(1);
        }
        $('#viewMode').find('.col-lg-8').find('.col-md-6').eq(3).find('p').html(genderDisplay);

        // Contact Number
        let contactDisplay = '<span class="text-muted">Not provided</span>';
        if (profileData.contact) {
            contactDisplay = profileData.contact;
        }
        $('#viewMode').find('.col-lg-8').find('.col-md-6').eq(4).find('p').html(contactDisplay);

        // Email (in same row as contact)
        $('#viewMode').find('.col-lg-8').find('.col-md-6').eq(5).find('p').html(
            '<a href="mailto:' + profileData.email + '" class="text-decoration-none">' + profileData.email + '</a>'
        );

        // Address (in separate div)
        let addressDisplay = '<span class="text-muted">Not provided</span>';
        if (profileData.address) {
            addressDisplay = profileData.address;
        }
        $('#viewMode').find('.col-lg-8').find('div:last').find('p').html(addressDisplay);
    }

    function populateFormFromPhpData(profileData) {
        $('#editProfileForm [name="first_name"]').val(profileData.first_name);
        $('#editProfileForm [name="last_name"]').val(profileData.last_name);
        $('#editProfileForm [name="dob"]').val(profileData.dob || '');
        $('#editProfileForm [name="gender"]').val(profileData.gender || '');
        $('#editProfileForm [name="contact"]').val(profileData.contact || '');
        $('#editProfileForm [name="email"]').val(profileData.email);
        $('#editProfileForm [name="address"]').val(profileData.address || '');
    }

    function loadProfileData() {
        fetch('/hrms/api/api_emp.php?action=get_profile')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const profile = data.data;

                    // Populate edit form
                    populateFormFromPhpData({
                        first_name: profile.first_name,
                        last_name: profile.last_name,
                        dob: profile.dob,
                        gender: profile.gender,
                        contact: profile.contact,
                        email: profile.email,
                        address: profile.address
                    });

                    // Update view mode display data dynamically
                    // Update name
                    $('#viewMode h4').eq(0).text(profile.first_name + ' ' + profile.last_name);

                    // Update designation
                    $('#viewMode .ti-briefcase').closest('p').html('<i class="ti ti-briefcase me-2"></i>' + (profile.designation_name || 'N/A'));

                    // Update personal information fields
                    $('#viewMode').find('.col-lg-8 .col-md-6').each(function (index) {
                        const fields = ['first_name', 'last_name', 'dob', 'gender', 'contact', 'email', 'address'];
                        if (index < fields.length) {
                            const fieldName = fields[index];
                            let value = profile[fieldName];

                            if (fieldName === 'dob' && value) {
                                value = new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                            } else if (fieldName === 'gender' && value) {
                                value = value.charAt(0).toUpperCase() + value.slice(1);
                            } else if (!value) {
                                value = '<span class="text-muted">Not provided</span>';
                            }

                            if (fieldName === 'email') {
                                $(this).find('p').html('<a href="mailto:' + profile.email + '" class="text-decoration-none">' + profile.email + '</a>');
                            } else if (fieldName === 'address') {
                                $(this).closest('div').find('p').html(value);
                            } else {
                                $(this).find('p').html(value);
                            }
                        }
                    });

                    // Generate and set avatar using main.js utilities with user_id
                    if (typeof generateAvatarData === 'function') {
                        const user = {
                            id: profile.user_id,
                            username: profile.username || (profile.first_name + ' ' + profile.last_name)
                        };

                        initializeAvatar(user);
                    }
                }
            });
    }

    function initializeAvatar(user) {
        if (typeof generateAvatarData === 'function') {
            const avatarData = generateAvatarData(user);
            const avatarEl = document.querySelector('.avatar-container .avatar');

            if (avatarEl && avatarData) {
                avatarEl.textContent = avatarData.initials;
                avatarEl.style.backgroundColor = avatarData.color;
            }
        }
    }
</script>