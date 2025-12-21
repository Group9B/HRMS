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

// Get stats for dashboard
$statsData = [
    'departments' => query($mysqli, "SELECT COUNT(*) as count FROM departments WHERE company_id = ?", [$company_id])['data'][0]['count'] ?? 0,
    'designations' => query($mysqli, "SELECT COUNT(*) as count FROM designations dsg JOIN departments d ON dsg.department_id = d.id WHERE d.company_id = ?", [$company_id])['data'][0]['count'] ?? 0,
    'teams' => query($mysqli, "SELECT COUNT(*) as count FROM teams WHERE company_id = ?", [$company_id])['data'][0]['count'] ?? 0,
    'shifts' => query($mysqli, "SELECT COUNT(*) as count FROM shifts WHERE company_id = ?", [$company_id])['data'][0]['count'] ?? 0,
];

// Get department-wise employee count
$deptEmployees = query($mysqli, "SELECT d.name, COUNT(e.id) as count FROM departments d LEFT JOIN employees e ON d.id = e.department_id WHERE d.company_id = ? GROUP BY d.id, d.name ORDER BY d.name", [$company_id])['data'] ?? [];

// Get shift-wise employee count
$shiftEmployees = query($mysqli, "SELECT s.name, COUNT(e.id) as count FROM shifts s LEFT JOIN employees e ON s.id = e.shift_id WHERE s.company_id = ? GROUP BY s.id, s.name ORDER BY s.name", [$company_id])['data'] ?? [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <ul class="nav nav-tabs mb-3" id="orgTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab"
                    data-bs-target="#dashboardSection" type="button" role="tab">
                    <i class="ti ti-chart-line me-2"></i> Dashboard
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="departments-tab" data-bs-toggle="tab" data-bs-target="#departmentsSection"
                    type="button" role="tab">
                    <i class="ti ti-building me-2"></i> Departments
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="designations-tab" data-bs-toggle="tab"
                    data-bs-target="#designationsSection" type="button" role="tab">
                    <i class="ti ti-id-badge me-2"></i> Designations
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="teams-tab" data-bs-toggle="tab" data-bs-target="#teamsSection"
                    type="button" role="tab">
                    <i class="ti ti-users me-2"></i> Teams
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="shifts-tab" data-bs-toggle="tab" data-bs-target="#shiftsSection"
                    type="button" role="tab">
                    <i class="ti ti-clock me-2"></i> Shifts
                </button>
            </li>
        </ul>

        <div class="tab-content" id="orgTabContent">
            <!-- Dashboard Tab -->
            <div id="dashboardSection" class="tab-pane fade show active" role="tabpanel">
                <!-- Overview Cards -->
                <div class="row mb-4">
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Departments</p>
                                        <h3 class="mb-0"><?php echo $statsData['departments']; ?></h3>
                                    </div>
                                    <i class="ti ti-building text-primary" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Designations</p>
                                        <h3 class="mb-0"><?php echo $statsData['designations']; ?></h3>
                                    </div>
                                    <i class="ti ti-id-badge text-success" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Teams</p>
                                        <h3 class="mb-0"><?php echo $statsData['teams']; ?></h3>
                                    </div>
                                    <i class="ti ti-users text-info" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Shifts</p>
                                        <h3 class="mb-0"><?php echo $statsData['shifts']; ?></h3>
                                    </div>
                                    <i class="ti text-warning ti-clock" style="font-size: 2rem; opacity: 0.2;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header border-bottom">
                                <h5 class="mb-0">Employees by Department</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="deptChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header border-bottom">
                                <h5 class="mb-0">Shift Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="shiftChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="departmentsSection" class="tab-pane fade" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0">Departments</h5>
                            <button class="btn btn-primary btn-sm" onclick="prepareAddModal('department')">
                                <i class="ti ti-plus me-2"></i><span class="d-none d-sm-inline">Add</span>
                                Department
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
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
                </div>
            </div>

            <div id="designationsSection" class="tab-pane fade" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0">Designations</h5>
                            <button class="btn btn-primary btn-sm" onclick="prepareAddModal('designation')">
                                <i class="ti ti-plus me-2"></i><span class="d-none d-sm-inline">Add</span>
                                Designation
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($departments)): ?>
                            <div class="alert alert-warning alert-dismissible fade show">
                                <strong>No Departments Found</strong>
                                <p>Please add departments in Organization Management section first.</p>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="designationsTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="d-none d-lg-table-cell">Department</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div id="teamsSection" class="tab-pane fade" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0">Teams</h5>
                            <button class="btn btn-primary btn-sm" onclick="prepareAddModal('team')">
                                <i class="ti ti-plus me-2"></i><span class="d-none d-sm-inline">Add</span> Team
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="teamsTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="text-center d-none d-md-table-cell">Members</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div id="shiftsSection" class="tab-pane fade" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header border-bottom">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0">Shift Management</h5>
                            <button class="btn btn-primary btn-sm" onclick="prepareAddModal('shift')">
                                <i class="ti ti-plus me-2"></i><span class="d-none d-sm-inline">Add</span> Shift
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="shiftsTable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Shift Name</th>
                                        <th class="d-none d-md-table-cell">Time Range</th>
                                        <th class="text-center d-none d-lg-table-cell">Duration</th>
                                        <th class="text-center d-none d-md-table-cell">Type</th>
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


    <style>
        .section-content {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Hide number input spinner buttons */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>

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
        let currentSection = 'departments';
        const loadedTabs = new Set(['dashboard']); // Track which tabs have been loaded

        $(document).ready(function () {
            modals.org = new bootstrap.Modal(document.getElementById('orgModal'));
            modals.members = new bootstrap.Modal(document.getElementById('manageMembersModal'));

            // Initialize only dashboard charts initially
            initializeCharts();

            $('#orgForm').on('submit', handleFormSubmit);
            $('#addMemberForm').on('submit', handleAddMember);

            // Handle tab switching for lazy loading
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function (e) {
                    const target = e.target.getAttribute('data-bs-target');
                    const sectionId = target.replace('#', '').replace('Section', '');
                    
                    // Load table data only if not already loaded
                    if (!loadedTabs.has(sectionId)) {
                        loadedTabs.add(sectionId);
                        initializeTableForSection(sectionId);
                    } else if (tables[sectionId]) {
                        // Reload data to get fresh content
                        tables[sectionId].ajax.reload(null, false);
                    }
                    
                    // Adjust table columns
                    if (tables[sectionId]) {
                        tables[sectionId].columns.adjust();
                    }
                    
                    // Trigger chart resize on tab show
                    if (sectionId === 'dashboard') {
                        window.deptChart?.resize();
                        window.shiftChart?.resize();
                    }
                });
            });
        });

        function initializeTableForSection(sectionId) {
            if (sectionId === 'dashboard') return; // Dashboard doesn't need a table
            
            const type = sectionId.replace('s', '').replace('Designation', 'designation').replace('Team', 'team').replace('Shift', 'shift').replace('Department', 'department');
            // Map sectionId to type
            const typeMap = {
                'department': 'department',
                'designation': 'designation',
                'team': 'team',
                'shift': 'shift'
            };
            
            const actualType = Object.keys(typeMap).find(key => key === sectionId.replace(/s$/, '').replace('s', ''));
            if (!actualType) {
                const simplified = sectionId === 'departments' ? 'department' : 
                                  sectionId === 'designations' ? 'designation' : 
                                  sectionId === 'teams' ? 'team' : 'shift';
                initTableForType(simplified);
            } else {
                initTableForType(actualType);
            }
        }

        function initTableForType(type) {
            if (tables[type]) return; // Already initialized
            
            const columns = getTableColumns(type);
            tables[type] = $(`#${type}sTable`).DataTable({
                responsive: true,
                autoWidth: false,
                ajax: { url: `/hrms/api/organization.php?action=get_${type}s`, dataSrc: 'data' },
                columns: columns,
                pageLength: 10,
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip'
            });
        }

        function getTableColumns(type) {
            const actions = (d, t, r) => {
                return createActionDropdown(
                    {
                        onEdit: () => prepareEditModal(type, r),
                        onDelete: () => deleteItem(type, r.id),
                        ...(type === 'team' && { onManage: () => openManageMembersModal(r.id, r.name) })
                    },
                    {
                        editTooltip: 'Edit',
                        deleteTooltip: 'Delete',
                        ...(type === 'team' && { manageTooltip: 'Manage Members' })
                    }
                );
            };

            const shiftTypeColor = (start, end) => {
                const parseMin = (t) => {
                    const [h, m] = t.split(':').map(Number);
                    return h * 60 + m;
                };
                const startMin = parseMin(start);
                const endMin = parseMin(end);

                if (endMin < startMin) return { type: 'Overnight', color: 'warning' };
                if (startMin >= 18 * 60 || endMin <= 6 * 60) return { type: 'Night', color: 'danger' };
                return { type: 'Day', color: 'success' };
            };

            switch (type) {
                case 'department':
                    return [
                        { data: 'name', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong><br><small class="text-muted d-none d-md-block">${escapeHTML(r.description || '')}</small>` },
                        { data: null, orderable: false, searchable: false, className: 'text-end', width: '15%', render: actions }
                    ];
                case 'designation':
                    return [
                        { data: 'name', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong><br><small class="text-muted d-none d-md-block">${escapeHTML(r.description || '')}</small>` },
                        { data: 'department_name', className: 'd-none d-lg-table-cell' },
                        { data: null, orderable: false, searchable: false, className: 'text-end', width: '15%', render: actions }
                    ];
                case 'team':
                    return [
                        { data: 'name', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong><br><small class="text-muted d-none d-md-block">${escapeHTML(r.description || '')}</small>` },
                        { data: 'member_count', className: 'text-center', render: (d) => `<span class="badge bg-info-subtle text-info">${d || 0} members</span>` },
                        { data: null, orderable: false, searchable: false, className: 'text-end', width: '15%', render: actions }
                    ];
                case 'shift':
                    return [
                        { data: 'name', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong>` },
                        { data: null, render: (d, t, r) => `<span class="text-muted">${formatTime(r.start_time)} → ${formatTime(r.end_time)}</span>` },
                        {
                            data: null, render: (d, t, r) => {
                                const parseMin = (t) => {
                                    const [h, m] = t.split(':').map(Number);
                                    return h * 60 + m;
                                };
                                let duration = parseMin(r.end_time) - parseMin(r.start_time);
                                if (duration < 0) duration += 24 * 60;
                                const h = Math.floor(duration / 60);
                                const m = duration % 60;
                                return `<span class="badge bg-secondary-subtle text-secondary text-center">${h}h ${m}m</span>`;
                            }
                        },
                        {
                            data: null, render: (d, t, r) => {
                                const shiftInfo = shiftTypeColor(r.start_time, r.end_time);
                                const colorMap = { 'Day': 'success-subtle text-success', 'Night': 'danger-subtle text-danger', 'Overnight': 'warning-subtle text-warning' };
                                const badgeClass = colorMap[shiftInfo.type] || 'secondary-subtle text-secondary';
                                return `<span class="badge bg-${badgeClass}">${shiftInfo.type}</span>`;
                            }
                        },
                        { data: null, orderable: false, searchable: false, className: 'text-end', width: '15%', render: actions }
                    ];
            }
        }

        function getFormFields(type, data = {}) {
            const validationGuide = `
                <div class="alert alert-info-subtle border border-info-subtle rounded mb-3">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="ti ti-info-circle text-info me-2 mt-1" style="font-size: 1.1rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="alert-heading text-info mb-2">Field Requirements</h6>
                            <ul class="mb-0 small">
                                <li class="mb-2"><span class="text-info fw-bold">Name:</span> 2-100 characters, letters & spaces only <span class="badge bg-danger-subtle text-danger">No Numbers</span></li>
                                <li class="mb-2"><span class="text-info fw-bold">Special Characters:</span> Only <code class="text-info">' - .</code> allowed</li>
                                <li><span class="text-info fw-bold">Description:</span> <span class="text-muted">Optional, max 500 characters</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;

            const nameField = `<div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="${escapeHTML(data.name || '')}" placeholder="e.g., Human Resources, IT-Support" maxlength="100" required>
            </div>`;
            const descField = `<div class="mb-3">
                <label class="form-label">Description <span class="text-muted">(Optional)</span></label>
                <textarea class="form-control" name="description" rows="3" placeholder="Add any additional details..." maxlength="500">${escapeHTML(data.description || '')}</textarea>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-info-emphasis fw-lighter"><i class="ti ti-info-circle"></i> <span id="charCount">0</span>/500 characters</small>
                </div>
            </div>`;

            let deptOptions = '<option value="">-- Select --</option>';
            departmentsData.forEach(dept => {
                const selected = data.department_id == dept.id ? 'selected' : '';
                deptOptions += `<option value="${dept.id}" ${selected}>${escapeHTML(dept.name)}</option>`;
            });
            const deptField = `<div class="mb-3"><label class="form-label">Department <span class="text-danger">*</span></label><select class="form-select" name="department_id" ${departmentsData.length === 0 ? 'disabled' : ''} required>${deptOptions}</select></div>`;

            // Convert 24-hour time to 12-hour format for display
            const convertTo12Hour = (time24) => {
                if (!time24) return { hours: '09', minutes: '00', period: 'AM' };
                const [h, m] = time24.split(':');
                const hours = parseInt(h, 10);
                const period = hours >= 12 ? 'PM' : 'AM';
                const hours12 = hours % 12 || 12;
                return {
                    hours: String(hours12).padStart(2, '0'),
                    minutes: m,
                    period: period
                };
            };

            const startTime = convertTo12Hour(data.start_time);
            const endTime = convertTo12Hour(data.end_time);

            const timeFields = `
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Start Time <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control text-center" name="start_hours" min="1" max="12" value="${startTime.hours}" placeholder="HH" required style="max-width: 80px;">
                            <span class="input-group-text">:</span>
                            <input type="number" class="form-control text-center" name="start_minutes" min="0" max="59" value="${startTime.minutes}" placeholder="MM" required style="max-width: 80px;">
                            <select class="form-select" name="start_period" style="max-width: 100px;">
                                <option value="AM" ${startTime.period === 'AM' ? 'selected' : ''}>AM</option>
                                <option value="PM" ${startTime.period === 'PM' ? 'selected' : ''}>PM</option>
                            </select>
                        </div>
                        <small class="form-text text-muted">Enter time in 12-hour format</small>
                        <input type="hidden" name="start_time" id="start_time_24h">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">End Time <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control text-center" name="end_hours" min="1" max="12" value="${endTime.hours}" placeholder="HH" required style="max-width: 80px;">
                            <span class="input-group-text">:</span>
                            <input type="number" class="form-control text-center" name="end_minutes" min="0" max="59" value="${endTime.minutes}" placeholder="MM" required style="max-width: 80px;">
                            <select class="form-select" name="end_period" style="max-width: 100px;">
                                <option value="AM" ${endTime.period === 'AM' ? 'selected' : ''}>AM</option>
                                <option value="PM" ${endTime.period === 'PM' ? 'selected' : ''}>PM</option>
                            </select>
                        </div>
                        <small class="form-text text-muted">Enter time in 12-hour format</small>
                        <input type="hidden" name="end_time" id="end_time_24h">
                    </div>
                </div>
            `;

            switch (type) {
                case 'department': return nameField + descField;
                case 'designation': return nameField + deptField + descField;
                case 'team': return nameField + descField;
                case 'shift': return nameField + timeFields + descField;
            }
        }

        function prepareAddModal(type) {
            if (type === 'designation' && departmentsData.length === 0) {
                showToast('Please add at least one department first.', 'error');
                return;
            }
            $('#orgForm').trigger("reset");
            $('#orgModalLabel').text(`Add ${capitalize(type)}`);
            $('#orgAction').val(`add_edit_${type}`);
            $('#orgId').val('0');
            $('#form-fields').html(getFormFields(type));
            initializeCharCounter();
            modals.org.show();
        }

        function prepareEditModal(type, data) {
            $('#orgForm').trigger("reset");
            $('#orgModalLabel').text(`Edit ${capitalize(type)}`);
            $('#orgAction').val(`add_edit_${type}`);
            $('#orgId').val(data.id);
            $('#form-fields').html(getFormFields(type, data));
            initializeCharCounter();
            modals.org.show();
        }

        function initializeCharCounter() {
            const descField = $('[name="description"]');
            if (descField.length) {
                const currentLength = descField.val().length;
                $('#charCount').text(currentLength);
            }
        }

        function validateOrgName(name, fieldName = 'Name') {
            if (!name || name.trim().length === 0) return `${fieldName} is required.`;
            if (name.length < 2) return `${fieldName} must be at least 2 characters long.`;
            if (name.length > 100) return `${fieldName} must not exceed 100 characters.`;
            if (/\d/.test(name)) return `${fieldName} cannot contain numbers.`;
            if (!/^[a-zA-Z\s.'-]+$/.test(name)) return `${fieldName} can only contain letters, spaces, hyphens, apostrophes, and periods.`;
            return null;
        }

        function validateOrgDescription(desc, maxLength = 500) {
            if (desc && desc.length > maxLength) return `Description must not exceed ${maxLength} characters.`;
            return null;
        }

        function validateOrgTime(time) {
            if (!time || !/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(time)) {
                return 'Time must be in HH:MM format (24-hour).';
            }
            return null;
        }

        function handleFormSubmit(e) {
            e.preventDefault();
            const form = $(this);
            const type = $('#orgAction').val().replace('add_edit_', '');
            const name = form.find('[name="name"]').val().trim();

            // Validate name
            const nameError = validateOrgName(name, capitalize(type) + ' name');
            if (nameError) {
                showToast(nameError, 'error');
                return;
            }

            // Validate description
            const description = form.find('[name="description"]').val().trim();
            const descError = validateOrgDescription(description);
            if (descError) {
                showToast(descError, 'error');
                return;
            }

            // Convert 12-hour time to 24-hour format for shifts
            if (type === 'shift') {
                const convertTo24Hour = (hours, minutes, period) => {
                    let h = parseInt(hours, 10);
                    if (period === 'AM' && h === 12) h = 0;
                    if (period === 'PM' && h !== 12) h += 12;
                    return `${String(h).padStart(2, '0')}:${String(parseInt(minutes, 10)).padStart(2, '0')}`;
                };

                const startHours = form.find('[name="start_hours"]').val();
                const startMinutes = form.find('[name="start_minutes"]').val();
                const startPeriod = form.find('[name="start_period"]').val();

                const endHours = form.find('[name="end_hours"]').val();
                const endMinutes = form.find('[name="end_minutes"]').val();
                const endPeriod = form.find('[name="end_period"]').val();

                // Validate time inputs
                if (!startHours || !startMinutes || !endHours || !endMinutes) {
                    showToast('Please enter all time values.', 'error');
                    return;
                }

                if (parseInt(startHours) < 1 || parseInt(startHours) > 12) {
                    showToast('Start hours must be between 1 and 12.', 'error');
                    return;
                }

                if (parseInt(endHours) < 1 || parseInt(endHours) > 12) {
                    showToast('End hours must be between 1 and 12.', 'error');
                    return;
                }

                if (parseInt(startMinutes) < 0 || parseInt(startMinutes) > 59) {
                    showToast('Start minutes must be between 0 and 59.', 'error');
                    return;
                }

                if (parseInt(endMinutes) < 0 || parseInt(endMinutes) > 59) {
                    showToast('End minutes must be between 0 and 59.', 'error');
                    return;
                }

                const start24 = convertTo24Hour(startHours, startMinutes, startPeriod);
                const end24 = convertTo24Hour(endHours, endMinutes, endPeriod);

                form.find('[name="start_time"]').val(start24);
                form.find('[name="end_time"]').val(end24);
            }

            fetch('/hrms/api/organization.php', { method: 'POST', body: new FormData(this) })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        modals.org.hide();
                        if (type === 'department') {
                            refreshDepartments();
                            tables.designation.ajax.reload();
                        }
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
                                if (type === 'department') {
                                    refreshDepartments();
                                    tables.designation.ajax.reload();
                                }
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
                            const li = `<li class="list-group-item d-flex justify-content-between align-items-center">${escapeHTML(member.first_name + ' ' + member.last_name)}<button class="btn btn-sm btn-outline-danger" onclick="removeMember(${member.id}, ${teamId}, '${teamName}')"><i class="ti ti-x"></i></button></li>`;
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
                .then(result => { 
                    if (result.data) departmentsData = result.data;
                    // Reload designations table immediately if loaded
                    if (tables.designation && loadedTabs.has('designation')) {
                        tables.designation.ajax.reload(null, false);
                    }
                });
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

        function initializeCharts() {
            // Department-wise employees chart
            const deptData = <?php echo json_encode(['labels' => array_column($deptEmployees, 'name'), 'data' => array_column($deptEmployees, 'count')]); ?>;

            const deptCtx = document.getElementById('deptChart');
            if (deptCtx) {
                window.deptChart = new Chart(deptCtx, {
                    type: 'bar',
                    data: {
                        labels: deptData.labels.length > 0 ? deptData.labels : ['No Data'],
                        datasets: [{
                            label: 'Employees',
                            data: deptData.data,
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.2)',
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(255, 206, 86, 0.2)',
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(153, 102, 255, 0.2)',
                                'rgba(255, 159, 64, 0.2)'
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1.5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true },
                            tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.8)' }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });
            }

            // Shift distribution chart
            const shiftData = <?php echo json_encode(['labels' => array_column($shiftEmployees, 'name'), 'data' => array_column($shiftEmployees, 'count')]); ?>;

            const shiftCtx = document.getElementById('shiftChart');
            if (shiftCtx) {
                const colors = ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(255, 206, 86, 0.2)', 'rgba(54, 162, 235, 0.2)'];
                const borderColors = ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)', 'rgba(255, 206, 86, 1)', 'rgba(54, 162, 235, 1)'];

                window.shiftChart = new Chart(shiftCtx, {
                    type: 'doughnut',
                    data: {
                        labels: shiftData.labels.length > 0 ? shiftData.labels : ['No Data'],
                        datasets: [{
                            label: 'Employees',
                            data: shiftData.data,
                            backgroundColor: colors.slice(0, shiftData.data.length),
                            borderColor: borderColors.slice(0, shiftData.data.length),
                            borderWidth: 1.5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                display: true
                            },
                            tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.8)' }
                        }
                    }
                });
            }
        }
        // Character counter for description field
        $(document).on('input', '[name="description"]', function () {
            const length = $(this).val().length;
            const maxLength = 500;
            $('#charCount').text(length);

            // Update color based on usage
            const counter = $('#charCount');
            if (length > 400) {
                counter.removeClass('text-muted').addClass('text-danger');
            } else if (length > 300) {
                counter.removeClass('text-muted text-danger').addClass('text-warning');
            } else {
                counter.removeClass('text-danger text-warning').addClass('text-muted');
            }
        });
    </script>