<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Organization Management";

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    redirect("/hrms/pages/unauthorized.php");
}
$company_id = $_SESSION['company_id'];

$departments = query($mysqli, "SELECT id, name FROM departments WHERE company_id = ? ORDER BY name ASC", [$company_id])['data'] ?? [];
$employees = query($mysqli, "SELECT e.id, e.first_name, e.last_name FROM employees e JOIN departments d ON e.department_id = d.id WHERE d.company_id = ? ORDER BY e.first_name ASC", [$company_id])['data'] ?? [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="card shadow-sm">
            <div class="card-header border-bottom-0 pb-0">
                <ul class="nav nav-tabs" id="orgTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active d-flex align-items-center gap-2 rounded-top" data-bs-toggle="tab"
                            data-bs-target="#departments-tab" type="button">
                            <i class="fas fa-building"></i> Departments
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-2  rounded-top" data-bs-toggle="tab"
                            data-bs-target="#designations-tab" type="button">
                            <i class="fas fa-id-badge"></i> Designations
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-2  rounded-top" data-bs-toggle="tab"
                            data-bs-target="#teams-tab" type="button">
                            <i class="fas fa-users"></i> Teams
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center gap-2  rounded-top" data-bs-toggle="tab"
                            data-bs-target="#shifts-tab" type="button">
                            <i class="fas fa-clock"></i> Shifts
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body pt-2">
                <div class="tab-content" id="orgTabsContent">
                    <!-- Departments Tab -->
                    <div class="tab-pane fade show active" id="departments-tab" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="m-0 text-secondary">Manage Departments</h5>
                            <button class="btn btn-primary btn-sm" onclick="prepareAddModal('department')">
                                <i class="fas fa-plus me-1"></i> Add Department
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="departmentsTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="shifts-tab" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="m-0 text-secondary">Manage Shifts</h5>
                            <button class="btn btn-primary btn-sm" onclick="prepareAddModal('shift')">
                                <i class="fas fa-plus me-1"></i> Add Shift
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="shiftsTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Time</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <style>
                        .shift-progress-container {
                            background: #f8f9fa;
                            border: 1px solid #dee2e6;
                            border-radius: 10px;
                            padding: 12px;
                            margin: 5px 0;
                            transition: all 0.3s ease;
                        }

                        [data-bs-theme="dark"] .shift-progress-container {
                            background: #2b3035;
                            border-color: #495057;
                        }

                        .shift-progress-bar {
                            height: 12px;
                            background: #e9ecef;
                            border-radius: 6px;
                            overflow: hidden;
                            position: relative;
                            margin: 8px 0;
                        }

                        [data-bs-theme="dark"] .shift-progress-bar {
                            background: #1e2125;
                        }

                        .shift-progress-fill {
                            height: 100%;
                            width: 0%;
                            background: linear-gradient(90deg, #0d6efd 0%, #0dcaf0 100%);
                            border-radius: 6px;
                            transition: width 1s cubic-bezier(0.1, 0.7, 1.0, 0.1);
                        }

                        .shift-status-badge {
                            font-size: 10px;
                            padding: 3px 8px;
                            border-radius: 20px;
                            font-weight: 600;
                            text-transform: uppercase;
                        }

                        .status-ongoing {
                            background: rgba(25, 135, 84, 0.15);
                            color: #198754;
                        }

                        .status-upcoming {
                            background: rgba(108, 117, 125, 0.15);
                            color: #6c757d;
                        }

                        .status-finished {
                            background: rgba(13, 110, 253, 0.15);
                            color: #0d6efd;
                        }
                    </style>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="orgModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="orgForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="orgModalLabel"></h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="orgAction">
                    <input type="hidden" name="id" id="orgId" value="0">
                    <div id="form-fields"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit"
                        class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="manageMembersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageMembersModalLabel"></h5><button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Add New Member</h6>
                <form id="addMemberForm" class="d-flex mb-3">
                    <input type="hidden" name="action" value="add_team_member">
                    <input type="hidden" name="team_id" id="addMemberTeamId">
                    <select class="form-select me-2" name="employee_id" required>
                        <option value="">-- Select Employee --</option><?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>">
                                <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
                <hr>
                <h6>Current Members</h6>
                <ul class="list-group" id="teamMemberList"></ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    const tables = {};
    const modals = {};
    let departmentsData = <?php echo json_encode($departments); ?>;

    $(document).ready(function () {
        modals.org = new bootstrap.Modal(document.getElementById('orgModal'));
        modals.members = new bootstrap.Modal(document.getElementById('manageMembersModal'));

        ['department', 'designation', 'team', 'shift'].forEach(type => {
            const columns = getTableColumns(type);
            tables[type] = $(`#${type}sTable`).DataTable({
                responsive: true,
                bautoWidth: false,
                ajax: { url: `/hrms/api/organization.php?action=get_${type}s`, dataSrc: 'data' },
                columns: columns,
                drawCallback: function () {
                    if (type === 'shift') {
                        updateLiveProgress();
                    }
                }
            });
        });

        $('#orgForm').on('submit', handleFormSubmit);
        $('#addMemberForm').on('submit', handleAddMember);

        // Update progress every minute
        setInterval(updateLiveProgress, 60000);
    });

    function getTableColumns(type) {
        const actions = (d, t, r) => `<div class="btn-group">
        ${type === 'team' ? `<button class="btn btn-sm btn-outline-info" title="Manage Members" onclick='openManageMembersModal(${r.id}, "${escapeHTML(r.name)}")'><i class="fas fa-users"></i></button>` : ''}
        <button class="btn btn-sm btn-outline-primary" title="Edit" onclick='prepareEditModal("${type}", ${JSON.stringify(r)})'><i class="fas fa-edit"></i></button>
        <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteItem('${type}', ${r.id})"><i class="fas fa-trash"></i></button>
    </div>`;

        switch (type) {
            case 'department':
                return [
                    { data: 'name', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong><br><small class="text-muted d-none d-md-block">${escapeHTML(r.description || '')}</small>` },
                    { data: null, orderable: false, searchable: false, className: 'text-end', render: actions }
                ];
            case 'designation':
                return [
                    { data: 'name', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong><br><small class="text-muted d-none d-md-block">${escapeHTML(r.description || '')}</small>` },
                    { data: 'department_name', className: 'd-none d-lg-table-cell' },
                    { data: null, orderable: false, searchable: false, className: 'text-end', render: actions }
                ];
            case 'team':
                return [
                    { data: 'name', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong><br><small class="text-muted d-none d-md-block">${escapeHTML(r.description || '')}</small>` },
                    { data: 'member_count', className: 'text-center' },
                    { data: null, orderable: false, searchable: false, className: 'text-end', render: actions }
                ];
            case 'shift':
                return [
                    { data: 'name', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong><br><small class="text-muted d-none d-md-block">${escapeHTML(r.description || '')}</small>` },
                    { data: null, className: 'd-none d-md-table-cell', width: '40%', render: (d, t, r) => renderShiftTimeline(r.start_time, r.end_time, r.id) },
                    { data: null, orderable: false, searchable: false, className: 'text-end', render: actions }
                ];
        }
    }

    function renderShiftTimeline(start, end, id) {
        const parseMin = (t) => {
            const [h, m] = t.split(':').map(Number);
            return h * 60 + m;
        };
        const startMin = parseMin(start);
        const endMin = parseMin(end);
        let duration = endMin - startMin;
        if (duration < 0) duration += 24 * 60;

        return `
            <div class="shift-wrapper" data-id="${id}" data-start="${start}" data-end="${end}" style="width: 100%; min-width: 320px;">
                <div class="shift-progress-container shadow-sm border">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="shift-status-badge status-upcoming">Upcoming</span>
                        <span class="text-muted status-text" style="font-size: 11px;">Hasn't started</span>
                    </div>
                    <div class="shift-progress-bar">
                        <div class="shift-progress-fill"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold d-block" style="font-size: 13px;">${formatTime(start)}</span>
                            <small class="text-muted" style="font-size: 9px; text-transform: uppercase;">Start</small>
                        </div>
                        <div class="text-center">
                             <small class="text-muted d-block" style="font-size: 9px; text-transform: uppercase;">Duration</small>
                             <span class="fw-bold" style="font-size: 11px;">${Math.floor(duration / 60)}h ${duration % 60}m</span>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold d-block" style="font-size: 13px;">${formatTime(end)}</span>
                            <small class="text-muted" style="font-size: 9px; text-transform: uppercase;">End</small>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function updateLiveProgress() {
        const now = new Date();
        const currentMin = now.getHours() * 60 + now.getMinutes();
        const totalDayMin = 24 * 60;

        $('.shift-wrapper').each(function () {
            const wrapper = $(this);
            const startStr = wrapper.data('start');
            const endStr = wrapper.data('end');

            const parseMin = (t) => {
                const [h, m] = t.split(':').map(Number);
                return h * 60 + m;
            };

            const startMin = parseMin(startStr);
            const endMin = parseMin(endStr);

            let duration = endMin - startMin;
            if (duration < 0) duration += totalDayMin;

            let elapsed = 0;
            let status = 'upcoming';
            let statusText = 'Hasn\'t started';

            const isEndBeforeStart = endMin < startMin;

            if (!isEndBeforeStart) {
                // Normal Intra-day
                if (currentMin < startMin) {
                    elapsed = 0;
                    status = 'upcoming';
                    statusText = `Starts in ${formatDuration(startMin - currentMin)}`;
                } else if (currentMin >= startMin && currentMin <= endMin) {
                    elapsed = currentMin - startMin;
                    status = 'ongoing';
                    statusText = `${Math.floor((elapsed / duration) * 100)}% Complete • ${formatDuration(endMin - currentMin)} left`;
                } else {
                    elapsed = duration;
                    status = 'finished';
                    statusText = 'Shift Finished';
                }
            } else {
                // Overnight
                if (currentMin >= startMin) {
                    // Before midnight
                    elapsed = currentMin - startMin;
                    status = 'ongoing';
                    statusText = `${Math.floor((elapsed / duration) * 100)}% Complete • ${formatDuration(totalDayMin - currentMin + endMin)} left`;
                } else if (currentMin <= endMin) {
                    // After midnight
                    elapsed = (totalDayMin - startMin) + currentMin;
                    status = 'ongoing';
                    statusText = `${Math.floor((elapsed / duration) * 100)}% Complete • ${formatDuration(endMin - currentMin)} left`;
                } else {
                    // Outside shift
                    elapsed = 0;
                    status = 'upcoming';
                    statusText = `Starts today at ${formatTime(startStr)}`;
                }
            }

            const progress = (elapsed / duration) * 100;
            wrapper.find('.shift-progress-fill').css('width', `${Math.max(0, Math.min(100, progress))}%`);

            // Update Status UI
            const badge = wrapper.find('.shift-status-badge');
            badge.removeClass('status-ongoing status-upcoming status-finished').addClass(`status-${status}`).text(status);
            wrapper.find('.status-text').text(statusText);
        });
    }

    function formatDuration(minTotal) {
        const h = Math.floor(minTotal / 60);
        const m = minTotal % 60;
        if (h > 0) return `${h}h ${m}m`;
        return `${m}m`;
    }

    function getFormFields(type, data = {}) {
        const nameField = `<div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" value="${escapeHTML(data.name || '')}" required></div>`;
        const descField = `<div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3">${escapeHTML(data.description || '')}</textarea></div>`;

        let deptOptions = '<option value="">-- Select --</option>';
        departmentsData.forEach(dept => {
            const selected = data.department_id == dept.id ? 'selected' : '';
            deptOptions += `<option value="${dept.id}" ${selected}>${escapeHTML(dept.name)}</option>`;
        });
        const deptField = `<div class="mb-3"><label class="form-label">Department <span class="text-danger">*</span></label><select class="form-select" name="department_id" required>${deptOptions}</select></div>`;

        const timeFields = `<div class="row"><div class="col-6"><div class="mb-3"><label class="form-label">Start Time <span class="text-danger">*</span></label><input type="time" class="form-control" name="start_time" value="${data.start_time || ''}" required></div></div><div class="col-6"><div class="mb-3"><label class="form-label">End Time <span class="text-danger">*</span></label><input type="time" class="form-control" name="end_time" value="${data.end_time || ''}" required></div></div></div>`;

        switch (type) {
            case 'department': return nameField + descField;
            case 'designation': return nameField + deptField + descField;
            case 'team': return nameField + descField;
            case 'shift': return nameField + timeFields + descField;
        }
    }

    function prepareAddModal(type) {
        $('#orgForm').trigger("reset");
        $('#orgModalLabel').text(`Add ${capitalize(type)}`);
        $('#orgAction').val(`add_edit_${type}`);
        $('#orgId').val('0');
        $('#form-fields').html(getFormFields(type));
        modals.org.show();
    }

    function prepareEditModal(type, data) {
        $('#orgForm').trigger("reset");
        $('#orgModalLabel').text(`Edit ${capitalize(type)}`);
        $('#orgAction').val(`add_edit_${type}`);
        $('#orgId').val(data.id);
        $('#form-fields').html(getFormFields(type, data));
        modals.org.show();
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        const form = $(this);
        const type = $('#orgAction').val().replace('add_edit_', '');
        const name = form.find('[name="name"]').val().trim();

        if (name.length < 2) {
            showToast('Name must be at least 2 characters long.', 'error');
            return;
        }

        fetch('/hrms/api/organization.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    modals.org.hide();
                    if (type === 'department') refreshDepartments();
                    Object.values(tables).forEach(table => table.ajax.reload());
                } else { showToast(data.message, 'error'); }
            });
    }

    function deleteItem(type, id) {
        showConfirmationModal(
            `Are you sure you want to delete this ${type}?`,
            () => {
                const formData = new FormData();
                formData.append('action', `delete_${type}`);
                formData.append('id', id);
                fetch('/hrms/api/organization.php', { method: 'POST', body: formData })
                    .then(res => res.json()).then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            tables[type].ajax.reload();
                        } else { showToast(data.message, 'error'); }
                    });
            },
            `Delete ${capitalize(type)}`
        );
    }

    function openManageMembersModal(teamId, teamName) {
        $('#manageMembersModalLabel').text(`Manage Members for: ${teamName}`);
        $('#addMemberTeamId').val(teamId);
        const memberList = $('#teamMemberList');
        memberList.html('<li class="list-group-item text-center"><div class="spinner-border spinner-border-sm"></div></li>');

        fetch(`/hrms/api/organization.php?action=get_team_members&team_id=${teamId}`)
            .then(res => res.json()).then(result => {
                memberList.empty();
                if (result.success && result.data.length > 0) {
                    result.data.forEach(member => {
                        const li = `<li class="list-group-item d-flex justify-content-between align-items-center">${escapeHTML(member.first_name + ' ' + member.last_name)}<button class="btn btn-sm btn-outline-danger" onclick="removeMember(${member.id}, ${teamId}, '${teamName}')"><i class="fas fa-times"></i></button></li>`;
                        memberList.append(li);
                    });
                } else {
                    memberList.append('<li class="list-group-item text-muted">No members in this team yet.</li>');
                }
            });
        modals.members.show();
    }

    function handleAddMember(e) {
        e.preventDefault();
        const form = $(this);
        const teamId = form.find('[name="team_id"]').val();
        const teamName = $('#manageMembersModalLabel').text().replace('Manage Members for: ', '');
        fetch('/hrms/api/organization.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    openManageMembersModal(teamId, teamName);
                    tables.team.ajax.reload();
                } else { showToast(data.message, 'error'); }
            });
    }

    function removeMember(memberId, teamId, teamName) {
        showConfirmationModal('Remove this member?', () => {
            const formData = new FormData();
            formData.append('action', 'remove_team_member');
            formData.append('member_id', memberId);
            fetch('/hrms/api/organization.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        openManageMembersModal(teamId, teamName);
                        tables.team.ajax.reload();
                    } else { showToast(data.message, 'error'); }
                });
        });
    }

    function refreshDepartments() {
        fetch('/hrms/api/organization.php?action=get_departments')
            .then(res => res.json())
            .then(result => { if (result.data) departmentsData = result.data; });
    }

    function formatTime(timeStr) {
        if (!timeStr) return '';
        const [h, m] = timeStr.split(':');
        let hours = parseInt(h, 10);
        const minutes = parseInt(m, 10);
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        return `${hours}:${minutes < 10 ? '0' + minutes : minutes} ${ampm}`;
    }
</script>