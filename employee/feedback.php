<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Feedback & Suggestions";

if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}
if ($_SESSION['role_id'] !== 4) {
    redirect("/hrms/pages/unauthorized.php");
}

$user_id = $_SESSION['user_id'];

// Get employee details
$employee_result = query($mysqli, "SELECT id, first_name, last_name FROM employees WHERE user_id = ?", [$user_id]);
if (!$employee_result['success'] || empty($employee_result['data'])) {
    redirect('/hrms/pages/unauthorized.php');
}
$employee = $employee_result['data'][0];
$employee_id = $employee['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $type = $_POST['type'] ?? 'feedback';

    if (!empty($message)) {
        try {
            // Check if type column exists, if not use basic insert
            $check_column = query($mysqli, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'feedback' AND COLUMN_NAME = 'type'");

            if ($check_column['success'] && count($check_column['data']) > 0) {
                $sql = "INSERT INTO feedback (employee_id, submitted_by, message, type) VALUES (?, ?, ?, ?)";
                $result = query($mysqli, $sql, [$employee_id, $user_id, $message, $type]);
            } else {
                $sql = "INSERT INTO feedback (employee_id, submitted_by, message) VALUES (?, ?, ?)";
                $result = query($mysqli, $sql, [$employee_id, $user_id, $message]);
            }

            if ($result['success']) {
                $_SESSION['success'] = "Feedback submitted successfully!";
                redirect("/hrms/employee/feedback.php");
            } else {
                $error = "Failed to submit feedback. Error: " . ($result['error'] ?? 'Unknown error');
                error_log("Feedback submission failed: " . print_r($result, true));
            }
        } catch (Exception $e) {
            $error = "An error occurred: " . $e->getMessage();
            error_log("Feedback submission exception: " . $e->getMessage());
        }
    } else {
        $error = "Please enter your feedback message.";
    }
}

// Get feedback history
$feedback_result = query($mysqli, "SELECT f.*, e.first_name as submitted_by_name FROM feedback f LEFT JOIN employees e ON f.submitted_by = e.user_id WHERE f.employee_id = ? ORDER BY f.created_at DESC", [$employee_id]);
$feedback_history = $feedback_result['success'] ? $feedback_result['data'] : [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="h3 mb-0"><i class="ti ti-message me-2"></i>Feedback & Suggestions</h2>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
            <div class="alert alert-info">
                <strong>Debug Info:</strong><br>
                Employee ID: <?= $employee_id ?><br>
                User ID: <?= $user_id ?><br>
                Database Connection: <?= $mysqli ? 'Connected' : 'Failed' ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Submit Feedback</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="feedbackForm">
                            <div class="mb-3">
                                <label class="form-label">Feedback Type</label>
                                <select class="form-select" name="type" required>
                                    <option value="feedback">General Feedback</option>
                                    <option value="suggestion">Suggestion</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="appreciation">Appreciation</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Your Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="message" rows="5"
                                    placeholder="Please share your feedback, suggestions, or concerns here..."
                                    required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-send me-2"></i>Submit Feedback
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Feedback Guidelines</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>We value your feedback!</strong></p>
                        <ul>
                            <li>Be specific and constructive in your feedback</li>
                            <li>Include suggestions for improvement when possible</li>
                            <li>Focus on processes, policies, or work environment</li>
                            <li>All feedback is reviewed by HR management</li>
                            <li>Your identity will be kept confidential</li>
                        </ul>

                        <div class="alert alert-info">
                            <small>
                                <i class="ti ti-info-circle me-1"></i>
                                You can submit feedback anonymously or with your name attached.
                                All feedback is reviewed and appropriate action is taken when necessary.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Your Feedback History</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($feedback_history)): ?>
                            <div class="text-center py-4">
                                <i class="ti ti-message-circle-off fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No feedback submitted yet</h6>
                                <p class="text-muted">Your feedback history will appear here once you submit feedback.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="feedbackTable">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Message</th>
                                            <th>Submitted</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($feedback_history as $feedback): ?>
                                            <tr>
                                                <td>
                                                    <span
                                                        class="badge text-bg-<?= $feedback['type'] === 'suggestion' ? 'info' : ($feedback['type'] === 'complaint' ? 'danger' : ($feedback['type'] === 'appreciation' ? 'success' : 'secondary')) ?>">
                                                        <?= ucfirst($feedback['type']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 300px;"
                                                        title="<?= htmlspecialchars($feedback['message']) ?>">
                                                        <?= htmlspecialchars($feedback['message']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= date('M d, Y H:i', strtotime($feedback['created_at'])) ?>
                                                </td>
                                                <td>
                                                    <span class="badge text-bg-secondary">Submitted</span>
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

<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
        $('#feedbackTable').DataTable({
            responsive: true,
            order: [[2, 'desc']]
        });

        $('#feedbackForm').on('submit', function (e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    });
</script>