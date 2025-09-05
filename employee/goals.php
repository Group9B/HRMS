<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Tasks & Goals";

if (!isLoggedIn()) {
    redirect("/hrms/auth/login.php");
}
if ($_SESSION['role_id'] !== 4) {
    redirect("/hrms/unauthorized.php");
}

$user_id = $_SESSION['user_id'];

// Get employee details
$employee_result = query($mysqli, "SELECT id, first_name, last_name FROM employees WHERE user_id = ?", [$user_id]);
if (!$employee_result['success'] || empty($employee_result['data'])) {
    redirect('/hrms/unauthorized.php');
}
$employee = $employee_result['data'][0];
$employee_id = $employee['id'];

// Handle personal todo creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['todo_task'])) {
    $todo_task = $_POST['todo_task'] ?? '';
    
    if (!empty($todo_task)) {
        $sql = "INSERT INTO todo_list (user_id, task) VALUES (?, ?)";
        $result = query($mysqli, $sql, [$user_id, $todo_task]);
        
        if ($result['success']) {
            $_SESSION['success'] = "Personal task added successfully!";
            redirect("/hrms/employee/goals.php");
        } else {
            $error = "Failed to add personal task. Please try again.";
        }
    }
}

// Handle personal todo completion
if (isset($_GET['complete_todo'])) {
    $todo_id = (int)$_GET['complete_todo'];
    $sql = "UPDATE todo_list SET is_completed = 1 WHERE id = ? AND user_id = ?";
    $result = query($mysqli, $sql, [$todo_id, $user_id]);
    
    if ($result['success']) {
        $_SESSION['success'] = "Personal task marked as completed!";
    }
    redirect("/hrms/employee/goals.php");
}

// Handle personal todo deletion
if (isset($_GET['delete_todo'])) {
    $todo_id = (int)$_GET['delete_todo'];
    $sql = "DELETE FROM todo_list WHERE id = ? AND user_id = ?";
    $result = query($mysqli, $sql, [$todo_id, $user_id]);
    
    if ($result['success']) {
        $_SESSION['success'] = "Personal task deleted!";
    }
    redirect("/hrms/employee/goals.php");
}

// Get assigned tasks
$tasks_result = query($mysqli, "SELECT t.*, e.first_name as assigned_by_name FROM tasks t LEFT JOIN employees e ON t.assigned_by = e.user_id WHERE t.employee_id = ? ORDER BY t.due_date ASC", [$employee_id]);
$assigned_tasks = $tasks_result['success'] ? $tasks_result['data'] : [];

// Get personal todos
$todos_result = query($mysqli, "SELECT * FROM todo_list WHERE user_id = ? ORDER BY created_at DESC", [$user_id]);
$personal_todos = $todos_result['success'] ? $todos_result['data'] : [];

// Calculate task statistics
$total_tasks = count($assigned_tasks);
$pending_tasks = array_filter($assigned_tasks, fn($task) => $task['status'] === 'pending');
$in_progress_tasks = array_filter($assigned_tasks, fn($task) => $task['status'] === 'in_progress');
$completed_tasks = array_filter($assigned_tasks, fn($task) => $task['status'] === 'completed');

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 class="h3 mb-0"><i class="fas fa-tasks me-2"></i>Tasks & Goals</h2>
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
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Task Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-primary border-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= $total_tasks ?></h4>
                                <p class="text-muted mb-0">Total Tasks</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-tasks fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-warning border-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= count($pending_tasks) ?></h4>
                                <p class="text-muted mb-0">Pending</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-info border-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= count($in_progress_tasks) ?></h4>
                                <p class="text-muted mb-0">In Progress</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-spinner fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-start border-success border-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-0"><?= count($completed_tasks) ?></h4>
                                <p class="text-muted mb-0">Completed</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Assigned Tasks -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Assigned Tasks</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assigned_tasks)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No assigned tasks</h6>
                                <p class="text-muted">Tasks assigned to you will appear here.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="tasksTable">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Assigned By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assigned_tasks as $task): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($task['title']) ?></strong>
                                                    <?php if ($task['description']): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars(substr($task['description'], 0, 100)) ?><?= strlen($task['description']) > 100 ? '...' : '' ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($task['due_date']): ?>
                                                        <span class="badge text-bg-<?= $task['due_date'] < date('Y-m-d') && $task['status'] !== 'completed' ? 'danger' : 'secondary' ?>">
                                                            <?= date('M d, Y', strtotime($task['due_date'])) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">No due date</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge text-bg-<?= $task['status'] === 'completed' ? 'success' : ($task['status'] === 'in_progress' ? 'info' : ($task['status'] === 'cancelled' ? 'danger' : 'warning')) ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= $task['assigned_by_name'] ?? 'System' ?>
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

            <!-- Personal To-Do List -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Personal To-Do List</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="todo_task" 
                                       placeholder="Add a personal task..." required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </form>

                        <?php if (empty($personal_todos)): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-list-ul fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No personal tasks</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($personal_todos as $todo): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <span class="<?= $todo['is_completed'] ? 'text-decoration-line-through text-muted' : '' ?>">
                                                <?= htmlspecialchars($todo['task']) ?>
                                            </span>
                                            <br><small class="text-muted"><?= date('M d, Y', strtotime($todo['created_at'])) ?></small>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <?php if (!$todo['is_completed']): ?>
                                                <a href="?complete_todo=<?= $todo['id'] ?>" class="btn btn-sm btn-success me-1" title="Mark as completed">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?delete_todo=<?= $todo['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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
$(function() {
    $('#tasksTable').DataTable({
        responsive: true,
        order: [[1, 'asc']]
    });
});
</script>