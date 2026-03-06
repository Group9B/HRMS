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

<div class="d-flex flex-column flex-md-row">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-2 p-md-4 w-100">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Companies</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#companyModal"
                    onclick="prepareAddModal()">
                    <i class="ti ti-plus me-2"></i>Add Company
                </button>
            </div>
            <div class="card-body p-2 p-md-3">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered nowrap" id="companiesTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Created At</th>
                                <th class="text-end">Actions</th>
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
                                        <div class="action-dropdown text-end" data-company='<?= json_encode($company); ?>'>
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

                    <!-- Validation errors -->
                    <div id="validationErrors" class="alert alert-danger d-none" role="alert"></div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required maxlength="100"
                            placeholder="Enter company name">
                        <small class="form-text text-muted">Company name (2-100 characters)</small>
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" maxlength="255" rows="2"
                            placeholder="Enter company address"></textarea>
                        <small class="form-text text-muted">Optional (max 255 characters)</small>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" maxlength="100"
                            placeholder="company@example.com">
                        <small class="form-text text-muted">Optional (valid email format required)</small>
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" maxlength="20"
                            placeholder="+91 123-456-7890">
                        <small class="form-text text-muted">Optional (10-15 digits/characters)</small>
                        <div class="invalid-feedback" id="phoneError"></div>
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

<!-- IoT Devices Modal -->
<div class="modal fade" id="devicesModal" tabindex="-1" aria-labelledby="devicesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="devicesModalLabel">IoT Devices</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="devicesCompanyId" value="0">

                <!-- Add Device Form -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Add New Device</h6>
                        <form id="addDeviceForm" class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label for="deviceName" class="form-label">Device Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="deviceName" name="device_name" required
                                    maxlength="100" placeholder="e.g. Main Entrance Scanner">
                            </div>
                            <div class="col-md-4">
                                <label for="deviceLocation" class="form-label">Location</label>
                                <input type="text" class="form-control" id="deviceLocation" name="location"
                                    maxlength="100" placeholder="e.g. Front Gate">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100"><i class="ti ti-plus me-1"></i>Add
                                    Device</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Devices List -->
                <div id="devicesListContainer">
                    <div class="text-center text-muted py-4" id="devicesLoading">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading devices...
                    </div>
                    <div class="table-responsive d-none" id="devicesTableWrap">
                        <table class="table table-hover table-bordered table-sm" id="devicesTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Token</th>
                                    <th>Last Heartbeat</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="devicesTableBody"></tbody>
                        </table>
                    </div>
                    <div class="text-center text-muted py-4 d-none" id="devicesEmpty">
                        <i class="ti ti-device-desktop-off fs-1 d-block mb-2"></i>No IoT devices registered for this
                        company.
                    </div>
                </div>
            </div>
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
        // Check if device width is less than 768px (Bootstrap's md breakpoint)
        var isMobile = window.innerWidth < 768;

        companiesTable = $('#companiesTable').DataTable({
            order: [[3, 'desc']],
            responsive: false,
            scrollX: isMobile, // Enable scrollX only on mobile
            columnDefs: [
                {
                    targets: 4, // Actions column (0-indexed)
                    sortable: false,
                    searchable: false
                }
            ]
        });
        companyModal = new bootstrap.Modal(document.getElementById('companyModal'));

        initializeActionDropdowns();

        // Handle form submission with AJAX
        $('#companyForm').on('submit', function (e) {
            e.preventDefault();

            // Clear previous validation errors
            $('#validationErrors').addClass('d-none').html('');
            $('#companyForm').removeClass('was-validated');

            // Validate form
            if (!validateCompanyForm()) {
                return;
            }

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
                        // Display backend validation errors
                        if (data.errors && typeof data.errors === 'object') {
                            displayValidationErrors(data.errors);
                        } else {
                            showToast(data.message, 'error');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An unexpected error occurred.', 'error');
                });
        });
    });

    function initializeActionDropdowns() {
        $('.action-dropdown').each(function () {
            const company = JSON.parse($(this).attr('data-company'));
            const dropdownHTML = createActionDropdown(
                {
                    onManage: function () {
                        openDevicesModal(company);
                    },
                    onEdit: function () {
                        prepareEditModal(company);
                        companyModal.show();
                    },
                    onDelete: function () {
                        deleteCompany(company.id);
                    }
                },
                {
                    manageTooltip: 'IoT Devices',
                    manageIcon: 'ti ti-device-desktop-analytics',
                    editTooltip: 'Edit Company',
                    deleteTooltip: 'Delete Company'
                }
            );
            $(this).html(dropdownHTML);
        });
    }

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
        showConfirmationModal(
            'Are you sure you want to delete this company? This action cannot be undone.',
            function () {
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
            },
            'Delete Company',
            'Delete',
            'btn-danger'
        );
    }


    function createCompanyRowHTML(company) {
        const createdDate = new Date(company.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        return `
            <td><strong>${escapeHTML(company.name)}</strong></td>
            <td>${escapeHTML(company.email)}</td>
            <td>${escapeHTML(company.phone)}</td>
            <td>${createdDate}</td>
            <td>
                <div class="action-dropdown text-end" data-company='${JSON.stringify(company)}'>
                </div>
            </td>
        `;
    }

    function addCompanyRow(company) {
        const newRowNode = companiesTable.row.add($(
            `<tr id="company-row-${company.id}">${createCompanyRowHTML(company)}</tr>`
        )).draw().node();

        // Initialize the dropdown for the new row
        initializeActionDropdownForRow($(newRowNode).find('.action-dropdown'));

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

            // Re-initialize the dropdown for the updated row
            const updatedNode = $(`#company-row-${company.id}`);
            initializeActionDropdownForRow(updatedNode.find('.action-dropdown'));

            // Add a temporary highlight to the updated row for better UX
            $(rowNode.node()).addClass('table-info').delay(2000).queue(function (next) {
                $(this).removeClass('table-info');
                next();
            });
        }
    }

    function initializeActionDropdownForRow(dropdownElement) {
        const company = JSON.parse(dropdownElement.attr('data-company'));
        const dropdownHTML = createActionDropdown(
            {
                onManage: function () {
                    openDevicesModal(company);
                },
                onEdit: function () {
                    prepareEditModal(company);
                    companyModal.show();
                },
                onDelete: function () {
                    deleteCompany(company.id);
                }
            },
            {
                manageTooltip: 'IoT Devices',
                manageIcon: 'ti ti-device-desktop-analytics',
                editTooltip: 'Edit Company',
                deleteTooltip: 'Delete Company'
            }
        );
        dropdownElement.html(dropdownHTML);
    }

    // --- VALIDATION FUNCTIONS ---

    function validateCompanyForm() {
        const errors = {};
        const name = $('#name').val().trim();
        const email = $('#email').val().trim();
        const phone = $('#phone').val().trim();

        // Name validation
        if (!name) {
            errors.name = 'Company name is required.';
        } else if (name.length < 2) {
            errors.name = 'Company name must be at least 2 characters.';
        } else if (name.length > 100) {
            errors.name = 'Company name must not exceed 100 characters.';
        }

        // Email validation (if provided)
        if (email && !isValidEmail(email)) {
            errors.email = 'Please enter a valid email address.';
        } else if (email && email.length > 100) {
            errors.email = 'Email must not exceed 100 characters.';
        }

        // Phone validation (if provided)
        if (phone && !isValidPhone(phone)) {
            errors.phone = 'Phone must be between 10-20 digits/characters.';
        }

        if (Object.keys(errors).length > 0) {
            displayValidationErrors(errors);
            return false;
        }

        return true;
    }

    function displayValidationErrors(errors) {
        const errorContainer = $('#validationErrors');
        let errorHTML = '<strong>Please fix the following errors:</strong><ul class="mb-0">';

        Object.keys(errors).forEach(field => {
            errorHTML += `<li>${escapeHTML(errors[field])}</li>`;

            // Also add to individual field feedback
            if (field === 'name') {
                $('#nameError').text(errors[field]);
                $('#name').addClass('is-invalid');
            } else if (field === 'email') {
                $('#emailError').text(errors[field]);
                $('#email').addClass('is-invalid');
            } else if (field === 'phone') {
                $('#phoneError').text(errors[field]);
                $('#phone').addClass('is-invalid');
            }
        });

        errorHTML += '</ul>';
        errorContainer.html(errorHTML).removeClass('d-none');

        // Scroll to error
        errorContainer[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function clearValidationErrors() {
        $('#validationErrors').addClass('d-none').html('');
        $('#nameError, #emailError, #phoneError').text('');
        $('#name, #email, #phone').removeClass('is-invalid');
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidPhone(phone) {
        // Allow only digits, spaces, hyphens, parentheses, and plus sign
        const phoneRegex = /^[\d\s\-()\.+]{10,20}$/;
        return phoneRegex.test(phone);
    }

    // Clear errors when modal opens
    $('#companyModal').on('show.bs.modal', function () {
        clearValidationErrors();
    });

    // ─── IoT Device Management ────────────────────────────────
    let devicesModalInstance;
    $(function () {
        devicesModalInstance = new bootstrap.Modal(document.getElementById('devicesModal'));

        $('#addDeviceForm').on('submit', function (e) {
            e.preventDefault();
            const companyId = $('#devicesCompanyId').val();
            const deviceName = $('#deviceName').val().trim();
            const location = $('#deviceLocation').val().trim();

            if (!deviceName) { showToast('Device name is required.', 'error'); return; }

            const fd = new FormData();
            fd.append('action', 'add_device');
            fd.append('company_id', companyId);
            fd.append('device_name', deviceName);
            fd.append('location', location);

            fetch('/hrms/api/api_companies.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        $('#addDeviceForm')[0].reset();
                        loadDevices(companyId);
                    } else {
                        if (data.errors) {
                            const msgs = Object.values(data.errors).join(' ');
                            showToast(msgs, 'error');
                        } else {
                            showToast(data.message, 'error');
                        }
                    }
                })
                .catch(() => showToast('An unexpected error occurred.', 'error'));
        });
    });

    function openDevicesModal(company) {
        $('#devicesCompanyId').val(company.id);
        $('#devicesModalLabel').text('IoT Devices — ' + escapeHTML(company.name));
        $('#addDeviceForm')[0].reset();
        devicesModalInstance.show();
        loadDevices(company.id);
    }

    function loadDevices(companyId) {
        $('#devicesLoading').removeClass('d-none');
        $('#devicesTableWrap, #devicesEmpty').addClass('d-none');

        fetch('/hrms/api/api_companies.php?action=list_devices&company_id=' + encodeURIComponent(companyId))
            .then(r => r.json())
            .then(data => {
                $('#devicesLoading').addClass('d-none');
                if (data.success && data.devices.length > 0) {
                    renderDevicesTable(data.devices);
                    $('#devicesTableWrap').removeClass('d-none');
                } else {
                    $('#devicesEmpty').removeClass('d-none');
                }
            })
            .catch(() => {
                $('#devicesLoading').addClass('d-none');
                $('#devicesEmpty').removeClass('d-none');
            });
    }

    function renderDevicesTable(devices) {
        const tbody = $('#devicesTableBody');
        tbody.empty();
        devices.forEach(d => {
            const statusBadge = d.status === 'active'
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>';
            const heartbeat = d.last_heartbeat
                ? new Date(d.last_heartbeat).toLocaleString()
                : '<span class="text-muted">Never</span>';
            const maskedToken = d.device_token.substring(0, 8) + '…';
            tbody.append(`
                <tr id="device-row-${d.id}">
                    <td>${escapeHTML(d.device_name)}</td>
                    <td>${d.location ? escapeHTML(d.location) : '<span class="text-muted">—</span>'}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <code class="me-1">${escapeHTML(maskedToken)}</code>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-1" title="Copy token" onclick="copyToken('${escapeHTML(d.device_token)}')"><i class="ti ti-copy"></i></button>
                    </td>
                    <td>${heartbeat}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteDevice(${d.id})" title="Delete device"><i class="ti ti-trash"></i></button>
                    </td>
                </tr>
            `);
        });
    }

    function deleteDevice(deviceId) {
        showConfirmationModal(
            'Are you sure you want to delete this IoT device? This cannot be undone.',
            function () {
                const fd = new FormData();
                fd.append('action', 'delete_device');
                fd.append('device_id', deviceId);

                fetch('/hrms/api/api_companies.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            $(`#device-row-${deviceId}`).remove();
                            if ($('#devicesTableBody tr').length === 0) {
                                $('#devicesTableWrap').addClass('d-none');
                                $('#devicesEmpty').removeClass('d-none');
                            }
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(() => showToast('An unexpected error occurred.', 'error'));
            },
            'Delete Device',
            'Delete',
            'btn-danger'
        );
    }

    function copyToken(token) {
        navigator.clipboard.writeText(token).then(() => {
            showToast('Device token copied to clipboard.', 'success');
        }).catch(() => {
            // Fallback for older browsers
            const ta = document.createElement('textarea');
            ta.value = token;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            showToast('Device token copied to clipboard.', 'success');
        });
    }
</script>