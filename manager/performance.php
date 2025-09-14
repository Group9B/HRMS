<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Team Performance";

if (!isLoggedIn() || $_SESSION['role_id'] !== 6) {
    redirect("/hrms/auth/login.php");
}

$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];

// Get manager's employee record
$manager_result = query($mysqli, "SELECT * FROM employees WHERE user_id = ?", [$user_id]);
$manager = $manager_result['success'] ? $manager_result['data'][0] : null;

if (!$manager) {
    redirect("/hrms/pages/unauthorized.php");
}

$manager_id = $manager['id'];
$manager_department_id = $manager['department_id'];

// Get filter parameters
$employee_filter = $_GET['employee_id'] ?? '';
$period_filter = $_GET['period'] ?? date('Y-m');

// Build query conditions
$where_conditions = ["e.department_id = ?"];
$params = [$manager_department_id];

if (!empty($employee_filter)) {
    $where_conditions[] = "p.employee_id = ?";
    $params[] = $employee_filter;
}

if (!empty($period_filter)) {
    $where_conditions[] = "p.period = ?";
    $params[] = $period_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get performance records
$performance_result = query($mysqli, "
    SELECT p.*, e.first_name, e.last_name, e.employee_code,
           des.name as designation_name, u.username as evaluator_name
    FROM performance p
    JOIN employees e ON p.employee_id = e.id
    LEFT JOIN designations des ON e.designation_id = des.id
    LEFT JOIN users u ON p.evaluator_id = u.id
    WHERE $where_clause
    ORDER BY p.created_at DESC
", $params);

$performance_records = $performance_result['success'] ? $performance_result['data'] : [];

// Get team members for filter dropdown
$team_members_result = query($mysqli, "
    SELECT e.id, e.first_name, e.last_name, e.employee_code
    FROM employees e
    WHERE e.department_id = ? AND e.status = 'active'
    ORDER BY e.first_name ASC
", [$manager_department_id]);

$team_members = $team_members_result['success'] ? $team_members_result['data'] : [];

// Get performance statistics
$stats_result = query($mysqli, "
    SELECT 
        COUNT(*) as total_reviews,
        AVG(p.score) as average_score,
        COUNT(CASE WHEN p.score >= 80 THEN 1 END) as excellent,
        COUNT(CASE WHEN p.score >= 60 AND p.score < 80 THEN 1 END) as good,
        COUNT(CASE WHEN p.score >= 40 AND p.score < 60 THEN 1 END) as average,
        COUNT(CASE WHEN p.score < 40 THEN 1 END) as poor
    FROM performance p
    JOIN employees e ON p.employee_id = e.id
    WHERE e.department_id = ? AND p.period = ?
", [$manager_department_id, $period_filter]);

$stats = $stats_result['success'] ? $stats_result['data'][0] : [
    'total_reviews' => 0,
    'average_score' => 0,
    'excellent' => 0,
    'good' => 0,
    'average' => 0,
    'poor' => 0
];

// Get recent performance reviews
$recent_reviews_result = query($mysqli, "
    SELECT p.*, e.first_name, e.last_name, e.employee_code
    FROM performance p
    JOIN employees e ON p.employee_id = e.id
    WHERE e.department_id = ?
    ORDER BY p.created_at DESC
    LIMIT 5
", [$manager_department_id]);

$recent_reviews = $recent_reviews_result['success'] ? $recent_reviews_result['data'] : [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 text-gray-800">
                <i class="fas fa-chart-line me-2"></i>Team Performance
            </h2>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPerformanceModal">
                    <i class="fas fa-plus me-2"></i>Add Performance Review
                </button>
                <button class="btn btn-success" onclick="exportPerformance()">
                    <i class="fas fa-download me-2"></i>Export Report
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-primary"><i class="fas fa-chart-bar"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Reviews</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_reviews'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-info"><i class="fas fa-star"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Average Score</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['average_score'], 1) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-success"><i class="fas fa-trophy"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Excellent</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['excellent'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-warning"><i class="fas fa-thumbs-up"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Good</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['good'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-info"><i class="fas fa-chart-line"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Average</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['average'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <div class="icon-circle bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Poor</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['poor'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select class="form-select" id="employee_id" name="employee_id">
                            <option value="">All Employees</option>
                            <?php foreach ($team_members as $member): ?>
                                <option value="<?= $member['id'] ?>" <?= $employee_filter == $member['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="period" class="form-label">Period</label>
                        <input type="month" class="form-control" id="period" name="period" value="<?= htmlspecialchars($period_filter) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Performance Records Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Performance Reviews</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($performance_records)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="performanceTable">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Period</th>
                                    <th>Score</th>
                                    <th>Rating</th>
                                    <th>Evaluator</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($performance_records as $record): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-3">
                                                    <?= strtoupper(substr($record['first_name'], 0, 1) . substr($record['last_name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($record['employee_code'] ?? 'N/A') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= htmlspecialchars($record['period']) ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 60px; height: 8px;">
                                                    <div class="progress-bar bg-<?= getScoreColor($record['score']) ?>" 
                                                         style="width: <?= $record['score'] ?>%"></div>
                                                </div>
                                                <span class="fw-bold"><?= $record['score'] ?>/100</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= getScoreColor($record['score']) ?>">
                                                <?= getScoreRating($record['score']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($record['evaluator_name'] ?? 'N/A') ?></td>
                                        <td><?= date('M j, Y', strtotime($record['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="viewPerformance(<?= $record['id'] ?>)" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success" onclick="editPerformance(<?= $record['id'] ?>)" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="deletePerformance(<?= $record['id'] ?>)" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted p-5">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <h5>No Performance Reviews Found</h5>
                        <p>No performance reviews match your current filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Reviews -->
        <?php if (!empty($recent_reviews)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Recent Performance Reviews</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($recent_reviews as $review): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border-left-<?= getScoreColor($review['score']) ?> shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="card-title mb-0"><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></h6>
                                                <span class="badge bg-<?= getScoreColor($review['score']) ?>"><?= $review['score'] ?></span>
                                            </div>
                                            <p class="card-text text-muted small">
                                                <strong>Period:</strong> <?= htmlspecialchars($review['period']) ?><br>
                                                <strong>Date:</strong> <?= date('M j, Y', strtotime($review['created_at'])) ?>
                                            </p>
                                            <?php if ($review['remarks']): ?>
                                                <p class="card-text small"><?= htmlspecialchars(substr($review['remarks'], 0, 100)) ?><?= strlen($review['remarks']) > 100 ? '...' : '' ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Performance Modal -->
<div class="modal fade" id="addPerformanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Performance Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="performanceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="performance_employee" class="form-label">Employee *</label>
                        <select class="form-select" id="performance_employee" name="employee_id" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($team_members as $member): ?>
                                <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="performance_period" class="form-label">Period *</label>
                        <input type="month" class="form-control" id="performance_period" name="period" value="<?= $period_filter ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="performance_score" class="form-label">Score (0-100) *</label>
                        <input type="number" class="form-control" id="performance_score" name="score" min="0" max="100" required>
                        <div class="form-text">Enter a score between 0 and 100</div>
                    </div>
                    <div class="mb-3">
                        <label for="performance_remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="performance_remarks" name="remarks" rows="3" placeholder="Enter performance remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Performance Details Modal -->
<div class="modal fade" id="performanceDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Performance Review Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="performanceDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(45deg, #4e73df, #36b9cc);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.stat-card .card-body {
    display: flex;
    align-items: center;
}

.icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
    font-size: 20px;
}

.border-left-success {
    border-left: 4px solid #28a745 !important;
}

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}

.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#performanceTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[5, 'desc']] // Sort by date
    });

    // Handle performance form submission
    $('#performanceForm').on('submit', function(e) {
        e.preventDefault();
        addPerformance();
    });
});

function addPerformance() {
    const formData = new FormData(document.getElementById('performanceForm'));
    formData.append('action', 'add_performance');

    fetch('/hrms/api/api_manager.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            $('#addPerformanceModal').modal('hide');
            document.getElementById('performanceForm').reset();
            location.reload();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('An error occurred. Please try again.', 'error');
    });
}

function viewPerformance(performanceId) {
    fetch(`/hrms/api/api_manager.php?action=get_performance_details&performance_id=${performanceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPerformanceDetails(data.data);
                $('#performanceDetailsModal').modal('show');
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
        });
}

function displayPerformanceDetails(performance) {
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Employee Information</h6>
                <p><strong>Name:</strong> ${performance.first_name} ${performance.last_name}</p>
                <p><strong>Employee Code:</strong> ${performance.employee_code || 'N/A'}</p>
                <p><strong>Designation:</strong> ${performance.designation_name || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <h6>Performance Information</h6>
                <p><strong>Period:</strong> ${performance.period}</p>
                <p><strong>Score:</strong> <span class="badge bg-${getScoreColor(performance.score)}">${performance.score}/100</span></p>
                <p><strong>Rating:</strong> <span class="badge bg-${getScoreColor(performance.score)}">${getScoreRating(performance.score)}</span></p>
                <p><strong>Evaluator:</strong> ${performance.evaluator_name || 'N/A'}</p>
                <p><strong>Date:</strong> ${new Date(performance.created_at).toLocaleString()}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Remarks</h6>
                <p class="border p-3 rounded">${performance.remarks || 'No remarks provided'}</p>
            </div>
        </div>
    `;
    
    $('#performanceDetailsContent').html(html);
}

function editPerformance(performanceId) {
    // Redirect to edit page or show edit modal
    window.location.href = `/hrms/manager/edit_performance.php?id=${performanceId}`;
}

function deletePerformance(performanceId) {
    if (confirm('Are you sure you want to delete this performance review?')) {
        const formData = new FormData();
        formData.append('action', 'delete_performance');
        formData.append('performance_id', performanceId);

        fetch('/hrms/api/api_manager.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                location.reload();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
        });
    }
}

function exportPerformance() {
    // Implement export functionality
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    window.open(`/hrms/manager/performance.php?${params.toString()}`, '_blank');
}

function getScoreColor(score) {
    if (score >= 80) return 'success';
    if (score >= 60) return 'warning';
    if (score >= 40) return 'info';
    return 'danger';
}

function getScoreRating(score) {
    if (score >= 80) return 'Excellent';
    if (score >= 60) return 'Good';
    if (score >= 40) return 'Average';
    return 'Poor';
}
</script>

<?php
// Helper functions for PHP
function getScoreColor($score) {
    if ($score >= 80) return 'success';
    if ($score >= 60) return 'warning';
    if ($score >= 40) return 'info';
    return 'danger';
}

function getScoreRating($score) {
    if ($score >= 80) return 'Excellent';
    if ($score >= 60) return 'Good';
    if ($score >= 40) return 'Average';
    return 'Poor';
}
?>
