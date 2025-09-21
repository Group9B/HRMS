<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

$job_id = (int) ($_GET['job_id'] ?? 0);
$job = null;
if ($job_id > 0) {
    $job_result = query($mysqli, "SELECT j.title, c.name as company_name FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.id = ? AND j.status = 'open'", [$job_id]);
    $job = $job_result['data'][0] ?? null;
}
$title = $job ? "Apply for " . htmlspecialchars($job['title']) : "Job Application";

require_once '../components/layout/header.php';
?>
<div class="container my-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="m-0"><?= $title ?></h4>
                    <?php if ($job): ?>
                        <h6 class="text-muted m-0"><?= htmlspecialchars($job['company_name']) ?></h6>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!$job): ?>
                        <div class="alert alert-danger">This job posting is either invalid or no longer open.</div>
                    <?php else: ?>
                        <form id="applicationForm" enctype="multipart/form-data">
                            <input type="hidden" name="job_id" value="<?= $job_id ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">First Name *</label><input type="text"
                                        class="form-control" name="first_name" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Last Name *</label><input type="text"
                                        class="form-control" name="last_name" required></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Email Address *</label><input
                                        type="email" class="form-control" name="email" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Phone Number</label><input type="tel"
                                        class="form-control" name="phone"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Date of Birth</label><input type="date"
                                        class="form-control" name="dob"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Gender</label><select
                                        class="form-select" name="gender">
                                        <option value="">-- Select --</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Upload CV/Resume * <small class="text-muted">(PDF, DOC,
                                        DOCX)</small></label>
                                <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx" required>
                            </div>
                            <div class="d-grid"><button type="submit" class="btn btn-primary">Submit Application</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
        $('#applicationForm').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting...');
            const formData = new FormData(this);
            formData.append('action', 'apply_for_job');

            fetch('/hrms/api/api_public_recruitment.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    if (result.success) {
                        $('.card-body').html(`<div class="alert alert-success">${result.message}</div><p><a href="check_status.php">Click here</a> to check your application status.</p>`);
                    } else {
                        showToast(result.message, 'error');
                    }
                }).finally(() => {
                    btn.prop('disabled', false).text('Submit Application');
                });
        });
    });
</script>