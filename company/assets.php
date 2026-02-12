<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Asset Management";

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [2, 3])) {
    redirect("/hrms/pages/unauthorized.php");
}

$company_id = $_SESSION['company_id'];

// Fetch employees for assignment dropdown
$employees = query($mysqli, "SELECT e.id, CONCAT(e.first_name, ' ', e.last_name) as full_name, e.employee_code FROM employees e JOIN departments d ON e.department_id = d.id WHERE d.company_id = ? AND e.status = 'active' ORDER BY e.first_name ASC", [$company_id])['data'] ?? [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">

        <!-- Stat Cards -->
        <div class="row" id="assetStatsContainer"></div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="assetTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="assets-tab" data-bs-toggle="tab" data-bs-target="#assetsPane"
                    type="button" role="tab">
                    <i class="ti ti-device-laptop me-1"></i> Assets
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categoriesPane"
                    type="button" role="tab">
                    <i class="ti ti-category me-1"></i> Categories
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">

            <!-- Assets Tab -->
            <div class="tab-pane fade show active" id="assetsPane" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">All Assets</h6>
                        <button class="btn btn-sm btn-primary" onclick="openAddAssetModal()">
                            <i class="ti ti-plus me-1"></i>Add Asset
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered dtr-inline nowrap" id="assetsTable"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Asset Name</th>
                                        <th>Category</th>
                                        <th>Asset Tag</th>
                                        <th>Status</th>
                                        <th>Condition</th>
                                        <th>Assigned To</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Tab -->
            <div class="tab-pane fade" id="categoriesPane" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Asset Categories</h6>
                        <button class="btn btn-sm btn-primary" onclick="openAddCategoryModal()">
                            <i class="ti ti-plus me-1"></i>Add Category
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered dtr-inline nowrap" id="categoriesTable"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Assets Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Asset Modal -->
<div class="modal fade" id="assetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="assetForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="assetModalLabel">Add Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="asset_id" id="assetId" value="0">
                    <input type="hidden" name="action" id="assetFormAction" value="add_asset">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asset Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="asset_name" id="assetName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="assetCategory" required>
                                <option value="">-- Select Category --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Asset Tag</label>
                            <input type="text" class="form-control" name="asset_tag" id="assetTag"
                                placeholder="e.g., AST-001">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Serial Number</label>
                            <input type="text" class="form-control" name="serial_number" id="serialNumber">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Condition</label>
                            <select class="form-select" name="condition_status" id="conditionStatus">
                                <option value="New">New</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Poor">Poor</option>
                                <option value="Damaged">Damaged</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" name="purchase_date" id="purchaseDate">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Purchase Cost</label>
                            <input type="number" class="form-control" name="purchase_cost" id="purchaseCost" step="0.01"
                                min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Warranty Expiry</label>
                            <input type="date" class="form-control" name="warranty_expiry" id="warrantyExpiry">
                        </div>
                    </div>
                    <div class="row" id="statusFieldRow" style="display:none;">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="assetStatus">
                                <option value="Assigned">Assigned</option>
                                <option value="Available">Available</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Retired">Retired</option>
                                <option value="Lost">Lost</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="assetDescription" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="assetSaveBtn">Save Asset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Asset Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignForm">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="assign_asset">
                    <input type="hidden" name="asset_id" id="assignAssetId">
                    <p class="mb-3">Assigning: <strong id="assignAssetName"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select class="form-select" name="employee_id" id="assignEmployeeId" required>
                            <option value="">-- Select Employee --</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>">
                                    <?= htmlspecialchars($emp['full_name']) ?> (
                                    <?= htmlspecialchars($emp['employee_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assigned Date</label>
                            <input type="date" class="form-control" name="assigned_date" id="assignedDate"
                                value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expected Return</label>
                            <input type="date" class="form-control" name="expected_return_date" id="expectedReturnDate">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Condition on Assignment</label>
                        <select class="form-select" name="condition_on_assignment" id="conditionOnAssignment">
                            <option value="New">New</option>
                            <option value="Good" selected>Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Asset Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="returnForm">
                <div class="modal-header">
                    <h5 class="modal-title">Return Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="return_asset">
                    <input type="hidden" name="assignment_id" id="returnAssignmentId">
                    <p class="mb-3">Returning: <strong id="returnAssetName"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Condition on Return</label>
                        <select class="form-select" name="condition_on_return" id="conditionOnReturn">
                            <option value="New">New</option>
                            <option value="Good" selected>Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Return Notes</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="categoryForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category_id" id="categoryId" value="0">
                    <input type="hidden" name="action" id="categoryFormAction" value="add_category">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="categoryName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" id="categoryType" required>
                            <option value="Hardware">Hardware</option>
                            <option value="Software">Software</option>
                            <option value="Access">Access</option>
                            <option value="Security">Security</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" id="categoryDescription">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assignment History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assignment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="historyContent">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    const API = '/hrms/api/api_assets.php';
    let assetsTable, categoriesTable;
    let allCategories = [];

    // ==================== INITIALIZATION ====================
    $(function () {
        loadStats();
        loadCategories();
        initAssetsTable();
        initCategoriesTable();

        $('#assetForm').on('submit', handleAssetSubmit);
        $('#assignForm').on('submit', handleAssignSubmit);
        $('#returnForm').on('submit', handleReturnSubmit);
        $('#categoryForm').on('submit', handleCategorySubmit);
    });

    function loadStats() {
        fetch(`${API}?action=get_asset_stats`)
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    const s = result.data;
                    renderStatCards('assetStatsContainer', [
                        { label: 'Total Assets', value: s.total, color: 'primary', icon: 'device-laptop' },
                        { label: 'Available', value: s.available, color: 'success', icon: 'circle-check' },
                        { label: 'Assigned', value: s.assigned, color: 'info', icon: 'user-check' },
                        { label: 'Maintenance / Other', value: s.other, color: 'warning', icon: 'tool' }
                    ]);
                }
            });
    }

    function loadCategories() {
        fetch(`${API}?action=get_categories`)
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    allCategories = result.data;
                    populateCategoryDropdown();
                }
            });
    }

    function populateCategoryDropdown() {
        const select = document.getElementById('assetCategory');
        select.innerHTML = '<option value="">-- Select Category --</option>';
        const grouped = {};
        allCategories.forEach(c => {
            if (!grouped[c.type]) grouped[c.type] = [];
            grouped[c.type].push(c);
        });
        Object.keys(grouped).sort().forEach(type => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = type;
            grouped[type].forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                optgroup.appendChild(opt);
            });
            select.appendChild(optgroup);
        });
    }

    // ==================== ASSETS TABLE ====================
    function initAssetsTable() {
        assetsTable = $('#assetsTable').DataTable({
            ajax: { url: `${API}?action=get_assets`, dataSrc: 'data' },
            responsive: true,
            columns: [
                { data: 'asset_name', render: (d, t, r) => `<strong>${escapeHTML(d)}</strong>${r.serial_number ? '<br><small class="text-muted">SN: ' + escapeHTML(r.serial_number) + '</small>' : ''}` },
                { data: 'category_name', render: (d, t, r) => `<span class="badge bg-secondary-subtle text-secondary-emphasis">${escapeHTML(r.category_type)}</span> ${escapeHTML(d)}` },
                { data: 'asset_tag', render: d => d ? escapeHTML(d) : '<span class="text-muted">-</span>' },
                {
                    data: 'status', render: d => {
                        const colors = { 'Available': 'success', 'Assigned': 'primary', 'Maintenance': 'warning', 'Retired': 'secondary', 'Lost': 'danger' };
                        const color = colors[d] || 'secondary';
                        return `<span class="badge bg-${color}-subtle text-${color}-emphasis">${d}</span>`;
                    }
                },
                {
                    data: 'condition_status', render: d => {
                        const colors = { 'New': 'success', 'Good': 'info', 'Fair': 'warning', 'Poor': 'danger', 'Damaged': 'danger' };
                        const color = colors[d] || 'secondary';
                        return `<span class="badge bg-${color}-subtle text-${color}-emphasis">${d}</span>`;
                    }
                },
                { data: 'assigned_to_name', render: (d, t, r) => d ? escapeHTML(d) : '<span class="text-muted">Unassigned</span>' },
                { data: null, orderable: false, render: (d, t, r) => buildAssetActions(r) }
            ],
            order: [[0, 'asc']]
        });
    }

    function buildAssetActions(r) {
        const config = {};

        if (r.status === 'Available') {
            config.onManage = () => openAssignModal(r.id, r.asset_name);
        }
        if (r.status === 'Assigned' && r.assigned_to_id) {
            config.onClose = () => openReturnModal(r.id, r.asset_name);
        }
        config.onViewLink = () => viewHistory(r.id);
        config.onEdit = () => openEditAssetModal(r);
        config.onDelete = () => deleteAsset(r.id, r.asset_name);

        return createActionDropdown(config, {
            manageTooltip: 'Assign',
            manageIcon: 'ti ti-user-plus',
            closeTooltip: 'Return',
            closeIcon: 'ti ti-arrow-back-up',
            viewLinkTooltip: 'History',
            viewLinkIcon: 'ti ti-history'
        });
    }

    // ==================== CATEGORIES TABLE ====================
    function initCategoriesTable() {
        categoriesTable = $('#categoriesTable').DataTable({
            ajax: {
                url: `${API}?action=get_categories`,
                dataSrc: function (json) {
                    if (!json.success) return [];
                    // Count assets per category from assets table
                    return json.data;
                }
            },
            responsive: true,
            columns: [
                { data: 'name', render: d => `<strong>${escapeHTML(d)}</strong>` },
                {
                    data: 'type', render: d => {
                        const colors = { 'Hardware': 'primary', 'Software': 'info', 'Access': 'warning', 'Security': 'danger', 'Other': 'secondary' };
                        const color = colors[d] || 'secondary';
                        return `<span class="badge bg-${color}-subtle text-${color}-emphasis">${d}</span>`;
                    }
                },
                { data: 'description', render: d => d ? escapeHTML(d) : '<span class="text-muted">-</span>' },
                { data: 'id', render: (d) => '<span class="asset-count" data-category-id="' + d + '">-</span>' },
                {
                    data: null, orderable: false, render: (d, t, r) => {
                        return createActionDropdown({
                            onEdit: () => openEditCategoryModal(r),
                            onDelete: () => deleteCategory(r.id, r.name)
                        });
                    }
                }
            ],
            order: [[1, 'asc'], [0, 'asc']],
            drawCallback: function () {
                // Load asset counts after draw
                fetch(`${API}?action=get_assets`).then(r => r.json()).then(result => {
                    if (result.success) {
                        const counts = {};
                        result.data.forEach(a => {
                            counts[a.category_id] = (counts[a.category_id] || 0) + 1;
                        });
                        document.querySelectorAll('.asset-count').forEach(el => {
                            const catId = el.dataset.categoryId;
                            el.textContent = counts[catId] || 0;
                        });
                    }
                });
            }
        });
    }

    // ==================== ASSET CRUD ====================
    function openAddAssetModal() {
        document.getElementById('assetForm').reset();
        document.getElementById('assetId').value = '0';
        document.getElementById('assetFormAction').value = 'add_asset';
        document.getElementById('assetModalLabel').textContent = 'Add Asset';
        document.getElementById('statusFieldRow').style.display = 'none';
        new bootstrap.Modal(document.getElementById('assetModal')).show();
    }

    function openEditAssetModal(r) {
        document.getElementById('assetForm').reset();
        document.getElementById('assetId').value = r.id;
        document.getElementById('assetFormAction').value = 'edit_asset';
        document.getElementById('assetModalLabel').textContent = 'Edit Asset';
        document.getElementById('assetName').value = r.asset_name;
        document.getElementById('assetCategory').value = r.category_id;
        document.getElementById('assetTag').value = r.asset_tag || '';
        document.getElementById('serialNumber').value = r.serial_number || '';
        document.getElementById('conditionStatus').value = r.condition_status || 'Good';
        document.getElementById('purchaseDate').value = r.purchase_date || '';
        document.getElementById('purchaseCost').value = r.purchase_cost || '';
        document.getElementById('warrantyExpiry').value = r.warranty_expiry || '';
        document.getElementById('assetDescription').value = r.description || '';

        const statusSelect = document.getElementById('assetStatus');
        // Always set current status so it is posted even when hidden
        statusSelect.value = r.status;

        // Show status field for edit only when not assigned; keep hidden but populated otherwise
        if (r.status !== 'Assigned') {
            document.getElementById('statusFieldRow').style.display = 'flex';
        } else {
            document.getElementById('statusFieldRow').style.display = 'none';
        }
        // Keep the field enabled so the current status posts with the form
        statusSelect.disabled = false;

        new bootstrap.Modal(document.getElementById('assetModal')).show();
    }

    function handleAssetSubmit(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch(API, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    showToast(result.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('assetModal')).hide();
                    assetsTable.ajax.reload();
                    loadStats();
                } else {
                    showToast(result.message, 'error');
                }
            })
            .catch(() => showToast('An error occurred.', 'error'));
    }

    function deleteAsset(id, name) {
        showConfirmationModal(
            `Are you sure you want to delete asset <strong>${escapeHTML(name)}</strong>?`,
            () => {
                const formData = new FormData();
                formData.append('action', 'delete_asset');
                formData.append('asset_id', id);
                fetch(API, { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(result => {
                        if (result.success) {
                            showToast(result.message, 'success');
                            assetsTable.ajax.reload();
                            loadStats();
                        } else {
                            showToast(result.message, 'error');
                        }
                    });
            },
            'Delete Asset', 'Delete', 'btn-danger'
        );
    }

    // ==================== ASSIGNMENT ====================
    function openAssignModal(assetId, assetName) {
        document.getElementById('assignForm').reset();
        document.getElementById('assignAssetId').value = assetId;
        document.getElementById('assignAssetName').textContent = assetName;
        document.getElementById('assignedDate').value = new Date().toISOString().split('T')[0];
        new bootstrap.Modal(document.getElementById('assignModal')).show();
    }

    function handleAssignSubmit(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch(API, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    showToast(result.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
                    assetsTable.ajax.reload();
                    loadStats();
                } else {
                    showToast(result.message, 'error');
                }
            })
            .catch(() => showToast('An error occurred.', 'error'));
    }

    function openReturnModal(assetId, assetName) {
        // Find the active assignment ID for this asset
        fetch(`${API}?action=get_assignment_history&asset_id=${assetId}`)
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    const active = result.data.find(a => a.status === 'Active');
                    if (active) {
                        document.getElementById('returnForm').reset();
                        document.getElementById('returnAssignmentId').value = active.id;
                        document.getElementById('returnAssetName').textContent = assetName;
                        new bootstrap.Modal(document.getElementById('returnModal')).show();
                    } else {
                        showToast('No active assignment found for this asset.', 'warning');
                    }
                }
            });
    }

    function handleReturnSubmit(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch(API, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    showToast(result.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('returnModal')).hide();
                    assetsTable.ajax.reload();
                    loadStats();
                } else {
                    showToast(result.message, 'error');
                }
            })
            .catch(() => showToast('An error occurred.', 'error'));
    }

    // ==================== HISTORY ====================
    function viewHistory(assetId) {
        const content = document.getElementById('historyContent');
        content.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>';
        new bootstrap.Modal(document.getElementById('historyModal')).show();

        fetch(`${API}?action=get_assignment_history&asset_id=${assetId}`)
            .then(r => r.json())
            .then(result => {
                if (result.success && result.data.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Employee</th><th>Assigned Date</th><th>Returned</th><th>Condition (Out/In)</th><th>Status</th></tr></thead><tbody>';
                    result.data.forEach(a => {
                        const statusBadge = a.status === 'Active'
                            ? '<span class="badge bg-success-subtle text-success-emphasis">Active</span>'
                            : '<span class="badge bg-secondary-subtle text-secondary-emphasis">Returned</span>';
                        html += `<tr>
                        <td>${escapeHTML(a.employee_name)}</td>
                        <td>${a.assigned_date}</td>
                        <td>${a.actual_return_date || '-'}</td>
                        <td>${a.condition_on_assignment || '-'} / ${a.condition_on_return || '-'}</td>
                        <td>${statusBadge}</td>
                    </tr>`;
                    });
                    html += '</tbody></table></div>';
                    content.innerHTML = html;
                } else {
                    content.innerHTML = '<p class="text-center text-muted p-4">No assignment history for this asset.</p>';
                }
            });
    }

    // ==================== CATEGORY CRUD ====================
    function openAddCategoryModal() {
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '0';
        document.getElementById('categoryFormAction').value = 'add_category';
        document.getElementById('categoryModalLabel').textContent = 'Add Category';
        new bootstrap.Modal(document.getElementById('categoryModal')).show();
    }

    function openEditCategoryModal(r) {
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = r.id;
        document.getElementById('categoryFormAction').value = 'edit_category';
        document.getElementById('categoryModalLabel').textContent = 'Edit Category';
        document.getElementById('categoryName').value = r.name;
        document.getElementById('categoryType').value = r.type;
        document.getElementById('categoryDescription').value = r.description || '';
        new bootstrap.Modal(document.getElementById('categoryModal')).show();
    }

    function handleCategorySubmit(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch(API, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    showToast(result.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
                    categoriesTable.ajax.reload();
                    loadCategories(); // Refresh dropdown too
                } else {
                    showToast(result.message, 'error');
                }
            })
            .catch(() => showToast('An error occurred.', 'error'));
    }

    function deleteCategory(id, name) {
        showConfirmationModal(
            `Are you sure you want to delete category <strong>${escapeHTML(name)}</strong>?`,
            () => {
                const formData = new FormData();
                formData.append('action', 'delete_category');
                formData.append('category_id', id);
                fetch(API, { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(result => {
                        if (result.success) {
                            showToast(result.message, 'success');
                            categoriesTable.ajax.reload();
                            loadCategories();
                        } else {
                            showToast(result.message, 'error');
                        }
                    });
            },
            'Delete Category', 'Delete', 'btn-danger'
        );
    }
</script>