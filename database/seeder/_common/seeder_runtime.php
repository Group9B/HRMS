<?php
/**
 * Shared Seeder Runtime Configuration
 * ------------------------------------
 * Provides a single PDO factory used by all orchestrated seeders.
 * When run via master_seeder.php the $pdo variable is already set;
 * when a seeder is executed standalone it parses CLI flags / env vars.
 *
 * CLI usage:
 *   php seeder.php --host=localhost --db=mydb --user=root --pass=secret
 *
 * Env fallback: DB_HOST, DB_NAME, DB_USER, DB_PASS
 * Hard default:  localhost / original_template / root / (empty)
 */

if (!isset($pdo)) {
    // Parse CLI flags  --key=value
    $cli = [];
    if (php_sapi_name() === 'cli' && isset($argv)) {
        foreach ($argv as $arg) {
            if (preg_match('/^--(\w+)=(.*)$/', $arg, $m)) {
                $cli[$m[1]] = $m[2];
            }
        }
    }

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
    echo "[runtime] Connected to $__db@$__host\n";
}

// ── Role ID constants (matches header.php) ──────────────────────
define('ROLE_ADMIN', 1);
define('ROLE_COMPANY_OWNER', 2);
define('ROLE_HR', 3);
define('ROLE_EMPLOYEE', 4);
define('ROLE_CANDIDATE', 5);
define('ROLE_MANAGER', 6);

// Roles that seeders are allowed to assign to users
define('ALLOWED_SEED_ROLES', [ROLE_COMPANY_OWNER, ROLE_HR, ROLE_EMPLOYEE, ROLE_MANAGER]);

/**
 * Utility: simple coloured log for CLI
 */
function seeder_log(string $msg): void
{
    echo "[seeder] $msg\n";
}
