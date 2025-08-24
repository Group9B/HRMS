<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "Company Management";

// --- INITIAL DATA FETCHING ---
// The only PHP needed now is to get the initial list of companies for the page load.
$companies_result = query($mysqli, "SELECT * FROM companies ORDER BY created_at DESC");
$companies = $companies_result['success'] ? $companies_result['data'] : [];

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-building me-2"></i>Company Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#companyModal"
                onclick="prepareAddModal()">
                <i class="fas fa-plus me-2"></i>Add Company
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="companiesTable">
                        <thead class="">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $company): ?>
                                <tr id="company-row-<?= $company['id']; ?>">
                                    <td><strong><?= htmlspecialchars($company['name']); ?></strong></td>
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
    </div>
</div>

<!-- Company Modal -->
<div class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="companyForm">
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

<!-- Toast Notification -->
<div id="toast" class="toast-notification"></div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    let companyModal;
    let companiesTable;

    $(function () {
        companiesTable = $('#companiesTable').DataTable({ order: [[3, 'desc']] });
        companyModal = new bootstrap.Modal(document.getElementById('companyModal'));

        // Handle form submission with AJAX
        $('#companyForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/hrms/api/api_companies.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        companyModal.hide();

                        const action = $('#formAction').val();
                        if (action === 'add') {
                            addCompanyRow(data.company);
                        } else {
                            updateCompanyRow(data.company);
                        }
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An unexpected error occurred.', 'error');
                });
        });
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
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('company_id', companyId);

            fetch('/hrms/api/api_companies.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        companiesTable.row(`#company-row-${companyId}`).remove().draw();
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    }

    // --- Helper functions to dynamically update the DataTable ---

    function createCompanyRowHTML(company) {
        const createdDate = new Date(company.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        return `
            <td><strong>${escapeHTML(company.name)}</strong></td>
            <td>${escapeHTML(company.email)}</td>
            <td>${escapeHTML(company.phone)}</td>
            <td>${createdDate}</td>
            <td>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick='prepareEditModal(${JSON.stringify(company)})' data-bs-toggle="modal" data-bs-target="#companyModal"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCompany(${company.id})"><i class="fas fa-trash"></i></button>
                </div>
            </td>
        `;
    }

    function addCompanyRow(company) {
        const newRowNode = companiesTable.row.add($(
            `<tr id="company-row-${company.id}">${createCompanyRowHTML(company)}</tr>`
        )).draw().node();
        // Add a temporary highlight to the new row for better UX
        $(newRowNode).addClass('table-success').delay(2000).queue(function (next) {
            $(this).removeClass('table-success');
            next();
        });
    }

    function updateCompanyRow(company) {
        const rowNode = companiesTable.row(`#company-row-${company.id}`);
        if (rowNode.length) {
            // Create jQuery object of the new TD elements from the HTML string
            const newCells = $(createCompanyRowHTML(company));

            // Map the jQuery object to a simple array of HTML strings for each cell.
            // This is the format DataTables' .data() method expects.
            const newCellData = newCells.map(function () {
                return $(this).html();
            }).get();

            // Update the row's data with the new array and redraw the table
            rowNode.data(newCellData).draw();

            // Add a temporary highlight to the updated row for better UX
            $(rowNode.node()).addClass('table-info').delay(2000).queue(function (next) {
                $(this).removeClass('table-info');
                next();
            });
        }
    }
</script>