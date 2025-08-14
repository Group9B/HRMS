<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Handle Add, Edit, Delete Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $company_id = isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0;

    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    if ($action === 'add') {
        $sql = "INSERT INTO companies (name, address, email, phone) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        // 's' denotes the type is a string
        $stmt->bind_param('ssss', $name, $address, $email, $phone);
        $stmt->execute();
    } elseif ($action === 'edit' && $company_id > 0) {
        $sql = "UPDATE companies SET name = ?, address = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        // 'i' denotes the type is an integer
        $stmt->bind_param('ssssi', $name, $address, $email, $phone, $company_id);
        $stmt->execute();
    } elseif ($action === 'delete' && $company_id > 0) {
        $sql = "DELETE FROM companies WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $company_id);
        $stmt->execute();
    }
    redirect('companies.php?success=1');
    exit();
}


// Fetch all companies
$companiesQuery = "SELECT * FROM companies ORDER BY created_at DESC";
$result = $mysqli->query($companiesQuery);
$companies = $result->fetch_all(MYSQLI_ASSOC);

$title = "Company Management";
require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-building me-2"></i>Company Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#companyModal"
                onclick="prepareAddModal()">
                <i class="fas fa-plus me-2"></i>Add Company
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="companiesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $company): ?>
                                <tr>
                                    <td><?= $company['id']; ?></td>
                                    <td><?= htmlspecialchars($company['name']); ?></td>
                                    <td><?= htmlspecialchars($company['address']); ?></td>
                                    <td><?= htmlspecialchars($company['email']); ?></td>
                                    <td><?= htmlspecialchars($company['phone']); ?></td>
                                    <td><?= date('M d, Y', strtotime($company['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick='prepareEditModal(<?= json_encode($company); ?>)'
                                                data-bs-toggle="modal" data-bs-target="#companyModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="deleteCompany(<?= $company['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success mt-3">Action completed successfully.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Company Modal -->
<div class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="companyForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="companyModalLabel">Add Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="company_id" id="companyId" value="0">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Company</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
        $('#companiesTable').DataTable({ order: [[5, 'desc']], pageLength: 10 });
    });

    function prepareAddModal() {
        $('#companyForm').trigger("reset");
        $('#companyModalLabel').text('Add Company');
        $('#formAction').val('add');
        $('#companyId').val('0');
    }

    function prepareEditModal(company) {
        $('#companyForm').trigger("reset");
        $('#companyModalLabel').text('Edit Company');
        $('#formAction').val('edit');
        $('#companyId').val(company.id);
        $('#name').val(company.name);
        $('#address').val(company.address);
        $('#email').val(company.email);
        $('#phone').val(company.phone);
    }

    function deleteCompany(companyId) {
        if (confirm('Are you sure you want to delete this company?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="company_id" value="${companyId}">
                <input type="hidden" name="action" value="delete">
            `;
            document.body.append(form);
            form.submit();
        }
    }
</script>