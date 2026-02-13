<?php
// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$result_file = __DIR__ . '/seed_result.txt';
file_put_contents($result_file, "Starting Seeder...\n");

function logMsg($msg)
{
    global $result_file;
    echo $msg . "\n";
    file_put_contents($result_file, $msg . "\n", FILE_APPEND);
}

// Database Configuration - Try original_template first
$host = 'localhost';
$db = 'original_template'; // Attempting this DB
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    logMsg("Connecting to $db at $host...");
    $pdo = new PDO($dsn, $user, $pass, $options);
    logMsg("Connected successfully.");

    // Indian Business Data Sources (SAME AS BEFORE)
    $prefixes = [
        'Surya',
        'Chandra',
        'Veda',
        'Ganga',
        'Yamuna',
        'Indus',
        'Kaveri',
        'Godavari',
        'Krishna',
        'Narmada',
        'Himalaya',
        'Vindhya',
        'Satpura',
        'Aravali',
        'Deccan',
        'Konkan',
        'Malwa',
        'Bharat',
        'Hindustan',
        'Shakti',
        'Pragati',
        'Vikas',
        'Udyog',
        'Vyapar',
        'Nirman',
        'Sarvottam',
        'Shreshth',
        'Utkarsh',
        'Nav',
        'Navyug',
        'Navbharat',
        'Jai',
        'Vijay',
        'Amrit',
        'Anand',
        'Ashirwad',
        'Shubh',
        'Labh',
        'Dhan',
        'Lakshmi',
        'Saraswati',
        'Durga',
        'Kali',
        'Shiva',
        'Vishnu',
        'Brahma',
        'Ram',
        'Hanuman',
        'Ganesh',
        'Aarav',
        'Vihaan',
        'Aditya',
        'Sai',
        'Arjun',
        'Rohan',
        'Karan',
        'Aryan',
        'Ishaan'
    ];

    $suffixes = [
        'Enterprises',
        'Solutions',
        'Technologies',
        'Systems',
        'Industries',
        'Group',
        'Consulting',
        'Services',
        'Logistics',
        'Ventures',
        'Holdings',
        'Corporation',
        'Agency',
        'Associates',
        'Partners',
        'Traders',
        'Exports',
        'Imports',
        'Textiles',
        'Engineering',
        'Works',
        'Construct',
        'Realty'
    ];

    $cities = [
        ['Mumbai', 'Maharashtra', '400'],
        ['Delhi', 'Delhi', '110'],
        ['Bangalore', 'Karnataka', '560'],
        ['Hyderabad', 'Telangana', '500'],
        ['Ahmedabad', 'Gujarat', '380'],
        ['Chennai', 'Tamil Nadu', '600'],
        ['Kolkata', 'West Bengal', '700'],
        ['Surat', 'Gujarat', '395'],
        ['Pune', 'Maharashtra', '411'],
        ['Jaipur', 'Rajasthan', '302'],
        ['Lucknow', 'Uttar Pradesh', '226'],
        ['Kanpur', 'Uttar Pradesh', '208'],
        ['Nagpur', 'Maharashtra', '440'],
        ['Indore', 'Madhya Pradesh', '452'],
        ['Thane', 'Maharashtra', '400'],
        ['Bhopal', 'Madhya Pradesh', '462'],
        ['Visakhapatnam', 'Andhra Pradesh', '530'],
        ['Patna', 'Bihar', '800'],
        ['Vadodara', 'Gujarat', '390'],
        ['Ghaziabad', 'Uttar Pradesh', '201']
    ];

    $areas = ['Industrial Area', 'Tech Park', 'Business Hub', 'Market', 'GIDC', 'MIDC', 'Phase 1', 'Phase 2', 'Sector 5', 'Sector 62', 'Main Road', 'Station Road', 'Ring Road'];

    // Generate 55 unique companies
    $target_count = 55;
    $generated_count = 0;

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO companies (name, email, phone, address, subscription_status) VALUES (?, ?, ?, ?, 'active')");

    logMsg("Seeding $target_count companies...");

    for ($i = 0; $i < $target_count; $i++) {
        // Generate Name
        $prefix = $prefixes[array_rand($prefixes)];
        $suffix = $suffixes[array_rand($suffixes)];
        $name = "$prefix $suffix";
        if (rand(0, 10) > 7) {
            $name .= " " . rand(1, 99);
        }

        // Generate Email
        $domain_slug = strtolower(str_replace(' ', '', $name)) . rand(1, 999);
        $email = "info@" . $domain_slug . ".in";

        // Generate Phone (+91 7/8/9xxxx xxxxx)
        $phone = "+91 " . rand(7, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . " " . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);

        // Generate Address
        $city_data = $cities[array_rand($cities)];
        $city = $city_data[0];
        $state = $city_data[1];
        $pincode_prefix = $city_data[2];
        $pincode = $pincode_prefix . rand(100, 999);
        $area = $areas[array_rand($areas)];
        $plot = rand(1, 500);
        $address = "Plot No $plot, $area, $city, $state - $pincode";

        try {
            $stmt->execute([$name, $email, $phone, $address]);
            $generated_count++;
        } catch (PDOException $e) {
            logMsg("Skipped duplicate or error: " . $e->getMessage());
        }
    }

    $pdo->commit();
    logMsg("SUCCESS: Inserted $generated_count Indian companies into original_template.");

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logMsg("Seeding Failed: " . $e->getMessage());
}
?>