<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

requireAuth();

$user_id = $_SESSION['user_id'];
$currentUser = getCurrentUser($mysqli);

// Get user's support tickets
$ticketsRes = query($mysqli, "SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC", [$user_id]);
$tickets = $ticketsRes['success'] ? $ticketsRes['data'] : [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>

    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ti ti-ticket me-2"></i>Create New Support Ticket
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="createTicketForm" class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ticketSubject" name="subject"
                                    placeholder="Brief description of your issue" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select" id="ticketPriority" name="priority" required>
                                    <option value="">Select priority</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="ticketMessage" name="message" rows="5"
                                    placeholder="Please describe your issue in detail..." required></textarea>
                            </div>
                            <div class="col-12 d-flex gap-2 align-items-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-send me-2"></i>Submit Ticket
                                </button>
                                <small id="submitStatus" class="text-muted"></small>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Support Tickets List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="ti ti-list me-2"></i>Your Support Tickets
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tickets)): ?>
                            <div class="alert alert-info" role="alert">
                                <i class="ti ti-info-circle me-2"></i>
                                No support tickets yet. Create one above if you need assistance.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered  " id="ticketsTable">
                                    <thead class="table">
                                        <tr>
                                            <th>ID</th>
                                            <th>Subject</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Created Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tickets as $ticket): ?>
                                            <tr>
                                                <td><strong>#<?php echo htmlspecialchars($ticket['id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                                <td>
                                                    <?php
                                                    $priorityClass = match ($ticket['priority']) {
                                                        'low' => 'bg-success-subtle text-success-emphasis',
                                                        'medium' => 'bg-warning-subtle text-warning-emphasis',
                                                        'high' => 'bg-danger-subtle text-danger-emphasis',
                                                        default => 'bg-secondary-subtle text-secondary-emphasis'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $priorityClass; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($ticket['priority'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = match ($ticket['status']) {
                                                        'open' => 'bg-info-subtle text-info-emphasis',
                                                        'in_progress' => 'bg-warning-subtle text-warning-emphasis',
                                                        'closed' => 'bg-success-subtle text-success-emphasis',
                                                        default => 'bg-secondary-subtle text-secondary-emphasis'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($ticket['status']))); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary view-ticket-btn"
                                                        data-id="<?php echo $ticket['id']; ?>" data-bs-toggle="modal"
                                                        data-bs-target="#ticketModal">
                                                        <i class="ti ti-eye me-1"></i>View
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ticket Detail Modal -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ticketModalLabel">Support Ticket Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="ticketDetails">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
        const $form = $('#createTicketForm');
        const $status = $('#submitStatus');

        // Function to reload tickets table
        function reloadTicketsTable() {
            fetch(window.location.href)
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');
                    const newTableBody = newDoc.querySelector('#ticketsTable tbody');
                    const currentTableBody = document.querySelector('#ticketsTable tbody');

                    if (newTableBody && currentTableBody) {
                        currentTableBody.innerHTML = newTableBody.innerHTML;

                        // Re-attach event handlers to new rows
                        $(document).off('click', '.view-ticket-btn');
                        $(document).on('click', '.view-ticket-btn', handleViewTicket);
                    }
                });
        }

        // Create new ticket
        $form.on('submit', function (e) {
            e.preventDefault();

            $status.html('<i class="ti ti-loader me-1"></i>Submitting...').removeClass('d-none');

            const formData = new FormData();
            formData.append('action', 'create_ticket');
            formData.append('subject', $('#ticketSubject').val());
            formData.append('message', $('#ticketMessage').val());
            formData.append('priority', $('#ticketPriority').val());

            fetch('/hrms/api/api_support.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res && res.success) {
                        $status.html('<i class="ti ti-circle-check me-1"></i>Ticket submitted successfully').addClass('text-success');
                        $form[0].reset();
                        setTimeout(() => reloadTicketsTable(), 800);
                    } else {
                        $status.html('<i class="ti ti-alert-circle me-1"></i>Unable to submit ticket. Please try again.').addClass('text-warning');
                    }
                })
                .catch(err => {
                    $status.html('<i class="ti ti-alert-circle me-1"></i>An error occurred. Please try again.').addClass('text-warning');
                });
        });

        // View ticket details
        function handleViewTicket() {
            const ticketId = $(this).data('id');
            const $details = $('#ticketDetails');

            $details.html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

            fetch('/hrms/api/api_support.php?action=get_ticket&id=' + ticketId)
                .then(r => r.json())
                .then(res => {
                    if (res && res.success && res.data) {
                        const ticket = res.data;
                        const statusBadgeClass = {
                            'open': 'text-bg-info',
                            'in_progress': 'text-bg-warning',
                            'closed': 'text-bg-success'
                        }[ticket.status] || 'text-bg-secondary';

                        const priorityBadgeClass = {
                            'low': 'text-bg-success',
                            'medium': 'text-bg-warning',
                            'high': 'text-bg-danger'
                        }[ticket.priority] || 'text-bg-secondary';

                        let html = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Ticket ID</h6>
                                <p class="fw-bold">#${ticket.id}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Status</h6>
                                <span class="badge ${statusBadgeClass}">${ticket.status.replace('_', ' ').toUpperCase()}</span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Priority</h6>
                                <span class="badge ${priorityBadgeClass}">${ticket.priority.toUpperCase()}</span>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Last Updated</h6>
                                <p class="mb-0">${new Date(ticket.updated_at).toLocaleString()}</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted">Subject</h6>
                            <p class="fw-bold">${ticket.subject}</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted">Message</h6>
                            <p class="border-start ps-3">${ticket.message}</p>
                        </div>
                        
                        <div class="row text-muted small">
                            <div class="col-md-6">
                                <h6>Created</h6>
                                <p>${new Date(ticket.created_at).toLocaleString()}</p>
                            </div>
                        </div>
                    `;

                        $details.html(html);
                    } else {
                        $details.html('<div class="alert alert-warning">Unable to load ticket details. Please try again.</div>');
                    }
                })
                .catch(err => {
                    $details.html('<div class="alert alert-warning">An error occurred while loading the ticket.</div>');
                });
        }

        // View ticket details
        $(document).on('click', '.view-ticket-btn', handleViewTicket);
    });
</script>