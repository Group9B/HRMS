<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Interview Calendar";

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3, 6])) {
    redirect("/hrms/pages/unauthorized.php");
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0"><i class="ti ti-calendar me-2"></i>Interview Calendar</h5>
                <a href="/hrms/company/recruitment.php" class="btn btn-outline-primary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Back to Recruitment
                </a>
            </div>
            <div class="card-body">
                <div id="interviewCalendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-calendar-event me-2"></i>Interview Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetailsBody">
                <!-- Populated by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>

<style>
    #interviewCalendar {
        max-width: 100%;
    }

    .fc-event {
        cursor: pointer;
    }

    .fc-daygrid-event {
        padding: 4px 6px;
    }

    .interview-online {
        background-color: #0dcaf0 !important;
        border-color: #0dcaf0 !important;
    }

    .interview-offline {
        background-color: #6f42c1 !important;
        border-color: #6f42c1 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('interviewCalendar');
        const detailsModal = new bootstrap.Modal('#eventDetailsModal');

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 'auto',
            events: function (info, successCallback, failureCallback) {
                fetch('/hrms/api/api_recruitment.php?action=get_scheduled_interviews')
                    .then(res => res.json())
                    .then(result => {
                        if (result.success && result.data) {
                            const events = result.data.map(interview => ({
                                id: interview.interview_id,
                                title: `${interview.first_name} ${interview.last_name}`,
                                start: interview.interview_date,
                                className: interview.mode === 'online' ? 'interview-online' : 'interview-offline',
                                extendedProps: {
                                    candidateName: `${interview.first_name} ${interview.last_name}`,
                                    email: interview.email,
                                    jobTitle: interview.job_title,
                                    mode: interview.mode,
                                    interviewer: `${interview.interviewer_first_name || ''} ${interview.interviewer_last_name || ''}`.trim()
                                }
                            }));
                            successCallback(events);
                        } else {
                            successCallback([]);
                        }
                    })
                    .catch(err => {
                        console.error('Error loading interviews:', err);
                        failureCallback(err);
                    });
            },
            eventClick: function (info) {
                const props = info.event.extendedProps;
                const dateStr = new Date(info.event.start).toLocaleString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const modeIcon = props.mode === 'online' ? '💻' : '🏢';
                const modeBadge = props.mode === 'online'
                    ? '<span class="badge bg-info">Online</span>'
                    : '<span class="badge bg-purple">In-Person</span>';

                document.getElementById('eventDetailsBody').innerHTML = `
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Candidate</h6>
                        <h5>${props.candidateName}</h5>
                        <small class="text-muted">${props.email}</small>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <h6 class="text-muted mb-1">Position</h6>
                            <p class="m-0">${props.jobTitle || 'N/A'}</p>
                        </div>
                        <div class="col-6 mb-3">
                            <h6 class="text-muted mb-1">Mode</h6>
                            <p class="m-0">${modeIcon} ${modeBadge}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <h6 class="text-muted mb-1">Date & Time</h6>
                            <p class="m-0">${dateStr}</p>
                        </div>
                        <div class="col-6 mb-3">
                            <h6 class="text-muted mb-1">Interviewer</h6>
                            <p class="m-0">${props.interviewer || 'N/A'}</p>
                        </div>
                    </div>
                `;
                detailsModal.show();
            }
        });

        calendar.render();
    });
</script>