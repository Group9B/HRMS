<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Check Application Status";
require_once '../components/layout/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="m-0">Check Your Application Status</h4>
                </div>
                <div class="card-body">
                    <form id="statusCheckForm" class="mb-4">
                        <label class="form-label">Enter your email address to view your applications:</label>
                        <div class="input-group">
                            <input type="email" name="email" class="form-control" placeholder="your.email@example.com"
                                required>
                            <button type="submit" class="btn btn-primary">Check Status</button>
                        </div>
                    </form>
                    <div id="statusResult">
                        <p class="text-muted text-center">Your application history will appear here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../components/layout/footer.php'; ?>
<script>
    $(function () {
        $('#statusCheckForm').on('submit', function (e) {
            e.preventDefault();
            const email = $(this).find('[name="email"]').val();
            const resultDiv = $('#statusResult');
            const btn = $(this).find('button');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            resultDiv.html('<div class="text-center"><div class="spinner-border"></div></div>');

            fetch(`/hrms/api/api_public_recruitment.php?action=check_status&email=${encodeURIComponent(email)}`)
                .then(res => res.json()).then(result => {
                    resultDiv.empty();
                    if (result.success && result.data.length > 0) {
                        result.data.forEach(app => {
                            let interviewDetails = '';
                            if (app.interview_date) {
                                interviewDetails = `
                                <hr>
                                <h6><i class="fas fa-calendar-check me-2"></i>Interview Scheduled</h6>
                                <p class="card-text mb-0">
                                    <strong>Date & Time:</strong> ${new Date(app.interview_date).toLocaleString('en-IN', { dateStyle: 'long', timeStyle: 'short' })}
                                </p>
                                <p class="card-text mb-0">
                                    <strong>Mode:</strong> ${capitalize(app.mode)}
                                </p>
                                <p class="card-text">
                                    <strong>Interviewer:</strong> ${escapeHTML(app.interviewer_name || 'N/A')}
                                </p>
                            `;
                            }

                            const card = `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">${escapeHTML(app.job_title)}</h5>
                                    <h6 class="card-subtitle mb-2 text-muted">${escapeHTML(app.company_name)}</h6>
                                    <p class="card-text">
                                        Applied On: <strong>${new Date(app.applied_at).toLocaleDateString()}</strong><br>
                                        Status: <span class="badge text-bg-${getStatusClass(app.status)}">${capitalize(app.status)}</span>
                                    </p>
                                    ${interviewDetails}
                                </div>
                            </div>
                        `;
                            resultDiv.append(card);
                        });
                    } else {
                        resultDiv.html('<p class="text-muted text-center">No applications found for this email address.</p>');
                    }
                }).finally(() => {
                    btn.prop('disabled', false).text('Check Status');
                });
        });
    });
    function getStatusClass(status) {
        const map = { 'hired': 'success', 'offered': 'success', 'interviewed': 'info', 'shortlisted': 'primary', 'rejected': 'danger' };
        return map[status] || 'secondary'; // pending
    }
</script>