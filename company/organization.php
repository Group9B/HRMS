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

                    <!-- Designations Tab -->
                    <div class="tab-pane fade" id="designations-tab" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="m-0 text-secondary">Manage Designations</h5>
                            <button class="btn btn-primary btn-sm" onclick="prepareAddModal('designation')">
                                <i class="fas fa-plus me-1"></i> Add Designation
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="designationsTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <!-- Teams Tab -->
                    <div class="tab-pane fade" id="teams-tab" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="m-0 text-secondary">Manage Teams</h5>
                            <button class="btn btn-primary btn-sm" onclick="prepareAddModal('team')">
                                <i class="fas fa-plus me-1"></i> Add Team
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="teamsTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="text-center">Members</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <!-- Shifts Tab -->
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
                    { data: null, className: 'd-none d-md-table-cell', width: '40%', render: (d, t, r) => renderShiftTimeline(r.start_time, r.end_time) },
                    { data: null, orderable: false, searchable: false, className: 'text-end', render: actions }
                ];
        }
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

        // Frontend Validation
        const form = $(this);
        const type = $('#orgAction').val().replace('add_edit_', '');
        const name = form.find('[name="name"]').val().trim();

        if (name.length < 2) {
            showToast('Name must be at least 2 characters long.', 'error');
            return;
        }

        if (type === 'designation') {
            const deptId = form.find('[name="department_id"]').val();
            if (!deptId) {
                showToast('Please select a department.', 'error');
                return;
            }
        }

        if (type === 'shift') {
            const startTime = form.find('[name="start_time"]').val();
            const endTime = form.find('[name="end_time"]').val();
            if (!startTime || !endTime) {
                showToast('Start and End times are required.', 'error');
                return;
            }
        }

        fetch('/hrms/api/organization.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    modals.org.hide();
                    if (type === 'department') {
                        refreshDepartments();
                    }
                    Object.values(tables).forEach(table => table.ajax.reload());
                } else { showToast(data.message, 'error'); }
            });
    }

    function deleteItem(type, id) {
        showConfirmationModal(
            `Are you sure you want to delete this ${type}? This action cannot be undone.`,
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
            `Delete ${capitalize(type)}`,
            'Delete',
            'btn-danger'
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
        const employeeId = form.find('[name="employee_id"]').val();

        if (!employeeId) {
            showToast('Please select an employee.', 'error');
            return;
        }

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
        showConfirmationModal(
            'Remove this member from the team?',
            () => {
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
            },
            'Remove Member',
            'Remove',
            'btn-danger'
        );
    }

    function renderShiftTimeline(start, end) {
        const parseMinutes = (t) => {
            const [h, m] = t.split(':').map(Number);
            return h * 60 + m;
        };
        const startMin = parseMinutes(start);
        const endMin = parseMinutes(end);
        const totalMin = 24 * 60;

        let duration = endMin - startMin;
        if (duration < 0) {
            duration = totalMin + duration;
        }

        const hours = Math.floor(duration / 60);
        const mins = duration % 60;
        const durationStr = mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;

        if (endMin >= startMin) {
            // Normal shift (e.g., 09:00 to 17:00)
            const left = (startMin / totalMin) * 100;
            const width = ((endMin - startMin) / totalMin) * 100;

            return `
                <div style="width: 100%; min-width: 340px;">
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 10px; align-items: center;">
                        <div>
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 2px;">Shift Hours</div>
                            <div style="font-size: 14px; font-weight: 600; color: #495057;">${formatTime(start)} <span style="color: #adb5bd;">→</span> ${formatTime(end)}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 2px;">Duration</div>
                            <div style="font-size: 14px; font-weight: 600; color: #495057;">${durationStr}</div>
                        </div>
                    </div>
                    <div style="position: relative; height: 32px; background: linear-gradient(90deg, #c0c1c2ff 0%, #565759ff 50%, #f8f9fa 100%); border-radius: 6px; overflow: hidden; border: 1px solid #dee2e6;">
                        <div style="position: absolute; top: 0; bottom: 0; background: linear-gradient(135deg, #e8f0fe 0%, #e0e7ff 100%); left: ${left}%; width: ${width}%; border-left: 2px solid #5f6edb; border-right: 2px solid #5f6edb; opacity: 0.8;"></div>
                        <div style="position: absolute; top: 0; bottom: 0; display: flex; align-items: center; left: ${left}%; width: ${width}%; padding: 0 8px; font-size: 11px; font-weight: 600; color: #5f6edb;"></div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 2px; margin-top: 6px; font-size: 9px; color: #adb5bd; text-align: center;">
                        <span>12 AM</span><span>6 AM</span><span>12 PM</span><span>6 PM</span><span>12 AM</span>
                    </div>
                </div>`;
        } else {
            // Overnight shift (e.g., 22:00 to 06:00)
            const left1 = (startMin / totalMin) * 100;
            const width1 = ((totalMin - startMin) / totalMin) * 100;
            const width2 = (endMin / totalMin) * 100;

            return `
                <div style="width: 100%; min-width: 340px;">
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; margin-bottom: 10px; align-items: center;">
                        <div>
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 2px;">Shift Hours <i class="fas fa-moon" style="color: #adb5bd; margin-left: 4px; font-size: 10px;"></i></div>
                            <div style="font-size: 14px; font-weight: 600; color: #495057;">${formatTime(start)} <span style="color: #adb5bd;">→</span> ${formatTime(end)}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 2px;">Duration</div>
                            <div style="font-size: 14px; font-weight: 600; color: #495057;">${durationStr}</div>
                        </div>
                    </div>
                    <div style="position: relative; height: 32px; background: linear-gradient(90deg, #f8f9fa 0%, #f1f3f5 50%, #f8f9fa 100%); border-radius: 6px; overflow: hidden; border: 1px solid #dee2e6;">
                        <div style="position: absolute; top: 0; bottom: 0; background: linear-gradient(135deg, #fef7e8 0%, #fff8e0 100%); left: ${left1}%; width: ${width1}%; border-left: 2px solid #b8860b; opacity: 0.8;"></div>
                        <div style="position: absolute; top: 0; bottom: 0; background: linear-gradient(135deg, #fef7e8 0%, #fff8e0 100%); left: 0; width: ${width2}%; border-right: 2px solid #b8860b; opacity: 0.8;"></div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 2px; margin-top: 6px; font-size: 9px; color: #adb5bd; text-align: center;">
                        <span>12 AM</span><span>6 AM</span><span>12 PM</span><span>6 PM</span><span>12 AM</span>
                    </div>
                </div>`;
        }
    }
    function refreshDepartments() {
        fetch('/hrms/api/organization.php?action=get_departments')
            .then(res => res.json())
            .then(result => {
                if (result.data) {
                    departmentsData = result.data;
                }
            });
    }

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