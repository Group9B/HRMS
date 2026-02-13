<?php
/**
 * Asset Category Seeder
 * Run this script once to populate default asset categories for all existing companies.
 * Usage: php database/seed_asset_categories.php
 * Or visit: /hrms/database/seed_asset_categories.php
 */
// die(__DIR__);
require_once __DIR__ . '/../../config/db.php';

$default_categories = [
    // Hardware
    ['name' => 'Laptop', 'type' => 'Hardware', 'description' => 'Portable computer'],
    ['name' => 'Desktop', 'type' => 'Hardware', 'description' => 'Desktop computer'],
    ['name' => 'Monitor', 'type' => 'Hardware', 'description' => 'Display monitor'],
    ['name' => 'Keyboard', 'type' => 'Hardware', 'description' => 'Input keyboard'],
    ['name' => 'Mouse', 'type' => 'Hardware', 'description' => 'Input mouse'],
    ['name' => 'Docking Station', 'type' => 'Hardware', 'description' => 'Laptop docking station'],
    ['name' => 'Headset', 'type' => 'Hardware', 'description' => 'Audio headset'],
    ['name' => 'Webcam', 'type' => 'Hardware', 'description' => 'Video camera'],
    ['name' => 'Mobile Phone', 'type' => 'Hardware', 'description' => 'Company mobile phone'],
    ['name' => 'Tablet', 'type' => 'Hardware', 'description' => 'Tablet device'],
    ['name' => 'External Hard Drive', 'type' => 'Hardware', 'description' => 'External storage device'],
    ['name' => 'Power Adapter', 'type' => 'Hardware', 'description' => 'Power adapter/charger'],
    // Software
    ['name' => 'Windows License', 'type' => 'Software', 'description' => 'Microsoft Windows OS license'],
    ['name' => 'macOS License', 'type' => 'Software', 'description' => 'Apple macOS license'],
    ['name' => 'Microsoft 365 License', 'type' => 'Software', 'description' => 'Microsoft 365 subscription'],
    ['name' => 'Adobe License', 'type' => 'Software', 'description' => 'Adobe Creative Suite license'],
    ['name' => 'IDE / Developer Tool License', 'type' => 'Software', 'description' => 'Development environment license'],
    ['name' => 'VPN Client License', 'type' => 'Software', 'description' => 'VPN client software license'],
    // Access
    ['name' => 'SaaS Application Account', 'type' => 'Access', 'description' => 'SaaS platform account'],
    ['name' => 'Company Email Account', 'type' => 'Access', 'description' => 'Corporate email account'],
    ['name' => 'SSO / Directory Account', 'type' => 'Access', 'description' => 'Single sign-on / directory access'],
    ['name' => 'VPN Access', 'type' => 'Access', 'description' => 'VPN network access credentials'],
    ['name' => 'Git Repository Access', 'type' => 'Access', 'description' => 'Source code repository access'],
    ['name' => 'Cloud Console Access', 'type' => 'Access', 'description' => 'Cloud platform console access'],
    ['name' => 'ERP / Internal System Access', 'type' => 'Access', 'description' => 'Internal system access'],
    // Security
    ['name' => 'Smart Card', 'type' => 'Security', 'description' => 'Smart card for authentication'],
    ['name' => 'RFID Access Card', 'type' => 'Security', 'description' => 'RFID-based access card'],
    ['name' => 'Biometric Token', 'type' => 'Security', 'description' => 'Biometric authentication token'],
    ['name' => 'Hardware Security Key', 'type' => 'Security', 'description' => 'Physical security key (e.g., YubiKey)'],
    ['name' => 'Employee ID Card', 'type' => 'Security', 'description' => 'Company ID badge'],
    ['name' => 'Locker Key', 'type' => 'Security', 'description' => 'Physical locker key'],
    // Other
    ['name' => 'Company Vehicle', 'type' => 'Other', 'description' => 'Company-owned vehicle'],
    ['name' => 'Specialized Tools / Instruments', 'type' => 'Other', 'description' => 'Specialized work tools'],
];

// Get all companies
$companies = $mysqli->query("SELECT id, name FROM companies");
if (!$companies || $companies->num_rows === 0) {
    echo "No companies found.\n";
    exit;
}

$inserted = 0;
$skipped = 0;

while ($company = $companies->fetch_assoc()) {
    $company_id = $company['id'];
    echo "Seeding categories for Company: {$company['name']} (ID: {$company_id})\n";

    foreach ($default_categories as $cat) {
        $stmt = $mysqli->prepare("INSERT IGNORE INTO asset_categories (company_id, name, type, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $company_id, $cat['name'], $cat['type'], $cat['description']);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $inserted++;
            } else {
                $skipped++;
            }
        }
        $stmt->close();
    }
}

echo "\nDone! Inserted: {$inserted}, Skipped (already exist): {$skipped}\n";