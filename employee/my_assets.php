<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
$title = "My Assets";

if (!isLoggedIn() || !in_array($_SESSION['role_id'], [4, 6])) {
    redirect("/hrms/pages/unauthorized.php");
}

require_once '../components/layout/header.php';
?>

<div class="d-flex">
    <?php require_once '../components/layout/sidebar.php'; ?>
    <div class="p-3 p-md-4" style="flex: 1;">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">My Assigned Assets</h6>
            </div>
            <div class="card-body">
                <div id="assetsLoading" class="text-center p-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Loading your assets...</p>
                </div>
                <div id="assetsEmpty" class="text-center text-muted p-5" style="display:none;">
                    <i class="ti ti-device-laptop-off" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3">No assets are currently assigned to you.</p>
                </div>
                <div id="assetsContainer" class="row g-3" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/layout/footer.php'; ?>

<script>
    $(function () {
        loadMyAssets();
    });

    function loadMyAssets() {
        fetch('/hrms/api/api_assets.php?action=get_my_assets')
            .then(r => r.json())
            .then(result => {
                document.getElementById('assetsLoading').style.display = 'none';

                if (result.success && result.data.length > 0) {
                    const container = document.getElementById('assetsContainer');
                    container.style.display = 'flex';
                    container.innerHTML = '';

                    result.data.forEach(asset => {
                        const typeIcons = {
                            'Hardware': 'ti-device-laptop',
                            'Software': 'ti-app-window',
                            'Access': 'ti-key',
                            'Security': 'ti-shield-lock',
                            'Other': 'ti-box'
                        };
                        const typeColors = {
                            'Hardware': 'primary',
                            'Software': 'info',
                            'Access': 'warning',
                            'Security': 'danger',
                            'Other': 'secondary'
                        };
                        const conditionColors = {
                            'New': 'success', 'Good': 'info', 'Fair': 'warning', 'Poor': 'danger', 'Damaged': 'danger'
                        };

                        const icon = typeIcons[asset.category_type] || 'ti-box';
                        const color = typeColors[asset.category_type] || 'secondary';
                        const condColor = conditionColors[asset.condition_status] || 'secondary';

                        const card = `
                        <div class="col-md-6 col-lg-4">
                            <div class="card shadow-sm h-100 border-start border-4 border-${color}">
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="rounded-circle bg-${color}-subtle text-${color} d-flex align-items-center justify-content-center" style="width:45px;height:45px;min-width:45px;">
                                            <i class="ti ${icon} fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">${escapeHTML(asset.asset_name)}</h6>
                                            <small class="text-muted">${escapeHTML(asset.category_name)}</small>
                                        </div>
                                    </div>
                                    <div class="small">
                                        ${asset.asset_tag ? `<div class="mb-1"><i class="ti ti-tag me-1 text-muted"></i><strong>Tag:</strong> ${escapeHTML(asset.asset_tag)}</div>` : ''}
                                        ${asset.serial_number ? `<div class="mb-1"><i class="ti ti-barcode me-1 text-muted"></i><strong>SN:</strong> ${escapeHTML(asset.serial_number)}</div>` : ''}
                                        <div class="mb-1"><i class="ti ti-calendar me-1 text-muted"></i><strong>Assigned:</strong> ${asset.assigned_date}</div>
                                        ${asset.expected_return_date ? `<div class="mb-1"><i class="ti ti-calendar-due me-1 text-muted"></i><strong>Return by:</strong> ${asset.expected_return_date}</div>` : ''}
                                        <div class="mt-2">
                                            <span class="badge bg-${condColor}-subtle text-${condColor}-emphasis">${asset.condition_on_assignment} condition</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                        container.innerHTML += card;
                    });
                } else {
                    document.getElementById('assetsEmpty').style.display = 'block';
                }
            })
            .catch(() => {
                document.getElementById('assetsLoading').innerHTML = '<p class="text-danger">Failed to load assets. Please try again.</p>';
            });
    }
</script>