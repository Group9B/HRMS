<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Team Performance";

if (!isLoggedIn() || $_SESSION['role_id'] !== 6) {
    redirect("/hrms/pages/unauthorized.php");
}
$user_id = $_SESSION['user_id'];

// Get manager's department ID
$manager_info = query($mysqli, "SELECT department_id FROM employees WHERE user_id = ?", [$user_id]);
$manager_department_id = $manager_info['data'][0]['department_id'] ?? 0;

// Get team members for the filter and modal dropdowns
$team_members = query($mysqli, "
    SELECT e.id, e.first_name, e.last_name 
    FROM employees e
    JOIN team_members tm ON e.id = tm.employee_id
    WHERE tm.assigned_by = ? AND e.status = 'active' 
    ORDER BY e.first_name ASC
", [$user_id])['data'] ?? [];

// Get manager's teams for team performance
$teams = query($mysqli, "
    SELECT id, name, description 
    FROM teams 
    WHERE created_by = ? 
    ORDER BY name ASC
", [$user_id])['data'] ?? [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="h3 mb-0"><i class="ti ti-chart-line me-2"></i>Performance Management</h2>
            <button class="btn btn-primary" id="addReviewBtn" onclick="prepareAddModal()">
                <i class="ti ti-plus me-2"></i>Add Review
            </button>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="performanceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual"
                    type="button" role="tab" onclick="switchTab('individual')">
                    <i class="ti ti-user me-2"></i>Individual Performance
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="team-tab" data-bs-toggle="tab" data-bs-target="#team" type="button"
                    role="tab" onclick="switchTab('team')">
                    <i class="ti ti-users me-2"></i>Team Performance
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="performanceTabContent">
            <!-- Individual Performance Tab -->
            <div class="tab-pane fade show active" id="individual" role="tabpanel">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form id="filterForm" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="employee_filter" class="form-label">Employee</label>
                                <select class="form-select" id="employee_filter">
                                    <option value="">All Team Members</option>
                                    <?php foreach ($team_members as $member): ?>
                                        <option value="<?= $member['id'] ?>">
                                            <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="period_filter" class="form-label">Period</label>
                                <input type="month" class="form-control" id="period_filter" value="<?= date('Y-m') ?>">
                            </div>
                            <div class="col-md-2">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold">Performance Distribution</h6>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center"><canvas
                                    id="performanceChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-xl-8 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold">Performance Reviews</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="performanceTable" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Score</th>
                                                <th>Period</th>
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
            <!-- END Individual Performance Tab -->

            <!-- Team Performance Tab -->
            <div class="tab-pane fade" id="team" role="tabpanel">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form id="teamFilterForm" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="team_filter" class="form-label">Team</label>
                                <select class="form-select" id="team_filter">
                                    <option value="">All Teams</option>
                                    <?php foreach ($teams as $team): ?>
                                        <option value="<?= $team['id'] ?>">
                                            <?= htmlspecialchars($team['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="team_period_filter" class="form-label">Period</label>
                                <input type="month" class="form-control" id="team_period_filter"
                                    value="<?= date('Y-m') ?>">
                            </div>
                            <div class="col-md-2">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold">Team Performance Distribution</h6>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <canvas id="teamPerformanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-8 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold">Team Performance Reviews</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="teamPerformanceTable" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Team</th>
                                                <th>Members</th>
                                                <th>Score</th>
                                                <th>Period</th>
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
            <!-- END Team Performance Tab -->
        </div>
        <!-- END Tab Content -->
    </div>
</div>

<!-- Add/Edit Performance Modal -->
<div class="modal fade" id="performanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="performanceForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="performanceModalLabel"></h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="id" id="performanceId" value="0">
                    <div class="mb-3"><label class="form-label">Employee *</label><select class="form-select"
                            name="employee_id" required>
                            <option value="">-- Select --</option><?php foreach ($team_members as $member): ?>
                                <option value="<?= $member['id'] ?>">
                                    <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Period *</label><input type="month" class="form-control"
                            name="period" value="<?= date('Y-m') ?>" required></div>
                    <div class="mb-3"><label class="form-label">Score (0-100) *</label><input type="number"
                            class="form-control" name="score" min="0" max="100" required></div>
                    <div class="mb-3"><label class="form-label">Remarks</label><textarea class="form-control"
                            name="remarks" rows="4"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save
                        Review</button></div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Details</h5><button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody"></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

<!-- Team Performance Modal -->
<div class="modal fade" id="teamPerformanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="teamPerformanceForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="teamPerformanceModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="teamPerformanceId" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Team *</label>
                            <select class="form-select" name="team_id" required>
                                <option value="">-- Select Team --</option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?= $team['id'] ?>">
                                        <?= htmlspecialchars($team['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Period *</label>
                            <input type="month" class="form-control" name="period" value="<?= date('Y-m') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Overall Score (0-100) *</label>
                            <input type="number" class="form-control" name="score" min="0" max="100" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Collaboration Score (0-100)</label>
                            <input type="number" class="form-control" name="collaboration_score" min="0" max="100">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Achievements</label>
                        <textarea class="form-control" name="achievements" rows="3"
                            placeholder="List key achievements and milestones..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Challenges</label>
                        <textarea class="form-control" name="challenges" rows="3"
                            placeholder="Describe challenges faced and how they were handled..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">General Remarks</label>
                        <textarea class="form-control" name="remarks" rows="4"
                            placeholder="Overall assessment and recommendations..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Team Review</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once '../components/layout/footer.php'; ?>

<script>
    let performanceTable, teamPerformanceTable, performanceChart, teamPerformanceChart;
    let performanceModal, teamPerformanceModal, viewModal;
    let currentTab = 'individual';

    $(function () {
        performanceModal = new bootstrap.Modal('#performanceModal');
        teamPerformanceModal = new bootstrap.Modal('#teamPerformanceModal');
        viewModal = new bootstrap.Modal('#viewModal');

        performanceTable = $('#performanceTable').DataTable({
            responsive: true,
            processing: true,
            data: [],
            columns: [
                { data: 'first_name', render: (d, t, r) => `${escapeHTML(d)} ${escapeHTML(r.last_name)}` },
                { data: 'score', render: d => `<span class="badge bg-${getScoreColor(d)}">${d}</span>` },
                { data: 'period' },
                {
                    data: null, orderable: false, render: (d, t, r) => `
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-info" onclick="viewPerformance(${r.id})"><i class="ti ti-eye"></i></button>
                    <button class="btn btn-outline-primary" onclick="prepareEditModal(${r.id})"><i class="ti ti-edit"></i></button>
                    <button class="btn btn-outline-danger" onclick="deletePerformance(${r.id})"><i class="ti ti-trash"></i></button>
                </div>`
                }]
        });

        teamPerformanceTable = $('#teamPerformanceTable').DataTable({
            responsive: true,
            processing: true,
            data: [],
            columns: [
                { data: 'team_name' },
                { data: 'member_count', render: d => `${d || 0} members` },
                { data: 'score', render: d => `<span class="badge bg-${getScoreColor(d)}">${d}</span>` },
                { data: 'period' },
                {
                    data: null, orderable: false, render: (d, t, r) => `
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-info" onclick="viewTeamPerformance(${r.id})"><i class="ti ti-eye"></i></button>
                    <button class="btn btn-outline-primary" onclick="prepareEditTeamModal(${r.id})"><i class="ti ti-edit"></i></button>
                    <button class="btn btn-outline-danger" onclick="deleteTeamPerformance(${r.id})"><i class="ti ti-trash"></i></button>
                </div>`
                }]
        });

        $('#filterForm').on('submit', function (e) {
            e.preventDefault();
            loadData();
        });

        $('#teamFilterForm').on('submit', function (e) {
            e.preventDefault();
            loadTeamData();
        });

        $('#performanceForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_edit_performance');
            fetch('/hrms/api/api_performance.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        performanceModal.hide();
                        loadData();
                    } else {
                        showToast(result.message, 'error');
                    }
                });
        });

        $('#teamPerformanceForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_edit_team_performance');
            fetch('/hrms/api/api_performance.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        teamPerformanceModal.hide();
                        loadTeamData();
                    } else {
                        showToast(result.message, 'error');
                    }
                });
        });

        initChart();
        initTeamChart();
        loadData();
    });

    function switchTab(tab) {
        currentTab = tab;
        const btn = $('#addReviewBtn');
        if (tab === 'team') {
            btn.attr('onclick', 'prepareAddTeamModal()');
            btn.html('<i class="ti ti-plus me-2"></i>Add Team Review');
            loadTeamData();
        } else {
            btn.attr('onclick', 'prepareAddModal()');
            btn.html('<i class="ti ti-plus me-2"></i>Add Review');
            loadData();
        }
    }

    function loadData() {
        const employeeId = $('#employee_filter').val();
        const period = $('#period_filter').val();
        const url = `/hrms/api/api_performance.php?action=get_performance_data&employee_id=${employeeId}&period=${period}`;

        performanceTable.clear().draw();
        $('#performanceTable tbody').html('<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm"></div></td></tr>');

        fetch(url).then(res => res.json()).then(result => {
            if (result.success) {
                performanceTable.rows.add(result.data).draw();
                updateChart(result.chart_data);
            }
        });
    }

    function initChart() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        performanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Excellent', 'Good', 'Average', 'Poor'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#1cc88a', '#f6c23e', '#36b9cc', '#e74a3b'],
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    function initTeamChart() {
        const ctx = document.getElementById('teamPerformanceChart').getContext('2d');
        teamPerformanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Excellent', 'Good', 'Average', 'Poor'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#1cc88a', '#f6c23e', '#36b9cc', '#e74a3b'],
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    function loadTeamData() {
        const teamId = $('#team_filter').val();
        const period = $('#team_period_filter').val();
        const url = `/hrms/api/api_performance.php?action=get_team_performance_data&team_id=${teamId}&period=${period}`;

        teamPerformanceTable.clear().draw();
        $('#teamPerformanceTable tbody').html('<tr><td colspan="5" class="text-center"><div class="spinner-border spinner-border-sm"></div></td></tr>');

        fetch(url).then(res => res.json()).then(result => {
            if (result.success) {
                teamPerformanceTable.rows.add(result.data).draw();
                updateTeamChart(result.chart_data);
            }
        });
    }

    function updateTeamChart(data) {
        teamPerformanceChart.data.datasets[0].data = [data.excellent, data.good, data.average, data.poor];
        teamPerformanceChart.update();
    }

    function prepareAddTeamModal() {
        $('#teamPerformanceForm').trigger('reset');
        $('#teamPerformanceModalLabel').text('Add Team Performance Review');
        $('#teamPerformanceId').val(0);
        teamPerformanceModal.show();
    }

    function prepareEditTeamModal(id) {
        fetch(`/hrms/api/api_performance.php?action=get_team_performance_details&id=${id}`).then(res => res.json()).then(result => {
            if (result.success) {
                const data = result.data;
                $('#teamPerformanceForm').trigger('reset');
                $('#teamPerformanceModalLabel').text('Edit Team Performance Review');
                $('#teamPerformanceId').val(data.id);
                $('[name="team_id"]').val(data.team_id);
                $('[name="period"]').val(data.period);
                $('[name="score"]').val(data.score);
                $('[name="collaboration_score"]').val(data.collaboration_score);
                $('[name="achievements"]').val(data.achievements);
                $('[name="challenges"]').val(data.challenges);
                $('[name="remarks"]').val(data.remarks);
                teamPerformanceModal.show();
            } else {
                showToast(result.message, 'error');
            }
        });
    }

    function viewTeamPerformance(id) {
        fetch(`/hrms/api/api_performance.php?action=get_team_performance_details&id=${id}`).then(res => res.json()).then(result => {
            if (result.success) {
                const p = result.data;
                const html = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Team:</strong> ${escapeHTML(p.team_name)}</p>
                        <p><strong>Members:</strong> ${p.member_count || 0}</p>
                        <p><strong>Period:</strong> ${escapeHTML(p.period)}</p>
                        <p><strong>Overall Score:</strong> <span class="badge bg-${getScoreColor(p.score)}">${p.score}/100</span></p>
                        <p><strong>Collaboration Score:</strong> ${p.collaboration_score ? `<span class="badge bg-${getScoreColor(p.collaboration_score)}">${p.collaboration_score}/100</span>` : 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Evaluator:</strong> ${escapeHTML(p.evaluator_name || 'N/A')}</p>
                        <p><strong>Evaluated:</strong> ${new Date(p.created_at).toLocaleDateString()}</p>
                    </div>
                </div>
                <hr>
                <h6>Achievements</h6>
                <p class="border p-2 rounded">${escapeHTML(p.achievements || 'No achievements noted.')}</p>
                <h6>Challenges</h6>
                <p class="border p-2 rounded">${escapeHTML(p.challenges || 'No challenges noted.')}</p>
                <h6>General Remarks</h6>
                <p class="border p-2 rounded">${escapeHTML(p.remarks || 'No remarks provided.')}</p>
            `;
                $('#viewModalBody').html(html);
                viewModal.show();
            } else {
                showToast(result.message, 'error');
            }
        });
    }

    function deleteTeamPerformance(id) {
        if (confirm('Are you sure you want to delete this team review?')) {
            const formData = new FormData();
            formData.append('action', 'delete_team_performance');
            formData.append('id', id);
            fetch('/hrms/api/api_performance.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        loadTeamData();
                    } else {
                        showToast(result.message, 'error');
                    }
                });
        }
    }

    function updateChart(data) {
        performanceChart.data.datasets[0].data = [data.excellent, data.good, data.average, data.poor];
        performanceChart.update();
    }

    function prepareAddModal() {
        $('#performanceForm').trigger('reset');
        $('#performanceModalLabel').text('Add Performance Review');
        $('#performanceId').val(0);
        performanceModal.show();
    }

    function prepareEditModal(id) {
        fetch(`/hrms/api/api_performance.php?action=get_performance_details&id=${id}`).then(res => res.json()).then(result => {
            if (result.success) {
                const data = result.data;
                $('#performanceForm').trigger('reset');
                $('#performanceModalLabel').text('Edit Performance Review');
                $('#performanceId').val(data.id);
                $('[name="employee_id"]').val(data.employee_id);
                $('[name="period"]').val(data.period);
                $('[name="score"]').val(data.score);
                $('[name="remarks"]').val(data.remarks);
                performanceModal.show();
            } else {
                showToast(result.message, 'error');
            }
        });
    }

    function viewPerformance(id) {
        fetch(`/hrms/api/api_performance.php?action=get_performance_details&id=${id}`).then(res => res.json()).then(result => {
            if (result.success) {
                const p = result.data;
                const html = `
                <p><strong>Employee:</strong> ${escapeHTML(p.first_name)} ${escapeHTML(p.last_name)}</p>
                <p><strong>Period:</strong> ${escapeHTML(p.period)}</p>
                <p><strong>Score:</strong> <span class="badge bg-${getScoreColor(p.score)}">${p.score}/100</span></p>
                <p><strong>Evaluator:</strong> ${escapeHTML(p.evaluator_name || 'N/A')}</p>
                <hr>
                <h6>Remarks</h6>
                <p class="border p-2 rounded">${escapeHTML(p.remarks || 'No remarks provided.')}</p>
            `;
                $('#viewModalBody').html(html);
                viewModal.show();
            } else {
                showToast(result.message, 'error');
            }
        });
    }

    function deletePerformance(id) {
        if (confirm('Are you sure you want to delete this review?')) {
            const formData = new FormData();
            formData.append('action', 'delete_performance');
            formData.append('id', id);
            fetch('/hrms/api/api_performance.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        loadData();
                    } else {
                        showToast(result.message, 'error');
                    }
                });
        }
    }

    function getScoreColor(score) {
        if (score >= 80) return 'success';
        if (score >= 60) return 'warning';
        if (score >= 40) return 'info';
        return 'danger';
    }
</script>