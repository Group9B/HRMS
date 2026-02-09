<?php
$title = "Careers - StaffSync HRMS";
require_once '../components/layout/header.php';

// Fetch open jobs
$jobs_result = query($mysqli, "SELECT j.id, j.title, j.description, j.employment_type, j.location, j.openings, j.posted_at, d.name as department_name, c.name as company_name 
    FROM jobs j 
    LEFT JOIN departments d ON j.department_id = d.id 
    JOIN companies c ON j.company_id = c.id 
    WHERE j.status = 'open' 
    ORDER BY j.posted_at DESC");
$jobs = $jobs_result['data'] ?? [];
?>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .letter-spacing-2 {
        letter-spacing: 2px;
    }
</style>

<!-- Hero Section -->
<section class="py-5 bg-primary bg-gradient text-white text-center">
    <div class="container py-4">
        <h1 class="display-4 fw-bold mb-3">Join Our Team</h1>
        <p class="lead opacity-75 mb-0">
            Discover exciting career opportunities and grow with us. We're always looking for talented individuals.
        </p>
    </div>
</section>

<!-- Check Status Section -->
<section class="py-4 bg-body border-bottom">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="mb-3 fw-bold text-body"><i class="ti ti-search me-2 text-primary"></i>Check
                            Application Status</h5>
                        <form id="statusCheckForm" class="d-flex gap-2">
                            <input type="email" class="form-control" id="statusEmail"
                                placeholder="Enter your email address" required>
                            <button type="submit" class="btn btn-primary px-4 flex-shrink-0">Check</button>
                        </form>
                        <div id="statusResults" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Job Listings Section -->
<section class="py-5 bg-body-tertiary" id="jobs">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-primary fw-bold text-uppercase letter-spacing-2">Open Positions</h6>
            <h2 class="fw-bold text-body-emphasis">Current Opportunities</h2>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="text-center py-5">
                <i class="ti ti-briefcase-off display-1 text-secondary opacity-50 mb-3"></i>
                <h4 class="text-secondary">No Open Positions</h4>
                <p class="text-muted">There are no open positions at the moment. Please check back later!</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($jobs as $job): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 hover-shadow transition-all bg-body">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                                        <i class="ti ti-briefcase fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold mb-1 text-body"><?= htmlspecialchars($job['title']) ?></h5>
                                        <small class="text-muted"><?= htmlspecialchars($job['company_name']) ?></small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <?php if ($job['department_name']): ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary me-1">
                                            <i class="ti ti-building me-1"></i><?= htmlspecialchars($job['department_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge bg-info bg-opacity-10 text-info me-1">
                                        <i
                                            class="ti ti-clock me-1"></i><?= ucfirst(str_replace('-', ' ', $job['employment_type'])) ?>
                                    </span>
                                    <?php if ($job['location']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="ti ti-map-pin me-1"></i><?= htmlspecialchars($job['location']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($job['description']): ?>
                                    <p class="text-secondary small mb-3">
                                        <?= nl2br(htmlspecialchars(substr($job['description'], 0, 150))) ?>...
                                    </p>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="ti ti-users me-1"></i><?= $job['openings'] ?>
                                        opening<?= $job['openings'] > 1 ? 's' : '' ?>
                                    </small>
                                    <button class="btn btn-primary btn-sm rounded-pill px-3 apply-btn"
                                        data-job-id="<?= $job['id'] ?>" data-job-title="<?= htmlspecialchars($job['title']) ?>">
                                        Apply Now <i class="ti ti-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 px-4 pb-4 pt-0">
                                <small class="text-muted">Posted <?= date('M d, Y', strtotime($job['posted_at'])) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Application Modal -->
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="applyModalTitle">Apply for Position</h5>
                    <p class="text-muted mb-0 small" id="applyModalSubtitle"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="applicationForm" enctype="multipart/form-data">
                    <input type="hidden" name="job_id" id="applyJobId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name *</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="dob">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="">-- Select --</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload CV/Resume * <small class="text-muted">(PDF, DOC, DOCX - Max
                                5MB)</small></label>
                        <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx" required>
                    </div>
                </form>
                <div id="applicationResult" class="d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0" id="applyModalFooter">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="submitApplicationBtn">
                    <i class="ti ti-send me-1"></i> Submit Application
                </button>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../components/layout/footer.php';

// Include Nexus Bot for guests
if (!isLoggedIn()) {
    include '../nexusbot/chat_widget.php';
}
?>

<script>
    // Apply Button Click
    document.querySelectorAll('.apply-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('applyJobId').value = this.dataset.jobId;
            document.getElementById('applyModalTitle').textContent = 'Apply for ' + this.dataset.jobTitle;
            document.getElementById('applicationForm').reset();
            document.getElementById('applicationForm').classList.remove('d-none');
            document.getElementById('applicationResult').classList.add('d-none');
            document.getElementById('applyModalFooter').classList.remove('d-none');
            new bootstrap.Modal(document.getElementById('applyModal')).show();
        });
    });

    // Submit Application
    document.getElementById('submitApplicationBtn').addEventListener('click', function () {
        const form = document.getElementById('applicationForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

        const formData = new FormData(form);
        formData.append('action', 'apply_for_job');

        fetch('/hrms/api/api_public_recruitment.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(result => {
                const resultDiv = document.getElementById('applicationResult');
                if (result.success) {
                    form.classList.add('d-none');
                    document.getElementById('applyModalFooter').classList.add('d-none');
                    resultDiv.innerHTML = `<div class="alert alert-success mb-0"><i class="ti ti-circle-check me-2"></i>${result.message}</div>`;
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger mb-0"><i class="ti ti-alert-circle me-2"></i>${result.message}</div>`;
                }
                resultDiv.classList.remove('d-none');
            })
            .catch(() => {
                document.getElementById('applicationResult').innerHTML = '<div class="alert alert-danger mb-0">An error occurred. Please try again.</div>';
                document.getElementById('applicationResult').classList.remove('d-none');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-send me-1"></i> Submit Application';
            });
    });

    // Check Status Form
    document.getElementById('statusCheckForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const email = document.getElementById('statusEmail').value;
        const resultsDiv = document.getElementById('statusResults');
        resultsDiv.innerHTML = '<div class="text-center"><span class="spinner-border spinner-border-sm"></span> Checking...</div>';

        fetch(`/hrms/api/api_public_recruitment.php?action=check_status&email=${encodeURIComponent(email)}`)
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead><tr><th>Position</th><th>Status</th><th>Applied</th></tr></thead><tbody>';
                    result.data.forEach(app => {
                        const statusBadge = {
                            'pending': 'bg-warning',
                            'shortlisted': 'bg-info',
                            'interviewed': 'bg-primary',
                            'offered': 'bg-success',
                            'hired': 'bg-success',
                            'rejected': 'bg-danger'
                        }[app.status] || 'bg-secondary';
                        html += `<tr>
                            <td>${app.job_title}<br><small class="text-muted">${app.company_name}</small></td>
                            <td><span class="badge ${statusBadge}">${app.status.charAt(0).toUpperCase() + app.status.slice(1)}</span></td>
                            <td><small>${new Date(app.applied_at).toLocaleDateString()}</small></td>
                        </tr>`;
                    });
                    html += '</tbody></table></div>';
                    resultsDiv.innerHTML = html;
                } else if (result.success && result.data.length === 0) {
                    resultsDiv.innerHTML = '<div class="alert alert-info mb-0"><i class="ti ti-info-circle me-2"></i>No applications found for this email address.</div>';
                } else {
                    resultsDiv.innerHTML = `<div class="alert alert-danger mb-0">${result.message}</div>`;
                }
            })
            .catch(() => {
                resultsDiv.innerHTML = '<div class="alert alert-danger mb-0">An error occurred. Please try again.</div>';
            });
    });
</script>