<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "My Profile";

if (!isLoggedIn() || $_SESSION['role_id'] !== 4) {
    redirect("/hrms/unauthorized.php");
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="h3 mb-0"><i class="fas fa-user-edit me-2"></i>My Profile</h2>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header"><h6 class="m-0 font-weight-bold">Personal Information</h6></div>
                    <div class="card-body">
                        <form id="profileForm">
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">First Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="first_name" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Last Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="last_name" required></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Date of Birth</label><input type="date" class="form-control" name="dob"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Gender</label><select class="form-select" name="gender"><option value="">Select Gender</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>
                            </div>
                            <div class="mb-3"><label class="form-label">Contact Number</label><input type="tel" class="form-control" name="contact"></div>
                            <div class="mb-3"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="3" placeholder="Enter your full address"></textarea></div>
                            <div class="mb-3"><label class="form-label">Emergency Contact</label><input type="tel" class="form-control" name="emergency_contact" placeholder="Emergency contact number"></div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header"><h6 class="m-0 font-weight-bold">Account & Employment Information</h6></div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label">Username</label><input type="text" id="username" class="form-control" readonly></div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" id="email" class="form-control" readonly></div>
                        <div class="mb-3"><label class="form-label">Employee Code</label><input type="text" id="employee_code" class="form-control" readonly></div>
                        <div class="mb-3"><label class="form-label">Department</label><input type="text" id="department" class="form-control" readonly></div>
                        <div class="mb-3"><label class="form-label">Designation</label><input type="text" id="designation" class="form-control" readonly></div>
                        <div class="mb-3"><label class="form-label">Date of Joining</label><input type="text" id="date_of_joining" class="form-control" readonly></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
$(function() {
    loadProfileData();

    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update_personal_info');

        fetch('/hrms/api/api_profile.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            });
    });
});

function loadProfileData() {
    fetch('/hrms/api/api_profile.php?action=get_profile_data')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const profile = data.data;
                // Populate editable form
                $('form#profileForm [name="first_name"]').val(profile.first_name);
                $('form#profileForm [name="last_name"]').val(profile.last_name);
                $('form#profileForm [name="dob"]').val(profile.dob);
                $('form#profileForm [name="gender"]').val(profile.gender);
                $('form#profileForm [name="contact"]').val(profile.contact);
                $('form#profileForm [name="address"]').val(profile.address);
                $('form#profileForm [name="emergency_contact"]').val(profile.emergency_contact);

                // Populate read-only info
                $('#username').val(profile.username);
                $('#email').val(profile.email);
                $('#employee_code').val(profile.employee_code || 'N/A');
                $('#department').val(profile.department_name || 'N/A');
                $('#designation').val(profile.designation_name || 'N/A');
                const joinDate = new Date(profile.date_of_joining);
                $('#date_of_joining').val(joinDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }));

            } else {
                showToast(data.message, 'error');
            }
        });
}
</script>
