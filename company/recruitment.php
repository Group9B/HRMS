<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Recruitment Management";

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    redirect("/hrms/pages/unauthorized.php");
}

$company_id = $_SESSION['company_id'];
// Fetch data for modal dropdowns
$departments = query($mysqli, "SELECT id, name FROM departments WHERE company_id = ? ORDER BY name ASC", [$company_id])['data'] ?? [];
$interviewers = query($mysqli, "SELECT u.id, e.first_name, e.last_name FROM users u JOIN employees e ON u.id = e.user_id JOIN departments d ON e.department_id = d.id WHERE d.company_id = ? ORDER BY e.first_name ASC", [$company_id])['data'] ?? [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="accordion shadow-sm mb-4" id="dashboardAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#statsCollapse">
                        <i class="ti ti-chart-bar me-2"></i> <strong>Dashboard Statistics</strong>
                    </button>
                </h2>
                <div id="statsCollapse" class="accordion-collapse collapse" data-bs-parent="#dashboardAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card shadow-sm bg-primary-subtle">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">Total Jobs
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold" id="totalJobs">0</div>
                                            </div>
                                            <i class="ti ti-briefcase text-primary"
                                                style="font-size: 2.5rem; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card shadow-sm bg-info-subtle">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">Total
                                                    Applications</div>
                                                <div class="h5 mb-0 font-weight-bold" id="totalApplications">0</div>
                                            </div>
                                            <i class="ti ti-file-text text-info"
                                                style="font-size: 2.5rem; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card shadow-sm bg-success-subtle">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">Hired This
                                                    Month</div>
                                                <div class="h5 mb-0 font-weight-bold" id="hiredThisMonth">0</div>
                                            </div>
                                            <i class="ti ti-user-check text-success"
                                                style="font-size: 2.5rem; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card shadow-sm bg-warning-subtle">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">Open Positions
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold" id="openPositions">0</div>
                                            </div>
                                            <i class="ti ti-alert-circle text-warning"
                                                style="font-size: 2.5rem; opacity: 0.5;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Item -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#chartsCollapse">
                        <i class="ti ti-chart-line me-2"></i> <strong>Analytics & Charts</strong>
                    </button>
                </h2>
                <div id="chartsCollapse" class="accordion-collapse collapse" data-bs-parent="#dashboardAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="m-0 font-weight-bold">Applications by Status</h6>
                                    </div>
                                    <div class="card-body" style="height: 300px;">
                                        <canvas id="applicationStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="m-0 font-weight-bold">Job Status Overview</h6>
                                    </div>
                                    <div class="card-body" style="height: 300px;">
                                        <canvas id="jobStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shortlisted Candidates Item -->
            <div class="accordion-item" id="shortlistedAccordionItem" style="display: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#shortlistedCollapse">
                        <i class="ti ti-star me-2"></i> <strong>Shortlisted Candidates</strong>
                    </button>
                </h2>
                <div id="shortlistedCollapse" class="accordion-collapse collapse" data-bs-parent="#dashboardAccordion">
                    <div class="accordion-body">
                        <table class="table table-sm" id="shortlistedTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Candidate</th>
                                    <th>Contact</th>
                                    <th>Job Applied</th>
                                    <th>Applied On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Scheduled Interviews Item -->
            <div class="accordion-item" id="interviewsAccordionItem" style="display: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#interviewsCollapse">
                        <i class="ti ti-calendar me-2"></i> <strong>Scheduled Interviews</strong>
                    </button>
                </h2>
                <div id="interviewsCollapse" class="accordion-collapse collapse" data-bs-parent="#dashboardAccordion">
                    <div class="accordion-body">
                        <table class="table table-sm" id="interviewsTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Candidate</th>
                                    <th>Job Applied</th>
                                    <th>Interview Date</th>
                                    <th>Mode</th>
                                    <th>Interviewer</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Postings -->
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <h6 class="m-0 font-weight-bold">Job Postings</h6><button class="btn btn-primary btn-sm"
                        onclick="prepareJobModal()"><i class="ti ti-plus me-1"></i> Post New
                        Job</button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover" id="jobsTable" width="100%">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Applicants</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="jobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="jobForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="jobModalLabel"></h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="id" id="jobId">
                    <?php if (empty($departments)): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-circle me-2"></i>
                            <strong>No Departments Found</strong>
                            <p class="mb-0 mt-2">Please add departments in the <strong>Organization Management</strong>
                                section before posting a job.</p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Job Title <span
                                    class="text-danger">*</span></label><input type="text" class="form-control"
                                name="title" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Department</label><select
                                class="form-select" name="department_id" <?= empty($departments) ? 'disabled' : '' ?>>
                                <option value="">-- Select --</option><?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control"
                            name="description" rows="5"></textarea></div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label">Employment Type</label><select
                                class="form-select" name="employment_type">
                                <option value="full-time">Full-Time</option>
                                <option value="part-time">Part-Time</option>
                                <option value="internship">Internship</option>
                                <option value="contract">Contract</option>
                            </select></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Location</label><input type="text"
                                class="form-control" name="location"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Openings <span
                                    class="text-danger">*</span></label><input type="number" class="form-control"
                                name="openings" min="1" value="1" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Status</label><select class="form-select"
                                name="status">
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                            </select></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save
                        Job</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Applicants Modal -->
<div class="modal fade" id="applicantsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicantsModalLabel"></h5><button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm" id="applicantsTable" width="100%">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Contact</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Interview Modal -->
<div class="modal fade" id="interviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="interviewForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="interviewModalLabel"></h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="application_id" id="interviewApplicationId">
                    <div class="mb-3"><label class="form-label">Interviewer *</label><select class="form-select"
                            name="interviewer_id" required>
                            <option value="">-- Select --</option><?php foreach ($interviewers as $interviewer): ?>
                                <option value="<?= $interviewer['id'] ?>">
                                    <?= htmlspecialchars($interviewer['first_name'] . ' ' . $interviewer['last_name']) ?>
                                </option><?php endforeach; ?>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Date & Time *</label><input type="datetime-local"
                            class="form-control" name="interview_date" required></div>
                    <div class="mb-3"><label class="form-label">Mode</label><select class="form-select" name="mode">
                            <option value="offline">In-Person</option>
                            <option value="online">Online</option>
                        </select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit"
                        class="btn btn-primary">Schedule</button></div>
            </form>
        </div>
    </div>
</div>


<!-- Hire Password Modal -->
<div class="modal fade" id="hirePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hirePasswordForm">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="ti ti-user-plus me-2"></i>Hire Candidate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="application_id" id="hireApplicationId">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Set a password for the new employee's account. They will receive this via email.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" id="hirePassword" minlength="6"
                            required placeholder="Minimum 6 characters">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="hirePasswordConfirm" minlength="6" required
                            placeholder="Re-enter password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check me-1"></i>Hire & Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>
<script>
    let tables = {}, modals = {};
    const applicationStatuses = ['pending', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected'];

    // --- Validation Functions ---
    function validateJobTitle(title) {
        if (!title || title.trim().length === 0) return 'Job title is required.';
        if (title.length < 3) return 'Job title must be at least 3 characters long.';
        if (title.length > 100) return 'Job title must not exceed 100 characters.';
        if (!/^[a-zA-Z0-9\s\-&.,()]+$/.test(title)) return 'Job title contains invalid characters.';
        return null;
    }

    function validateDescription(description, maxLength = 2000) {
        if (description && description.length > maxLength) return `Description must not exceed ${maxLength} characters.`;
        return null;
    }

    function validateLocation(location) {
        if (location && location.length > 100) return 'Location must not exceed 100 characters.';
        return null;
    }

    function validateOpenings(openings) {
        if (!openings || isNaN(openings)) return 'Number of openings is required.';
        if (parseInt(openings) < 1) return 'Number of openings must be at least 1.';
        if (parseInt(openings) > 999) return 'Number of openings cannot exceed 999.';
        return null;
    }

    function validateEmploymentType(type) {
        const validTypes = ['full-time', 'part-time', 'internship', 'contract'];
        if (!validTypes.includes(type)) return 'Invalid employment type selected.';
        return null;
    }

    function validateJobStatus(status) {
        const validStatuses = ['open', 'closed'];
        if (!validStatuses.includes(status)) return 'Invalid job status.';
        return null;
    }

    function validateInterviewDate(date) {
        if (!date) return 'Interview date and time is required.';
        const timestamp = new Date(date).getTime();
        if (isNaN(timestamp)) return 'Invalid date format.';
        if (timestamp < Date.now()) return 'Interview date cannot be in the past.';
        return null;
    }

    function validateApplicationStatus(status) {
        if (!applicationStatuses.includes(status)) return 'Invalid application status.';
        return null;
    }
    // --- End Validation Functions ---

    $(function () {
        modals = {
            job: new bootstrap.Modal('#jobModal'),
            applicants: new bootstrap.Modal('#applicantsModal'),
            interview: new bootstrap.Modal('#interviewModal'),
            hirePassword: new bootstrap.Modal('#hirePasswordModal')
        };

        // Initialize dashboard
        loadDashboardData();
        loadShortlistedCandidates();
        loadScheduledInterviews();

        tables.jobs = $('#jobsTable').DataTable({
            ajax: { url: '/hrms/api/api_recruitment.php?action=get_jobs', dataSrc: 'data' },
            responsive: true,
            columns: [
                { data: 'title' }, { data: 'department_name', defaultContent: 'N/A' },
                { data: 'location', defaultContent: 'N/A' },
                { data: 'application_count', className: 'text-center' },
                { data: 'status', render: d => `<span class="badge text-bg-${d === 'open' ? 'success' : 'secondary'}">${capitalize(d)}</span>` },
                {
                    data: null, orderable: false, className: 'text-end', width: '10%', render: (d, t, r) => {
                        const hasApplicants = r.application_count > 0;
                        const callbacks = {
                            onManage: () => viewApplicants(r.id, r.title),
                            onViewLink: () => copyJobLink(r.id),
                        };
                        const tooltips = {
                            manageTooltip: 'View Applicants',
                            viewLinkTooltip: 'Copy Link',
                        };

                        // Only add edit and delete if no applicants
                        if (!hasApplicants) {
                            callbacks.onEdit = () => prepareJobModal(r);
                            callbacks.onDelete = () => deleteJob(r.id, r.title);
                            tooltips.editTooltip = 'Edit Job';
                            tooltips.deleteTooltip = 'Delete Job';
                        } else {
                            // Add close opening button if applicants exist and job is still open
                            if (r.status === 'open') {
                                callbacks.onClose = () => closeJobOpening(r.id, r.title);
                                tooltips.closeTooltip = 'Close Job Opening';
                            }
                        }

                        return createActionDropdown(callbacks, tooltips);
                    }
                }
            ]
        });

        $('#jobForm').on('submit', handleJobFormSubmit);
        $('#interviewForm').on('submit', handleInterviewFormSubmit);
    });

    // Dashboard Functions
    let applicationStatusChartInstance = null;
    let jobStatusChartInstance = null;

    function loadDashboardData() {
        fetch('/hrms/api/api_recruitment.php?action=get_dashboard_stats')
            .then(res => res.json())
            .then(result => {
                console.log('Dashboard stats:', result);
                if (result.success) {
                    const stats = result.data;
                    $('#totalJobs').text(stats.total_jobs || 0);
                    $('#totalApplications').text(stats.total_applications || 0);
                    $('#hiredThisMonth').text(stats.hired_this_month || 0);
                    $('#openPositions').text(stats.open_positions || 0);

                    // Application Status Chart
                    const statusCtx = document.getElementById('applicationStatusChart');
                    if (statusCtx) {
                        const ctx = statusCtx.getContext('2d');
                        if (applicationStatusChartInstance) {
                            applicationStatusChartInstance.destroy();
                        }
                        applicationStatusChartInstance = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: ['Pending', 'Shortlisted', 'Interviewed', 'Offered', 'Hired', 'Rejected'],
                                datasets: [{
                                    label: 'Applications',
                                    data: [
                                        stats.pending || 0,
                                        stats.shortlisted || 0,
                                        stats.interviewed || 0,
                                        stats.offered || 0,
                                        stats.hired || 0,
                                        stats.rejected || 0
                                    ],
                                    backgroundColor: [
                                        '#FFC107', '#17A2B8', '#6F42C1', '#FD7E14', '#28A745', '#DC3545'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y',
                                plugins: { legend: { display: false } },
                                scales: { x: { beginAtZero: true } }
                            }
                        });
                    }

                    // Job Status Chart
                    const jobCtx = document.getElementById('jobStatusChart');
                    if (jobCtx) {
                        const ctx = jobCtx.getContext('2d');
                        if (jobStatusChartInstance) {
                            jobStatusChartInstance.destroy();
                        }
                        jobStatusChartInstance = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Open', 'Closed'],
                                datasets: [{
                                    data: [
                                        stats.open || 0,
                                        stats.closed || 0
                                    ],
                                    backgroundColor: ['#28A745', '#6C757D'],
                                    borderColor: '#fff',
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom' }
                                }
                            }
                        });
                    }
                } else {
                    console.error('Failed to load dashboard stats:', result.message);
                }
            })
            .catch(err => console.error('Error loading dashboard:', err));
    }

    function loadShortlistedCandidates() {
        fetch('/hrms/api/api_recruitment.php?action=get_shortlisted_candidates')
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data.length > 0) {
                    $('#shortlistedAccordionItem').show();
                    if (!$.fn.DataTable.isDataTable('#shortlistedTable')) {
                        $('#shortlistedTable').DataTable({
                            data: result.data,
                            columns: [
                                { data: null, render: (d, t, r) => `<strong>${r.first_name} ${r.last_name}</strong><br><small>${r.email}</small>` },
                                { data: 'phone', defaultContent: 'N/A' },
                                { data: 'job_title', defaultContent: 'N/A' },
                                { data: 'applied_at', render: d => new Date(d).toLocaleDateString() },
                                {
                                    data: null, orderable: false, render: (d, t, r) =>
                                        `<button class="btn btn-sm btn-primary" onclick='prepareInterviewModal(${r.application_id}, "${r.first_name} ${r.last_name}")'>Schedule Interview</button>`
                                }
                            ],
                            destroy: true
                        });
                    }
                } else {
                    $('#shortlistedAccordionItem').hide();
                }
            });
    }

    function loadScheduledInterviews() {
        fetch('/hrms/api/api_recruitment.php?action=get_scheduled_interviews')
            .then(res => res.json())
            .then(result => {
                console.log('Scheduled interviews:', result);
                if (result.success && result.data.length > 0) {
                    $('#interviewsAccordionItem').show();
                    if (!$.fn.DataTable.isDataTable('#interviewsTable')) {
                        $('#interviewsTable').DataTable({
                            data: result.data,
                            columns: [
                                { data: null, render: (d, t, r) => `<strong>${r.first_name} ${r.last_name}</strong><br><small>${r.email}</small>` },
                                { data: 'job_title', defaultContent: 'N/A' },
                                { data: 'interview_date', render: d => new Date(d).toLocaleString() },
                                { data: 'mode', render: d => `<span class="badge ${d === 'online' ? 'bg-info' : 'bg-secondary'}">${capitalize(d)}</span>` },
                                { data: null, render: (d, t, r) => `${r.interviewer_first_name} ${r.interviewer_last_name}` },
                                {
                                    data: null, orderable: false, className: 'text-end', render: (d, t, r) => {
                                        return `<div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick='markInterviewComplete(${r.interview_id}, "${r.first_name} ${r.last_name}")' title="Mark as Completed">
                                                <i class="ti ti-check"></i> Complete
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" onclick='rescheduleInterview(${r.interview_id})' title="Reschedule">
                                                <i class="ti ti-calendar"></i> Reschedule
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" onclick='cancelInterview(${r.interview_id})' title="Cancel">
                                                <i class="ti ti-x"></i> Cancel
                                            </button>
                                        </div>`;
                                    }
                                }
                            ],
                            destroy: true
                        });
                    }
                } else {
                    $('#interviewsAccordionItem').hide();
                }
            })
            .catch(err => console.error('Error loading interviews:', err));
    }

    // Interview action functions
    function markInterviewComplete(interviewId, candidateName) {
        if (!confirm(`Mark interview as completed for ${candidateName}?`)) return;

        const formData = new FormData();
        formData.append('action', 'complete_interview');
        formData.append('interview_id', interviewId);

        fetch('/hrms/api/api_recruitment.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    showToast('Interview marked as completed!', 'success');
                    loadScheduledInterviews();
                } else {
                    showToast(result.message, 'error');
                }
            });
    }

    function rescheduleInterview(interviewId) {
        alert('Reschedule feature - redirect to interview form with interview_id: ' + interviewId);
        // TODO: Implement reschedule interview modal
    }

    function cancelInterview(interviewId) {
        if (!confirm('Are you sure you want to cancel this interview?')) return;

        const formData = new FormData();
        formData.append('action', 'cancel_interview');
        formData.append('interview_id', interviewId);

        fetch('/hrms/api/api_recruitment.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    showToast('Interview cancelled!', 'success');
                    loadScheduledInterviews();
                } else {
                    showToast(result.message, 'error');
                }
            });
    }

    function handleJobFormSubmit(e) {
        e.preventDefault();
        const form = $(this);

        // Get form values
        const title = form.find('[name="title"]').val().trim();
        const description = form.find('[name="description"]').val().trim();
        const location = form.find('[name="location"]').val().trim();
        const openings = form.find('[name="openings"]').val();
        const employmentType = form.find('[name="employment_type"]').val();
        const status = form.find('[name="status"]').val();

        // Validate all fields
        const titleError = validateJobTitle(title);
        if (titleError) { showToast(titleError, 'error'); return; }

        const descError = validateDescription(description);
        if (descError) { showToast(descError, 'error'); return; }

        const locError = validateLocation(location);
        if (locError) { showToast(locError, 'error'); return; }

        const openingsError = validateOpenings(openings);
        if (openingsError) { showToast(openingsError, 'error'); return; }

        const typeError = validateEmploymentType(employmentType);
        if (typeError) { showToast(typeError, 'error'); return; }

        const statusError = validateJobStatus(status);
        if (statusError) { showToast(statusError, 'error'); return; }

        // Submit form
        const formData = new FormData(this);
        formData.append('action', 'add_edit_job');
        fetch('/hrms/api/api_recruitment.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(result => {
                if (result.success) {
                    showToast(result.message, 'success');
                    modals.job.hide();
                    tables.jobs.ajax.reload();
                } else { showToast(result.message, 'error'); }
            });
    }

    function handleInterviewFormSubmit(e) {
        e.preventDefault();
        const form = $(this);

        const interviewerId = form.find('[name="interviewer_id"]').val();
        const interviewDate = form.find('[name="interview_date"]').val();

        if (!interviewerId) {
            showToast('Interviewer is required.', 'error');
            return;
        }

        const dateError = validateInterviewDate(interviewDate);
        if (dateError) { showToast(dateError, 'error'); return; }

        const formData = new FormData(this);
        formData.append('action', 'schedule_interview');
        fetch('/hrms/api/api_recruitment.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(result => {
                if (result.success) {
                    showToast(result.message, 'success');
                    modals.interview.hide();
                    tables.jobs.ajax.reload();
                    if (tables.applicants) tables.applicants.ajax.reload();
                } else { showToast(result.message, 'error'); }
            });
    }

    function deleteJob(jobId, jobTitle) {
        showConfirmationModal(
            `Are you sure you want to delete the job posting for "${escapeHTML(jobTitle)}"?`,
            () => {
                const formData = new FormData();
                formData.append('action', 'delete_job');
                formData.append('id', jobId);
                fetch('/hrms/api/api_recruitment.php', { method: 'POST', body: formData })
                    .then(res => res.json()).then(result => {
                        if (result.success) {
                            showToast(result.message, 'success');
                            tables.jobs.ajax.reload();
                        } else {
                            showToast(result.message, 'error');
                        }
                    });
            },
            'Delete Job Posting',
            'Delete',
            'btn-danger'
        );
    }

    function closeJobOpening(jobId, jobTitle) {
        showConfirmationModal(
            `Are you sure you want to close the job opening for "${escapeHTML(jobTitle)}"? This will prevent new applications.`,
            () => {
                // Get current job data from table
                const jobRow = tables.jobs.rows().data().toArray().find(row => row.id == jobId);
                if (!jobRow) {
                    showToast('Error: Could not find job data.', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'add_edit_job');
                formData.append('id', jobId);
                formData.append('title', jobRow.title);
                formData.append('department_id', jobRow.department_id);
                formData.append('description', jobRow.description);
                formData.append('employment_type', jobRow.employment_type);
                formData.append('location', jobRow.location);
                formData.append('openings', jobRow.openings);
                formData.append('status', 'closed');

                fetch('/hrms/api/api_recruitment.php', { method: 'POST', body: formData })
                    .then(res => res.json()).then(result => {
                        if (result.success) {
                            showToast('Job opening closed successfully!', 'success');
                            tables.jobs.ajax.reload();
                        } else {
                            showToast(result.message, 'error');
                        }
                    }).catch(err => {
                        console.error('Error:', err);
                        showToast('An error occurred while closing the job opening.', 'error');
                    });
            },
            'Close Job Opening',
            'Close',
            'btn-warning'
        );
    }

    function prepareJobModal(data = null) {
        // Check if trying to edit job with applicants
        if (data && data.application_count > 0) {
            showToast('Cannot edit this job - applicants have already applied. You can only close the job opening.', 'warning');
            return;
        }

        $('#jobForm').trigger('reset');
        if (data) {
            $('#jobModalLabel').text('Edit Job Posting');
            $('#jobId').val(data.id);
            $('[name="title"]').val(data.title);
            $('[name="department_id"]').val(data.department_id);
            $('[name="description"]').val(data.description);
            $('[name="employment_type"]').val(data.employment_type);
            $('[name="location"]').val(data.location);
            $('[name="openings"]').val(data.openings);
            $('[name="status"]').val(data.status);
        } else {
            $('#jobModalLabel').text('Post New Job');
            $('#jobId').val(0);
        }
        modals.job.show();
    }

    function viewApplicants(jobId, jobTitle) {
        $('#applicantsModalLabel').text(`Applicants for: ${jobTitle}`);
        if ($.fn.DataTable.isDataTable('#applicantsTable')) {
            tables.applicants.ajax.url(`/hrms/api/api_recruitment.php?action=get_applications&job_id=${jobId}`).load();
        } else {
            tables.applicants = $('#applicantsTable').DataTable({
                ajax: { url: `/hrms/api/api_recruitment.php?action=get_applications&job_id=${jobId}`, dataSrc: 'data' },
                columns: [
                    { data: null, render: (d, t, r) => `<strong>${r.first_name} ${r.last_name}</strong><br><small>${r.email}</small>` },
                    { data: 'phone', defaultContent: 'N/A' },
                    { data: 'applied_at', render: d => new Date(d).toLocaleDateString() },
                    { data: 'status', render: (d, t, r) => createStatusDropdown(d, r.id) },
                    {
                        data: null, orderable: false, render: (d, t, r) => {
                            const isHired = r.status === 'hired';
                            const isRejected = r.status === 'rejected';
                            if (isHired || isRejected) return '';
                            return `<button class="btn btn-sm btn-primary" onclick='prepareInterviewModal(${r.id}, "${r.first_name} ${r.last_name}")'>Schedule Interview</button>`;
                        }
                    }
                ]
            });
        }
        modals.applicants.show();
    }

    function createStatusDropdown(currentStatus, appId) {
        let options = applicationStatuses.map(s => `<option value="${s}" ${s === currentStatus ? 'selected' : ''}>${capitalize(s)}</option>`).join('');
        const isHired = currentStatus === 'hired';
        const isRejected = currentStatus === 'rejected';
        const disabled = (isHired || isRejected) ? 'disabled' : '';
        return `<select class="form-select form-select-sm" onchange="updateStatus(${appId}, this.value)" ${disabled}>${options}</select>`;
    }

    function updateStatus(appId, status) {
        const statusError = validateApplicationStatus(status);
        if (statusError) {
            showToast(statusError, 'error');
            return;
        }

        // Get the applicant row to check current status
        const appRow = tables.applicants.rows().data().toArray().find(row => row.id == appId);
        if (appRow && appRow.status === 'rejected') {
            showToast('Cannot change status - this applicant has been rejected.', 'warning');
            if (tables.applicants) tables.applicants.ajax.reload();
            return;
        }
        if (appRow && appRow.status === 'hired') {
            showToast('Cannot change status - this applicant has been hired.', 'warning');
            if (tables.applicants) tables.applicants.ajax.reload();
            return;
        }

        const formData = new FormData();
        formData.append('action', 'update_application_status');
        formData.append('id', appId);
        formData.append('status', status);

        fetch('/hrms/api/api_recruitment.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(result => {
                if (result.success) {
                    // Check if requires further action
                    if (result.requires_action === 'hire') {
                        // Show hire password modal
                        $('#hirePasswordForm').trigger('reset');
                        $('#hireApplicationId').val(result.application_id);
                        modals.hirePassword.show();
                    } else if (result.requires_action === 'confirm_delete') {
                        // Show delete confirmation using browser confirm
                        const keepData = confirm(
                            'Candidate rejected.\n\n' +
                            'Do you want to KEEP this candidate\'s data for future opportunities?\n\n' +
                            'Click OK to keep data, Cancel to delete data.'
                        );

                        if (keepData) {
                            showToast('Candidate rejected. Data retained for future opportunities.', 'info');
                            if (tables.applicants) tables.applicants.ajax.reload();
                        } else {
                            deleteCandidateData(result.application_id);
                        }
                    } else {
                        showToast(result.message, 'success');
                        if (tables.applicants) tables.applicants.ajax.reload();
                    }
                } else {
                    showToast(result.message, 'error');
                    if (tables.applicants) tables.applicants.ajax.reload();
                }
            });
    }

    // Handle hire password form submission
    $('#hirePasswordForm').on('submit', function (e) {
        e.preventDefault();

        const password = $('#hirePassword').val();
        const confirm = $('#hirePasswordConfirm').val();

        if (password !== confirm) {
            showToast('Passwords do not match!', 'error');
            return;
        }

        if (password.length < 6) {
            showToast('Password must be at least 6 characters.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'hire_candidate');
        formData.append('application_id', $('#hireApplicationId').val());
        formData.append('password', password);

        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        fetch('/hrms/api/api_recruitment.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(result => {
                if (result.success) {
                    modals.hirePassword.hide();
                    showToast(result.message, 'success');

                    // Show next steps info
                    setTimeout(() => {
                        showConfirmationModal(
                            `<div class="text-center mb-3"><i class="ti ti-check-circle text-success" style="font-size:3rem"></i></div>
                            <strong>Employee Created Successfully!</strong><br><br>
                            <div class="alert alert-info mt-3 text-start">
                                <strong>Welcome email sent with credentials.</strong><br>
                                <strong>Next Steps:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Assign <strong>Shift</strong> and <strong>Designation</strong></li>
                                    <li>Set salary in employee management</li>
                                </ul>
                            </div>`,
                            () => {
                                if (tables.applicants) tables.applicants.ajax.reload();
                                loadDashboardData();
                            },
                            'Welcome New Employee!',
                            'OK',
                            'btn-success'
                        );
                    }, 300);
                } else {
                    showToast(result.message, 'error');
                }
            }).finally(() => {
                btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Hire & Create Account');
            });
    });

    // Delete candidate data for rejected candidates
    function deleteCandidateData(appId) {
        const formData = new FormData();
        formData.append('action', 'delete_candidate_data');
        formData.append('application_id', appId);

        fetch('/hrms/api/api_recruitment.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(result => {
                if (result.success) {
                    showToast('Candidate data deleted.', 'success');
                } else {
                    showToast(result.message, 'error');
                }
                if (tables.applicants) tables.applicants.ajax.reload();
            });
    }

    function prepareInterviewModal(appId, candidateName) {
        $('#interviewForm').trigger('reset');
        $('#interviewModalLabel').text(`Schedule Interview for ${candidateName}`);
        $('#interviewApplicationId').val(appId);
        modals.interview.show();
    }

    /**
     * Copies the public application link for a job to the clipboard.
     * @param {number} jobId The ID of the job.
     */
    function copyJobLink(jobId) {
        const baseUrl = window.location.origin;
        const link = `${baseUrl}/hrms/candidate/register.php?job_id=${jobId}`;

        const tempInput = document.createElement('textarea');
        tempInput.style.position = 'absolute';
        tempInput.style.left = '-9999px';
        tempInput.value = link;
        document.body.appendChild(tempInput);
        tempInput.select();
        tempInput.focus();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showToast('Application link copied to clipboard!', 'success');
            } else {
                showToast('Failed to copy link.', 'error');
            }
        } catch (err) {
            showToast('Failed to copy link.', 'error');
        }

        document.body.removeChild(tempInput);
    }

</script>