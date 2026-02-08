<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isLoggedIn()) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];
$role_id = $_SESSION['role_id'];

// Helper: check if user is HR/Admin (roles 2 or 3)
$is_admin = in_array($role_id, [2, 3]);

switch ($action) {

    // ==================== CATEGORY MANAGEMENT ====================

    case 'get_categories':
        $sql = "SELECT * FROM asset_categories WHERE company_id = ? ORDER BY type ASC, name ASC";
        $result = query($mysqli, $sql, [$company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch categories.';
        }
        break;

    case 'add_category':
        if (!$is_admin) {
            $response['message'] = 'Permission denied.';
            break;
        }

        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'Other';
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $response['message'] = 'Category name is required.';
            break;
        }

        $allowed_types = ['Hardware', 'Software', 'Access', 'Security', 'Other'];
        if (!in_array($type, $allowed_types)) {
            $response['message'] = 'Invalid category type.';
            break;
        }

        // Check duplicate
        $dup = query($mysqli, "SELECT id FROM asset_categories WHERE company_id = ? AND name = ?", [$company_id, $name]);
        if ($dup['success'] && !empty($dup['data'])) {
            $response['message'] = 'A category with this name already exists.';
            break;
        }

        $sql = "INSERT INTO asset_categories (company_id, name, type, description) VALUES (?, ?, ?, ?)";
        $result = query($mysqli, $sql, [$company_id, $name, $type, $description]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Category added successfully!'];
        } else {
            $response['message'] = 'Failed to add category.';
        }
        break;

    case 'edit_category':
        if (!$is_admin) {
            $response['message'] = 'Permission denied.';
            break;
        }

        $category_id = (int) ($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'Other';
        $description = trim($_POST['description'] ?? '');

        if ($category_id <= 0 || empty($name)) {
            $response['message'] = 'Category ID and name are required.';
            break;
        }

        $allowed_types = ['Hardware', 'Software', 'Access', 'Security', 'Other'];
        if (!in_array($type, $allowed_types)) {
            $response['message'] = 'Invalid category type.';
            break;
        }

        // Check duplicate (excluding self)
        $dup = query($mysqli, "SELECT id FROM asset_categories WHERE company_id = ? AND name = ? AND id != ?", [$company_id, $name, $category_id]);
        if ($dup['success'] && !empty($dup['data'])) {
            $response['message'] = 'A category with this name already exists.';
            break;
        }

        $sql = "UPDATE asset_categories SET name = ?, type = ?, description = ? WHERE id = ? AND company_id = ?";
        $result = query($mysqli, $sql, [$name, $type, $description, $category_id, $company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Category updated successfully!'];
        } else {
            $response['message'] = 'Failed to update category.';
        }
        break;

    case 'delete_category':
        if (!$is_admin) {
            $response['message'] = 'Permission denied.';
            break;
        }

        $category_id = (int) ($_POST['category_id'] ?? 0);
        if ($category_id <= 0) {
            $response['message'] = 'Invalid category ID.';
            break;
        }

        // Check if any assets use this category
        $in_use = query($mysqli, "SELECT COUNT(*) as cnt FROM assets WHERE category_id = ? AND company_id = ?", [$category_id, $company_id]);
        if ($in_use['success'] && $in_use['data'][0]['cnt'] > 0) {
            $response['message'] = 'Cannot delete: this category has ' . $in_use['data'][0]['cnt'] . ' asset(s) linked to it.';
            break;
        }

        $sql = "DELETE FROM asset_categories WHERE id = ? AND company_id = ?";
        $result = query($mysqli, $sql, [$category_id, $company_id]);
        if ($result['success'] && $result['affected_rows'] > 0) {
            $response = ['success' => true, 'message' => 'Category deleted successfully!'];
        } else {
            $response['message'] = 'Failed to delete category or category not found.';
        }
        break;

    // ==================== ASSET MANAGEMENT ====================

    case 'get_assets':
        $sql = "
            SELECT a.*, ac.name as category_name, ac.type as category_type,
                   aa.employee_id as assigned_to_id,
                   CONCAT(e.first_name, ' ', e.last_name) as assigned_to_name
            FROM assets a
            LEFT JOIN asset_categories ac ON a.category_id = ac.id
            LEFT JOIN asset_assignments aa ON a.id = aa.asset_id AND aa.status = 'Active'
            LEFT JOIN employees e ON aa.employee_id = e.id
            WHERE a.company_id = ?
            ORDER BY a.created_at DESC
        ";
        $result = query($mysqli, $sql, [$company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch assets.';
        }
        break;

    case 'get_asset_stats':
        $stats = [];
        $total = query($mysqli, "SELECT COUNT(*) as cnt FROM assets WHERE company_id = ?", [$company_id]);
        $stats['total'] = $total['success'] ? (int) $total['data'][0]['cnt'] : 0;

        $available = query($mysqli, "SELECT COUNT(*) as cnt FROM assets WHERE company_id = ? AND status = 'Available'", [$company_id]);
        $stats['available'] = $available['success'] ? (int) $available['data'][0]['cnt'] : 0;

        $assigned = query($mysqli, "SELECT COUNT(*) as cnt FROM assets WHERE company_id = ? AND status = 'Assigned'", [$company_id]);
        $stats['assigned'] = $assigned['success'] ? (int) $assigned['data'][0]['cnt'] : 0;

        $maintenance = query($mysqli, "SELECT COUNT(*) as cnt FROM assets WHERE company_id = ? AND status IN ('Maintenance', 'Retired', 'Lost')", [$company_id]);
        $stats['other'] = $maintenance['success'] ? (int) $maintenance['data'][0]['cnt'] : 0;

        $response = ['success' => true, 'data' => $stats];
        break;

    case 'add_asset':
        if (!$is_admin) {
            $response['message'] = 'Permission denied.';
            break;
        }

        $category_id = (int) ($_POST['category_id'] ?? 0);
        $asset_name = trim($_POST['asset_name'] ?? '');
        $asset_tag = trim($_POST['asset_tag'] ?? '');
        $serial_number = trim($_POST['serial_number'] ?? '');
        $purchase_date = $_POST['purchase_date'] ?? null;
        $purchase_cost = $_POST['purchase_cost'] ?? null;
        $warranty_expiry = $_POST['warranty_expiry'] ?? null;
        $condition_status = $_POST['condition_status'] ?? 'New';
        $description = trim($_POST['description'] ?? '');

        if (empty($asset_name)) {
            $response['message'] = 'Asset name is required.';
            break;
        }
        if ($category_id <= 0) {
            $response['message'] = 'Please select a category.';
            break;
        }

        // Verify category belongs to company
        $cat_check = query($mysqli, "SELECT id FROM asset_categories WHERE id = ? AND company_id = ?", [$category_id, $company_id]);
        if (!$cat_check['success'] || empty($cat_check['data'])) {
            $response['message'] = 'Invalid category selected.';
            break;
        }

        $sql = "INSERT INTO assets (company_id, category_id, asset_name, asset_tag, serial_number, purchase_date, purchase_cost, warranty_expiry, condition_status, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available')";
        $params = [$company_id, $category_id, $asset_name, $asset_tag ?: null, $serial_number ?: null, $purchase_date ?: null, $purchase_cost ?: null, $warranty_expiry ?: null, $condition_status, $description ?: null];
        $result = query($mysqli, $sql, $params);

        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Asset added successfully!'];
        } else {
            $response['message'] = 'Failed to add asset.';
        }
        break;

    case 'edit_asset':
        if (!$is_admin) {
            $response['message'] = 'Permission denied.';
            break;
        }

        $asset_id = (int) ($_POST['asset_id'] ?? 0);
        $category_id = (int) ($_POST['category_id'] ?? 0);
        $asset_name = trim($_POST['asset_name'] ?? '');
        $asset_tag = trim($_POST['asset_tag'] ?? '');
        $serial_number = trim($_POST['serial_number'] ?? '');
        $purchase_date = $_POST['purchase_date'] ?? null;
        $purchase_cost = $_POST['purchase_cost'] ?? null;
        $warranty_expiry = $_POST['warranty_expiry'] ?? null;
        $incoming_status = $_POST['status'] ?? null; // may be missing when editing an assigned asset
        $condition_status = $_POST['condition_status'] ?? 'Good';
        $description = trim($_POST['description'] ?? '');

        if ($asset_id <= 0 || empty($asset_name) || $category_id <= 0) {
            $response['message'] = 'Asset ID, name, and category are required.';
            break;
        }

        // Fetch current asset status to avoid accidental override to "Available" when status is not sent
        $currentAsset = query($mysqli, "SELECT status FROM assets WHERE id = ? AND company_id = ?", [$asset_id, $company_id]);
        if (!$currentAsset['success'] || empty($currentAsset['data'])) {
            $response['message'] = 'Asset not found.';
            break;
        }
        $existing_status = $currentAsset['data'][0]['status'];

        // If there is an active assignment, force status to Assigned regardless of incoming value
        $activeAssign = query($mysqli, "SELECT COUNT(*) as cnt FROM asset_assignments WHERE asset_id = ? AND status = 'Active'", [$asset_id]);
        $has_active_assignment = $activeAssign['success'] && (int) $activeAssign['data'][0]['cnt'] > 0;

        $resolved_status = $has_active_assignment
            ? 'Assigned'
            : ($incoming_status ? $incoming_status : $existing_status);

        $sql = "UPDATE assets SET category_id = ?, asset_name = ?, asset_tag = ?, serial_number = ?, purchase_date = ?, purchase_cost = ?, warranty_expiry = ?, status = ?, condition_status = ?, description = ? WHERE id = ? AND company_id = ?";
        $params = [$category_id, $asset_name, $asset_tag ?: null, $serial_number ?: null, $purchase_date ?: null, $purchase_cost ?: null, $warranty_expiry ?: null, $resolved_status, $condition_status, $description ?: null, $asset_id, $company_id];
        $result = query($mysqli, $sql, $params);

        if ($result['success']) {
            $response = ['success' => true, 'message' => 'Asset updated successfully!'];
        } else {
            $response['message'] = 'Failed to update asset.';
        }
        break;

    case 'delete_asset':
        if (!$is_admin) {
            $response['message'] = 'Permission denied.';
            break;
        }

        $asset_id = (int) ($_POST['asset_id'] ?? 0);
        if ($asset_id <= 0) {
            $response['message'] = 'Invalid asset ID.';
            break;
        }

        // Check for active assignments
        $active = query($mysqli, "SELECT COUNT(*) as cnt FROM asset_assignments WHERE asset_id = ? AND status = 'Active'", [$asset_id]);
        if ($active['success'] && $active['data'][0]['cnt'] > 0) {
            $response['message'] = 'Cannot delete: this asset is currently assigned to an employee. Return it first.';
            break;
        }

        $sql = "DELETE FROM assets WHERE id = ? AND company_id = ?";
        $result = query($mysqli, $sql, [$asset_id, $company_id]);
        if ($result['success'] && $result['affected_rows'] > 0) {
            $response = ['success' => true, 'message' => 'Asset deleted successfully!'];
        } else {
            $response['message'] = 'Failed to delete asset.';
        }
        break;

    // ==================== ASSIGNMENT MANAGEMENT ====================

    case 'assign_asset':
        if (!$is_admin) {
            $response['message'] = 'Permission denied.';
            break;
        }

        $asset_id = (int) ($_POST['asset_id'] ?? 0);
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        $assigned_date = $_POST['assigned_date'] ?? date('Y-m-d');
        $expected_return_date = $_POST['expected_return_date'] ?? null;
        $condition_on_assignment = $_POST['condition_on_assignment'] ?? 'Good';
        $remarks = trim($_POST['remarks'] ?? '');

        if ($asset_id <= 0 || $employee_id <= 0) {
            $response['message'] = 'Asset and employee are required.';
            break;
        }

        // Verify asset belongs to company and is available
        $asset_check = query($mysqli, "SELECT id, status FROM assets WHERE id = ? AND company_id = ?", [$asset_id, $company_id]);
        if (!$asset_check['success'] || empty($asset_check['data'])) {
            $response['message'] = 'Asset not found.';
            break;
        }
        if ($asset_check['data'][0]['status'] !== 'Available') {
            $response['message'] = 'This asset is not available for assignment (current status: ' . $asset_check['data'][0]['status'] . ').';
            break;
        }

        // Verify employee belongs to company
        $emp_check = query($mysqli, "SELECT e.id FROM employees e JOIN departments d ON e.department_id = d.id WHERE e.id = ? AND d.company_id = ?", [$employee_id, $company_id]);
        if (!$emp_check['success'] || empty($emp_check['data'])) {
            $response['message'] = 'Employee not found.';
            break;
        }

        // Create assignment
        $sql = "INSERT INTO asset_assignments (asset_id, employee_id, assigned_by, assigned_date, expected_return_date, condition_on_assignment, remarks, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')";
        $result = query($mysqli, $sql, [$asset_id, $employee_id, $user_id, $assigned_date, $expected_return_date ?: null, $condition_on_assignment, $remarks ?: null]);

        if ($result['success']) {
            // Update asset status
            query($mysqli, "UPDATE assets SET status = 'Assigned' WHERE id = ?", [$asset_id]);
            $response = ['success' => true, 'message' => 'Asset assigned successfully!'];
        } else {
            $response['message'] = 'Failed to assign asset.';
        }
        break;

    case 'return_asset':
        if (!$is_admin) {
            $response['message'] = 'Permission denied.';
            break;
        }

        $assignment_id = (int) ($_POST['assignment_id'] ?? 0);
        $condition_on_return = $_POST['condition_on_return'] ?? 'Good';
        $remarks = trim($_POST['remarks'] ?? '');

        if ($assignment_id <= 0) {
            $response['message'] = 'Invalid assignment ID.';
            break;
        }

        // Get assignment details
        $assignment = query($mysqli, "
            SELECT aa.*, a.company_id 
            FROM asset_assignments aa 
            JOIN assets a ON aa.asset_id = a.id 
            WHERE aa.id = ? AND a.company_id = ? AND aa.status = 'Active'
        ", [$assignment_id, $company_id]);

        if (!$assignment['success'] || empty($assignment['data'])) {
            $response['message'] = 'Active assignment not found.';
            break;
        }

        $asset_id = $assignment['data'][0]['asset_id'];

        // Update assignment
        $sql = "UPDATE asset_assignments SET status = 'Returned', actual_return_date = ?, condition_on_return = ?, remarks = CONCAT(IFNULL(remarks, ''), ?) WHERE id = ?";
        $return_remark = $remarks ? "\n[Return Note] " . $remarks : '';
        $result = query($mysqli, $sql, [date('Y-m-d'), $condition_on_return, $return_remark, $assignment_id]);

        if ($result['success']) {
            // Update asset status back to Available (or Damaged based on condition)
            $new_status = in_array($condition_on_return, ['Poor', 'Damaged']) ? 'Maintenance' : 'Available';
            query($mysqli, "UPDATE assets SET status = ?, condition_status = ? WHERE id = ?", [$new_status, $condition_on_return, $asset_id]);
            $response = ['success' => true, 'message' => 'Asset returned successfully!'];
        } else {
            $response['message'] = 'Failed to return asset.';
        }
        break;

    case 'get_assignment_history':
        $asset_id = (int) ($_GET['asset_id'] ?? 0);
        if ($asset_id <= 0) {
            $response['message'] = 'Invalid asset ID.';
            break;
        }

        $sql = "
            SELECT aa.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, u.username as assigned_by_name
            FROM asset_assignments aa
            JOIN employees e ON aa.employee_id = e.id
            LEFT JOIN users u ON aa.assigned_by = u.id
            JOIN assets a ON aa.asset_id = a.id
            WHERE aa.asset_id = ? AND a.company_id = ?
            ORDER BY aa.assigned_date DESC
        ";
        $result = query($mysqli, $sql, [$asset_id, $company_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch assignment history.';
        }
        break;

    // ==================== EMPLOYEE VIEW ====================

    case 'get_my_assets':
        $emp_result = query($mysqli, "SELECT id FROM employees WHERE user_id = ?", [$user_id]);
        if (!$emp_result['success'] || empty($emp_result['data'])) {
            $response['message'] = 'Employee profile not found.';
            break;
        }
        $employee_id = $emp_result['data'][0]['id'];

        $sql = "
            SELECT a.asset_name, a.asset_tag, a.serial_number, a.condition_status,
                   ac.name as category_name, ac.type as category_type,
                   aa.assigned_date, aa.expected_return_date, aa.condition_on_assignment, aa.remarks
            FROM asset_assignments aa
            JOIN assets a ON aa.asset_id = a.id
            JOIN asset_categories ac ON a.category_id = ac.id
            WHERE aa.employee_id = ? AND aa.status = 'Active'
            ORDER BY aa.assigned_date DESC
        ";
        $result = query($mysqli, $sql, [$employee_id]);
        if ($result['success']) {
            $response = ['success' => true, 'data' => $result['data']];
        } else {
            $response['message'] = 'Failed to fetch your assets.';
        }
        break;

    default:
        $response['message'] = 'Invalid action specified.';
        break;
}

echo json_encode($response);
exit();