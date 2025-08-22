<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Support Center";

// Security Check: Ensure the user is logged in
if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}

$is_super_admin = ($_SESSION['role_id'] === 1);

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-circle-question me-2"></i>Support Center</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                <i class="fas fa-plus me-2"></i>Create New Ticket
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">
                    <?= $is_super_admin ? "All Support Tickets" : "Your Support Tickets" ?>
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="ticketsTable">
                        <thead class="table">
                            <tr>
                                <th>Subject</th>
                                <?php if ($is_super_admin): ?>
                                    <th>Submitted By</th>
                                <?php endif; ?>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody><!-- Data will be populated by DataTables --></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Ticket Modal -->
<div class="modal fade" id="newTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="newTicketForm">
                <div class="modal-header">
                    <h5 class="modal-title">Submit a Support Ticket</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="submit_ticket">
                    <div class="mb-3"><label for="subject" class="form-label">Subject</label><input type="text"
                            class="form-control" name="subject" required></div>
                    <div class="mb-3"><label for="message" class="form-label">Message</label><textarea
                            class="form-control" name="message" rows="5" required></textarea></div>
                    <div class="mb-3"><label for="priority" class="form-label">Priority</label><select
                            class="form-select" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Submit
                        Ticket</button></div>
            </form>
        </div>
    </div>
</div>

<!-- View/Update Ticket Modal (for Admins) -->
<?php if ($is_super_admin): ?>
    <div class="modal fade" id="viewTicketModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="updateStatusForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewTicketSubject"></h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="ticket_id" id="viewTicketId">
                        <p><strong>Submitted by:</strong> <span id="viewTicketUser"></span></p>
                        <p><strong>Message:</strong></p>
                        <p class="bg-light p-3 rounded" id="viewTicketMessage"></p>
                        <div class="mb-3"><label for="status" class="form-label"><strong>Update
                                    Status</strong></label><select class="form-select" name="status" id="viewTicketStatus">
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="closed">Closed</option>
                            </select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save
                            Changes</button></div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
        const isSuperAdmin = <?= json_encode($is_super_admin) ?>;
        let ticketsTable;
        const newTicketModal = new bootstrap.Modal(document.getElementById('newTicketModal'));
        const viewTicketModal = isSuperAdmin ? new bootstrap.Modal(document.getElementById('viewTicketModal')) : null;

        // Define columns based on user role
        const columns = [
            { data: 'subject', render: (d) => `<strong>${escapeHTML(d)}</strong>` },
        ];
        if (isSuperAdmin) {
            columns.push({ data: 'username', render: (d) => escapeHTML(d) });
        }
        columns.push(
            { data: 'priority', render: (d) => `<span class="badge text-bg-${getPriorityClass(d)}">${capitalize(d)}</span>` },
            { data: 'status', render: (d) => `<span class="badge text-bg-${getStatusClass(d)}">${capitalize(d.replace('_', ' '))}</span>` },
            { data: 'updated_at', render: (d) => new Date(d).toLocaleString() },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    if (isSuperAdmin) {
                        return `<button class="btn btn-sm btn-outline-primary" onclick="viewTicket(${row.id})">View / Update</button>`;
                    }
                    return `<button class="btn btn-sm btn-outline-secondary" disabled>No Actions</button>`;
                }
            }
        );

        // Initialize DataTable
        ticketsTable = $('#ticketsTable').DataTable({
            processing: true,
            serverSide: false, // We'll load all data at once
            ajax: {
                url: '/hrms/api/api_support.php?action=get_tickets',
                dataSrc: 'data'
            },
            columns: columns,
            order: [[isSuperAdmin ? 4 : 3, 'desc']] // Order by updated_at
        });

        // Handle new ticket submission
        $('#newTicketForm').on('submit', function (e) {
            e.preventDefault();
            fetch('/hrms/api/api_support.php', { method: 'POST', body: new FormData(this) })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        newTicketModal.hide();
                        ticketsTable.ajax.reload(); // Refresh the table
                    } else {
                        showToast(data.message, 'error');
                    }
                });
        });

        // Handle status update submission (for admins)
        if (isSuperAdmin) {
            $('#updateStatusForm').on('submit', function (e) {
                e.preventDefault();
                fetch('/hrms/api/api_support.php', { method: 'POST', body: new FormData(this) })
                    .then(res => res.json()).then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            viewTicketModal.hide();
                            ticketsTable.ajax.reload();
                        } else {
                            showToast(data.message, 'error');
                        }
                    });
            });
        }
    });

    // --- Helper Functions ---

    function viewTicket(ticketId) {
        // Find the ticket data from the DataTable's cache
        const ticketData = $('#ticketsTable').DataTable().rows().data().toArray().find(t => t.id == ticketId);
        if (ticketData) {
            $('#viewTicketId').val(ticketData.id);
            $('#viewTicketSubject').text(ticketData.subject);
            $('#viewTicketUser').text(ticketData.username);
            $('#viewTicketMessage').text(ticketData.message);
            $('#viewTicketStatus').val(ticketData.status);
            new bootstrap.Modal(document.getElementById('viewTicketModal')).show();
        }
    }

    function getStatusClass(status) {
        switch (status) {
            case 'open': return 'primary';
            case 'in_progress': return 'warning';
            case 'closed': return 'secondary';
            default: return 'light';
        }
    }

    function getPriorityClass(priority) {
        switch (priority) {
            case 'high': return 'danger';
            case 'medium': return 'info';
            case 'low': return 'success';
            default: return 'light';
        }
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
</script>