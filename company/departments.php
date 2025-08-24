<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Departments & Designations";

// --- SECURITY & SESSION ---
if (!isLoggedIn() && in_array($_SESSION['role_id'], [2, 3])) {
    redirect("/hrms/unauthorized.php");
}
$company_id = $_SESSION['company_id'];

// Fetch departments for the designation modal dropdown
$departments_result = query($mysqli, "SELECT id, name FROM departments WHERE company_id = ? ORDER BY name ASC", [$company_id]);
$departments = $departments_result['success'] ? $departments_result['data'] : [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <h2 class="h3 mb-4 text-gray-800"><i class="fas fa-sitemap me-2"></i>Departments & Designations</h2>

        <div class="row">
            <!-- Departments Panel -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Departments</h6>
                        <button class="btn btn-primary btn-sm" onclick="prepareAddModal('department')"><i
                                class="fas fa-plus me-1"></i> Add</button>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover" id="departmentsTable" width="100%">
                            <thead class="table">
                                <tr>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Designations Panel -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Designations</h6>
                        <button class="btn btn-primary btn-sm" onclick="prepareAddModal('designation')"><i
                                class="fas fa-plus me-1"></i> Add</button>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover" id="designationsTable" width="100%">
                            <thead class="">
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
    </div>
</div>

<!-- Department Modal -->
<div class="modal fade" id="departmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="departmentForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="departmentModalLabel"></h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="deptAction">
                    <input type="hidden" name="department_id" id="departmentId" value="0">
                    <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input
                            type="text" class="form-control" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control"
                            name="description" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit"
                        class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Designation Modal -->
<div class="modal fade" id="designationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="designationForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="designationModalLabel"></h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="desAction">
                    <input type="hidden" name="designation_id" id="designationId" value="0">
                    <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input
                            type="text" class="form-control" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Department <span
                                class="text-danger">*</span></label><select class="form-select" name="department_id"
                            required>
                            <option value="">-- Select --</option><?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control"
                            name="description" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit"
                        class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    let deptTable, desTable, deptModal, desModal;

    $(function () {
        deptModal = new bootstrap.Modal('#departmentModal');
        desModal = new bootstrap.Modal('#designationModal');

        // Initialize Departments Table
        deptTable = $('#departmentsTable').DataTable({
            ajax: { url: '/hrms/api/api_departments.php?action=get_departments', dataSrc: 'data' },
            columns: [
                { data: 'name', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong><br><small class="text-muted">${escapeHTML(r.description)}</small>` },
                { data: null, orderable: false, render: (d, t, r) => `<div class="btn-group"><button class="btn btn-sm btn-outline-primary" onclick='prepareEditModal("department", ${JSON.stringify(r)})'><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-outline-danger" onclick="deleteItem('department', ${r.id})"><i class="fas fa-trash"></i></button></div>` }
            ]
        });

        // Initialize Designations Table
        desTable = $('#designationsTable').DataTable({
            ajax: { url: '/hrms/api/api_departments.php?action=get_designations', dataSrc: 'data' },
            columns: [
                { data: 'name', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong><br><small class="text-muted">${escapeHTML(r.description)}</small>` },
                { data: 'department_name' },
                { data: null, orderable: false, render: (d, t, r) => `<div class="btn-group"><button class="btn btn-sm btn-outline-primary" onclick='prepareEditModal("designation", ${JSON.stringify(r)})'><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-outline-danger" onclick="deleteItem('designation', ${r.id})"><i class="fas fa-trash"></i></button></div>` }
            ]
        });

        // Handle Form Submissions
        $('#departmentForm, #designationForm').on('submit', function (e) {
            e.preventDefault();
            const form = $(this);
            fetch('/hrms/api/api_departments.php', { method: 'POST', body: new FormData(this) })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        (form.attr('id') === 'departmentForm' ? deptModal : desModal).hide();
                        deptTable.ajax.reload();
                        desTable.ajax.reload();
                    } else { showToast(data.message, 'error'); }
                });
        });
    });

    function prepareAddModal(type) {
        const formId = `#${type}Form`;
        $(formId).trigger("reset");
        $(`#${type}ModalLabel`).text(`Add ${capitalize(type)}`);
        $(`${formId} [name="action"]`).val(`add_edit_${type}`);
        $(`${formId} [name="${type}_id"]`).val('0');
        (type === 'department' ? deptModal : desModal).show();
    }

    function prepareEditModal(type, data) {
        const formId = `#${type}Form`;
        $(formId).trigger("reset");
        $(`#${type}ModalLabel`).text(`Edit ${capitalize(type)}`);
        $(`${formId} [name="action"]`).val(`add_edit_${type}`);
        $(`${formId} [name="${type}_id"]`).val(data.id);
        $(`${formId} [name="name"]`).val(data.name);
        $(`${formId} [name="description"]`).val(data.description);
        if (type === 'designation') {
            $(`${formId} [name="department_id"]`).val(data.department_id);
        }
        (type === 'department' ? deptModal : desModal).show();
    }

    function deleteItem(type, id) {
        if (confirm(`Are you sure you want to delete this ${type}?`)) {
            const formData = new FormData();
            formData.append('action', `delete_${type}`);
            formData.append(`${type}_id`, id);
            fetch('api_departments.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        (type === 'department' ? deptTable : desTable).ajax.reload();
                    } else { showToast(data.message, 'error'); }
                });
        }
    }

    function capitalize(str) { return str.charAt(0).toUpperCase() + str.slice(1); }
</script>