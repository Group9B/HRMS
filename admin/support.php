<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Support Center";

// Security Check: Ensure the user is logged in
if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}

$is_super_admin = ($_SESSION['role_id'] === 1);

if (!$is_super_admin) {
    redirect("/hrms/pages/unauthorized.php");
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4 overflow-x-hidden" style="flex: 1;">

        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">
                    <?= $is_super_admin ? "All Support Tickets" : "Your Support Tickets" ?>
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive overflow-x-hidden">
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
                        <p class="bg-secondary-subtle p-3 rounded" id="viewTicketMessage"></p>
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
    // Global variables for modals
    let viewTicketModal;

    $(function () {
        const isSuperAdmin = <?= json_encode($is_super_admin) ?>;
        let ticketsTable;
        viewTicketModal = new bootstrap.Modal(document.getElementById('viewTicketModal'));

        const columns = [
            { data: 'subject', render: (d) => `<strong>${escapeHTML(d)}</strong>` },
        ];
        if (isSuperAdmin) {
            columns.push({ data: 'username', render: (d) => escapeHTML(d) });
        }
        columns.push(
            { data: 'priority', render: (d) => `<span class="badge bg-${getPriorityBgClass(d)} text-${getPriorityTextClass(d)}-emphasis">${capitalize(d)}</span>` },
            { data: 'status', render: (d) => `<span class="badge bg-${getStatusBgClass(d)} text-${getStatusTextClass(d)}-emphasis">${capitalize(d.replace('_', ' '))}</span>` },
            { data: 'updated_at', render: (d) => new Date(d).toLocaleString() },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    if (isSuperAdmin) {
                        return createActionDropdown({
                            onViewLink: () => viewTicket(row.id),
                            onEdit: () => editTicketStatus(row.id)
                        }, {
                            viewLinkTooltip: 'View Details',
                            editTooltip: 'Update Status',
                            viewLinkIcon: 'ti ti-eye',
                            editIcon: 'ti ti-edit'
                        });
                    }
                    return '';
                }
            }
        );

        // Initialize DataTable
        ticketsTable = $('#ticketsTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '/hrms/api/api_support.php?action=get_tickets',
                dataSrc: 'data'
            },
            columns: columns,
            order: [[isSuperAdmin ? 4 : 3, 'desc']]
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

    function viewTicket(ticketId) {
        const ticketData = $('#ticketsTable').DataTable().rows().data().toArray().find(t => t.id == ticketId);
        if (ticketData) {
            $('#viewTicketId').val(ticketData.id);
            $('#viewTicketSubject').text(ticketData.subject);
            $('#viewTicketUser').text(ticketData.username);
            $('#viewTicketMessage').text(ticketData.message);
            $('#viewTicketStatus').val(ticketData.status);
            viewTicketModal.show();
        }
    }

    function editTicketStatus(ticketId) {
        viewTicket(ticketId);
    }

    function getStatusBgClass(status) {
        switch (status) {
            case 'open': return 'info-subtle';
            case 'in_progress': return 'warning-subtle';
            case 'closed': return 'success-subtle';
            default: return 'secondary-subtle';
        }
    }

    function getStatusTextClass(status) {
        switch (status) {
            case 'open': return 'info';
            case 'in_progress': return 'warning';
            case 'closed': return 'success';
            default: return 'secondary';
        }
    }

    function getPriorityBgClass(priority) {
        switch (priority) {
            case 'high': return 'danger-subtle';
            case 'medium': return 'info-subtle';
            case 'low': return 'success-subtle';
            default: return 'secondary-subtle';
        }
    }

    function getPriorityTextClass(priority) {
        switch (priority) {
            case 'high': return 'danger';
            case 'medium': return 'info';
            case 'low': return 'success';
            default: return 'secondary';
        }
    }
</script>