<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Organization Management";

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    redirect("/hrms/pages/unauthorized.php");
}
$company_id = $_SESSION['company_id'];

// Fetch data for modal dropdowns
$departments = query($mysqli, "SELECT id, name FROM departments WHERE company_id = ? ORDER BY name ASC", [$company_id])['data'] ?? [];
$employees = query($mysqli, "SELECT e.id, e.first_name, e.last_name FROM employees e JOIN departments d ON e.department_id = d.id WHERE d.company_id = ? ORDER BY e.first_name ASC", [$company_id])['data'] ?? [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <ul class="nav nav-tabs" id="orgTabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab"
                    data-bs-target="#departments-tab" type="button">Departments</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab"
                    data-bs-target="#designations-tab" type="button">Designations</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab"
                    data-bs-target="#teams-tab" type="button">Teams</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab"
                    data-bs-target="#shifts-tab" type="button">Shifts</button></li>
        </ul>

        <div class="tab-content" id="orgTabsContent">
            <div class="tab-pane fade show active" id="departments-tab" role="tabpanel">
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0">Manage Departments</h6><button class="btn btn-primary btn-sm"
                            onclick="prepareAddModal('department')"><i class="fas fa-plus me-1"></i> Add</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="departmentsTable" width="100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="designations-tab" role="tabpanel">
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0">Manage Designations</h6><button class="btn btn-primary btn-sm"
                            onclick="prepareAddModal('designation')"><i class="fas fa-plus me-1"></i> Add</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="designationsTable" width="100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="teams-tab" role="tabpanel">
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0">Manage Teams</h6><button class="btn btn-primary btn-sm"
                            onclick="prepareAddModal('team')"><i class="fas fa-plus me-1"></i> Add</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="teamsTable" width="100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Members</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="shifts-tab" role="tabpanel">
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0">Manage Shifts</h6><button class="btn btn-primary btn-sm"
                            onclick="prepareAddModal('shift')"><i class="fas fa-plus me-1"></i> Add</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="shiftsTable" width="100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
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
    let tables = {}, modals = {};

    $(function () {
        modals.org = new bootstrap.Modal('#orgModal');
        modals.members = new bootstrap.Modal('#manageMembersModal');

        ['department', 'designation', 'team', 'shift'].forEach(type => {
            const columns = getTableColumns(type);
            tables[type] = $(`#${type}sTable`).DataTable({
                responsive: true,
                ajax: { url: `/hrms/api/organization.php?action=get_${type}s`, dataSrc: 'data' },
                columns: columns
            });
        });

        $('#orgForm').on('submit', handleFormSubmit);
        $('#addMemberForm').on('submit', handleAddMember);
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
                    { data: null, className: 'd-none d-md-table-cell', render: (d, t, r) => `${formatTime(r.start_time)} - ${formatTime(r.end_time)}` },
                    { data: null, orderable: false, searchable: false, className: 'text-end', render: actions }
                ];
        }
    }

    function getFormFields(type, data = {}) {
        const nameField = `<div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" value="${escapeHTML(data.name || '')}" required></div>`;
        const descField = `<div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3">${escapeHTML(data.description || '')}</textarea></div>`;
        const deptField = `<div class="mb-3"><label class="form-label">Department <span class="text-danger">*</span></label><select class="form-select" name="department_id" required><option value="">-- Select --</option><?php foreach ($departments as $dept): ?><option value="<?= $dept['id'] ?>" ${data.department_id == <?= $dept['id'] ?> ? 'selected' : ''}>${'<?= htmlspecialchars($dept['name']) ?>'}</option><?php endforeach; ?></select></div>`;
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
        fetch('/hrms/api/organization.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    modals.org.hide();
                    Object.values(tables).forEach(table => table.ajax.reload());
                } else { showToast(data.message, 'error'); }
            });
    }

    function deleteItem(type, id) {
        if (confirm(`Are you sure you want to delete this ${type}?`)) {
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
        }
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
                    openManageMembersModal(teamId, teamName); // Refresh the member list
                    tables.team.ajax.reload(); // Reload the main teams table to update member count
                } else { showToast(data.message, 'error'); }
            });
    }

    function removeMember(memberId, teamId, teamName) {
        if (confirm('Remove this member from the team?')) {
            const formData = new FormData();
            formData.append('action', 'remove_team_member');
            formData.append('member_id', memberId);
            fetch('/hrms/api/organization.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        openManageMembersModal(teamId, teamName); // Refresh list
                        tables.team.ajax.reload(); // Reload main table
                    } else { showToast(data.message, 'error'); }
                });
        }
    }

    function capitalize(str) { return str.charAt(0).toUpperCase() + str.slice(1); }
    function formatTime(timeStr) {
        if (!timeStr) return '';
        const [h, m] = timeStr.split(':');
        const hours = parseInt(h, 10);
        const minutes = parseInt(m, 10);
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = hours % 12 || 12;
        const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
        return `${formattedHours}:${formattedMinutes} ${ampm}`;
    }
</script>