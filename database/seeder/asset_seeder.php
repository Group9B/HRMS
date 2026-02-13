<?php
// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
$host = 'localhost';
$db = 'original_template';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    echo "Connecting to $db...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Fetch First 3 Companies
    $stmt = $pdo->query("SELECT id, name FROM companies ORDER BY id ASC LIMIT 3");
    $companies = $stmt->fetchAll();

    foreach ($companies as $company) {
        $cid = $company['id'];
        $cname = $company['name'];
        echo "\nSeeding Assets for Company: $cname (ID: $cid)...\n";

        // 1. Create Asset Categories
        $categories = [
            ['name' => 'Laptops', 'type' => 'Hardware'],
            ['name' => 'Desktops', 'type' => 'Hardware'],
            ['name' => 'Monitors', 'type' => 'Hardware'],
            ['name' => 'Mobile Phones', 'type' => 'Hardware'],
            ['name' => 'Software Licenses', 'type' => 'Software'],
            ['name' => 'Access Cards', 'type' => 'Access'],
        ];

        $cat_ids = [];

        foreach ($categories as $cat) {
            $stmt = $pdo->prepare("SELECT id FROM asset_categories WHERE company_id = ? AND name = ?");
            $stmt->execute([$cid, $cat['name']]);
            $id = $stmt->fetchColumn();

            if (!$id) {
                $ins = $pdo->prepare("INSERT INTO asset_categories (company_id, name, type, description) VALUES (?, ?, ?, ?)");
                $ins->execute([$cid, $cat['name'], $cat['type'], "Standard " . $cat['name']]);
                $id = $pdo->lastInsertId();
            }
            $cat_ids[$cat['name']] = $id;
        }

        // 2. Fetch Employees for Assignment
        $stmt = $pdo->prepare("
            SELECT e.id, e.first_name, e.last_name 
            FROM employees e 
            JOIN departments d ON e.department_id = d.id 
            WHERE d.company_id = ? AND e.status = 'active'
        ");
        $stmt->execute([$cid]);
        $employees = $stmt->fetchAll();

        if (empty($employees)) {
            echo "  No employees found. Skipping assignment.\n";
        }

        // 3. Create Assets & Assign
        $asset_types = [
            'Laptops' => ['MacBook Pro M2', 'Dell XPS 15', 'HP EliteBook', 'Lenovo ThinkPad X1'],
            'Desktops' => ['iMac 24"', 'Dell Optiplex', 'HP ProDesk'],
            'Monitors' => ['Dell Ultrasharp 27"', 'LG 24" IPS', 'Samsung Curved 32"'],
            'Mobile Phones' => ['iPhone 14', 'Samsung S23', 'Google Pixel 7'],
            'Software Licenses' => ['Adobe Creative Cloud', 'JetBrains All Products', 'Office 365 E5'],
            'Access Cards' => ['RFID Access Card', 'Biometric ID'],
        ];

        $total_assets = 0;
        $total_assigned = 0;

        foreach ($asset_types as $cat_name => $models) {
            if (!isset($cat_ids[$cat_name]))
                continue;
            $cat_id = $cat_ids[$cat_name];

            // Create 3-5 assets per category
            $count = rand(3, 5);
            for ($i = 0; $i < $count; $i++) {
                $model = $models[array_rand($models)];
                $tag = strtoupper(substr($cname, 0, 3)) . '-' . strtoupper(substr($cat_name, 0, 3)) . '-' . rand(1000, 9999);
                $serial = strtoupper(bin2hex(random_bytes(6)));
                $cost = rand(5000, 200000); // INR
                $purchase_date = date('Y-m-d', strtotime("-" . rand(1, 365) . " days"));

                // Determine Status: 60% Assigned, 30% Available, 10% Maintenance
                $rand = rand(1, 100);
                if ($rand <= 60 && !empty($employees)) {
                    $status = 'Assigned';
                } elseif ($rand <= 90) {
                    $status = 'Available';
                } else {
                    $status = 'Maintenance';
                }

                // Insert Asset
                $ins = $pdo->prepare("
                    INSERT INTO assets 
                    (company_id, category_id, asset_name, asset_tag, serial_number, purchase_date, purchase_cost, status, condition_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'New')
                ");
                $ins->execute([$cid, $cat_id, $model, $tag, $serial, $purchase_date, $cost, $status]);
                $asset_id = $pdo->lastInsertId();
                $total_assets++;

                // Assign if status is Assigned
                if ($status == 'Assigned') {
                    $emp = $employees[array_rand($employees)];
                    $assigned_date = date('Y-m-d', strtotime("$purchase_date + " . rand(1, 30) . " days"));
                    if ($assigned_date > date('Y-m-d'))
                        $assigned_date = date('Y-m-d');

                    $assign = $pdo->prepare("
                        INSERT INTO asset_assignments 
                        (asset_id, employee_id, assigned_by, assigned_date, status, condition_on_assignment) 
                        VALUES (?, ?, 1, ?, 'Active', 'New')
                    ");
                    $assign->execute([$asset_id, $emp['id'], $assigned_date]);
                    $total_assigned++;
                }
            }
        }
        echo "  Created $total_assets assets, assigned $total_assigned.\n";
    }

    echo "\nSUCCESS: Asset seeding complete.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction())
        $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>