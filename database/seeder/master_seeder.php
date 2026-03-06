<?php
/**
 * ╔══════════════════════════════════════════════════════════════╗
 * ║                   HRMS  Master  Seeder                      ║
 * ╠══════════════════════════════════════════════════════════════╣
 * ║  Orchestrates all seeders in the correct dependency order.  ║
 * ║  The target database is configurable via CLI flags or env.  ║
 * ╚══════════════════════════════════════════════════════════════╝
 *
 * Usage:
 *   php master_seeder.php                                # uses defaults (localhost / original_template / root / '')
 *   php master_seeder.php --db=hrms_live                 # override database name only
 *   php master_seeder.php --host=db.example.com --db=hrms --user=admin --pass=secret
 *   php master_seeder.php --skip=payroll,leave           # skip specific seeders
 *   php master_seeder.php --only=final,company_admin     # run only listed seeders
 *
 * Environment variable fallback: DB_HOST, DB_NAME, DB_USER, DB_PASS
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ── Parse CLI flags ──────────────────────────────────────────
$cli = [];
if (php_sapi_name() === 'cli' && isset($argv)) {
    foreach ($argv as $arg) {
        if (preg_match('/^--(\w+)=(.*)$/', $arg, $m)) {
            $cli[$m[1]] = $m[2];
        }
    }
}

$skipList = isset($cli['skip']) ? array_map('trim', explode(',', $cli['skip'])) : [];
$onlyList = isset($cli['only']) ? array_map('trim', explode(',', $cli['only'])) : [];

// ── Establish shared PDO (picked up by seeder_runtime.php) ───
$__host = $cli['host'] ?? getenv('DB_HOST') ?: 'localhost';
$__db = $cli['db'] ?? getenv('DB_NAME') ?: 'original_template';
$__user = $cli['user'] ?? getenv('DB_USER') ?: 'root';
$__pass = $cli['pass'] ?? getenv('DB_PASS') ?: '';

$dsn = "mysql:host=$__host;dbname=$__db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, $__user, $__pass, $options);

echo "═══════════════════════════════════════════════════\n";
echo " HRMS Master Seeder\n";
echo " Database : $__db @ $__host\n";
echo " Started  : " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════\n\n";

// ── Seeder registry (order matters!) ─────────────────────────
// Each entry: [shortName, filePath]
$seeders = [
    ['final', __DIR__ . '/final_seeder.php'],
    ['company_admin', __DIR__ . '/company_admin_seeder.php'],
    ['detailed_employees', __DIR__ . '/detailed_employees_seeder.php'],
    ['manager', __DIR__ . '/manager_seeder.php'],
    ['teams', __DIR__ . '/teams_seeder.php'],
    ['asset_categories', __DIR__ . '/seed_asset_categories.php'],
    ['asset', __DIR__ . '/asset_seeder.php'],
    ['leave', __DIR__ . '/leave_seeder.php'],
    ['payroll', __DIR__ . '/payroll_seeder.php'],
    ['recruitment', __DIR__ . '/recruitment_seeder.php'],
    ['missing_modules', __DIR__ . '/missing_modules_seeder.php'],
    ['navbharat_activity', __DIR__ . '/navbharat_activity_seeder.php'],
    ['role_sanitizer', __DIR__ . '/role_sanitizer_seeder.php'],
];

// ── Not included (obsolete / diagnostic) ─────────────────────
// auditor_converter.php        — one-off migration, creates invalid roles
// manager_promoter.php         — promotes based on wrong role IDs
// hr_converter.php             — deduplication utility, not repeatable
// indian_companies_seeder.php  — superseded by final_seeder.php
// indian_companies_seeder_retry.php — duplicate of above
// list_demo_users_file.php     — diagnostic only
// list_demo_users.php          — diagnostic only
// check_roles.php              — diagnostic dump
// check_manager_roles.php      — diagnostic query
// verify_seeder.php            — simple count check
// emp_user_cmp_seed.php        — legacy bootstrap with wrong role IDs
// list_roles.php               — diagnostic dump

$passed = 0;
$failed = 0;
$skipped = 0;

foreach ($seeders as [$name, $file]) {
    // --skip filter
    if (in_array($name, $skipList, true)) {
        echo "⏭  SKIP  $name\n";
        $skipped++;
        continue;
    }
    // --only filter
    if ($onlyList && !in_array($name, $onlyList, true)) {
        echo "⏭  SKIP  $name  (not in --only list)\n";
        $skipped++;
        continue;
    }

    echo "───────────────────────────────────────────────────\n";
    echo "▶  Running: $name\n";
    echo "   File:    $file\n";
    echo "───────────────────────────────────────────────────\n";

    if (!file_exists($file)) {
        echo "✗  File not found! Skipping.\n\n";
        $failed++;
        continue;
    }

    $start = microtime(true);
    try {
        // $pdo is already in scope — each seeder's require_once of
        // seeder_runtime.php will detect it and skip reconnection.
        include $file;
        $elapsed = round(microtime(true) - $start, 2);
        echo "✓  Done ($elapsed s)\n\n";
        $passed++;
    } catch (Throwable $e) {
        $elapsed = round(microtime(true) - $start, 2);
        echo "✗  FAILED ($elapsed s): " . $e->getMessage() . "\n";
        echo "   " . $e->getFile() . ":" . $e->getLine() . "\n\n";
        $failed++;
    }
}

echo "═══════════════════════════════════════════════════\n";
echo " Summary:  $passed passed,  $failed failed,  $skipped skipped\n";
echo " Finished: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════\n";
