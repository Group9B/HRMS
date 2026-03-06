<?php
/**
 * Role Sanitizer Seeder
 * ---------------------
 * Run AFTER all other seeders to enforce two invariants:
 *   1. No user may have the Auditor role (id=5 in SQL template) → reassign to Employee (4).
 *   2. Each company must have at most ONE Company Owner (role_id=2).
 *      Duplicates are demoted to HR (role_id=3).
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/_common/seeder_runtime.php';

try {
    seeder_log("Role Sanitizer started.");

    // ── 1. Purge Auditor role ────────────────────────────────────
    // The SQL template defines role_id=5 as 'Auditor', but the app
    // uses role_id=5 as 'Candidate'.  Any user accidentally seeded
    // with the old Auditor role must be converted to Employee (4).
    $stmt = $pdo->prepare("UPDATE users SET role_id = ? WHERE role_id = 5 AND role_id NOT IN (" . implode(',', ALLOWED_SEED_ROLES) . ")");
    // Simpler: just check for anyone with role_id=5
    $auditors = $pdo->query("SELECT id, username, company_id FROM users WHERE role_id = 5")->fetchAll();
    if ($auditors) {
        $upd = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
        foreach ($auditors as $a) {
            $upd->execute([ROLE_EMPLOYEE, $a['id']]);
            seeder_log("  Converted auditor user #{$a['id']} ({$a['username']}) → Employee");
        }
        seeder_log("  Total auditor conversions: " . count($auditors));
    } else {
        seeder_log("  No auditor-role users found.");
    }

    // ── 2. Deduplicate Company Owners ────────────────────────────
    // Keep the FIRST owner (lowest user.id) per company; demote extras to HR.
    $dupes = $pdo->query("
        SELECT u.id, u.username, u.company_id
        FROM users u
        WHERE u.role_id = 2
          AND u.id NOT IN (
              SELECT MIN(u2.id) FROM users u2 WHERE u2.role_id = 2 GROUP BY u2.company_id
          )
    ")->fetchAll();

    if ($dupes) {
        $upd = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
        foreach ($dupes as $d) {
            $upd->execute([ROLE_HR, $d['id']]);
            seeder_log("  Demoted duplicate owner #{$d['id']} ({$d['username']}) in company #{$d['company_id']} → HR");
        }
        seeder_log("  Total owner demotions: " . count($dupes));
    } else {
        seeder_log("  No duplicate owners found.");
    }

    seeder_log("Role Sanitizer complete.");

} catch (Exception $e) {
    seeder_log("ERROR: " . $e->getMessage());
}
