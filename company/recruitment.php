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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0"><i class="ti ti-briefcase me-2"></i>Recruitment</h2>
            <button class="btn btn-primary" onclick="prepareJobModal()"><i class="ti ti-plus me-1"></i> Post New
                Job</button>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Job Postings</h6>
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

<!-- Job Modal -->
<div class="modal fade" id="jobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="jobForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="jobModalLabel"></h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="id" id="jobId">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Job Title *</label><input type="text"
                                class="form-control" name="title" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Department</label><select
                                class="form-select" name="department_id">
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
                        <div class="col-md-3 mb-3"><label class="form-label">Openings *</label><input type="number"
                                class="form-control" name="openings" min="1" value="1" required></div>
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
            interview: new bootstrap.Modal('#interviewModal')
        };

        tables.jobs = $('#jobsTable').DataTable({
            ajax: { url: '/hrms/api/api_recruitment.php?action=get_jobs', dataSrc: 'data' },
            responsive: true,
            columns: [
                { data: 'title' }, { data: 'department_name', defaultContent: 'N/A' },
                { data: 'location', defaultContent: 'N/A' },
                { data: 'application_count', className: 'text-center' },
                { data: 'status', render: d => `<span class="badge text-bg-${d === 'open' ? 'success' : 'secondary'}">${capitalize(d)}</span>` },
                {
                    data: null, orderable: false, className: 'text-end', width: '10%', render: (d, t, r) => createActionDropdown(
                        {
                            onEdit: () => prepareJobModal(r),
                            onManage: () => viewApplicants(r.id, r.title),
                            onDelete: () => deleteJob(r.id, r.title),
                            onViewLink: () => copyJobLink(r.id),
                        },
                        {
                            editTooltip: 'Edit Job',
                            manageTooltip: 'View Applicants',
                            deleteTooltip: 'Delete Job',
                        }
                    )
                }
            ]
        });

        $('#jobForm').on('submit', handleJobFormSubmit);
        $('#interviewForm').on('submit', handleInterviewFormSubmit);
    });

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

    function prepareJobModal(data = null) {
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
                    { data: null, orderable: false, render: (d, t, r) => `<button class="btn btn-sm btn-primary" onclick='prepareInterviewModal(${r.id}, "${r.first_name} ${r.last_name}")'>Schedule Interview</button>` }
                ]
            });
        }
        modals.applicants.show();
    }

    function createStatusDropdown(currentStatus, appId) {
        let options = applicationStatuses.map(s => `<option value="${s}" ${s === currentStatus ? 'selected' : ''}>${capitalize(s)}</option>`).join('');
        const isHired = currentStatus === 'hired';
        const disabled = isHired ? 'disabled' : '';
        const hiredBadge = isHired ? ' <span class="badge bg-success-subtle text-success ms-2">Final Status</span>' : '';
        return `<select class="form-select form-select-sm" onchange="updateStatus(${appId}, this.value)" ${disabled}>${options}</select>${hiredBadge}`;
    }

    function updateStatus(appId, status) {
        const statusError = validateApplicationStatus(status);
        if (statusError) {
            showToast(statusError, 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'update_application_status');
        formData.append('id', appId);
        formData.append('status', status);
        fetch('/hrms/api/api_recruitment.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(result => {
                if (result.success) {
                    showToast(result.message, 'success');
                } else {
                    showToast(result.message, 'error');
                }
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