<?php
/**
 * User Preferences Migration Script
 * 
 * This is a TEMPORARY script to add user_preferences for all existing users
 * who don't yet have preferences initialized.
 * 
 * Usage:
 * 1. Open this file in browser: http://localhost/HRMS/admin/migrate_preferences.php
 * 2. Click "Migrate Preferences" button
 * 3. Script will add default preferences for all users without them
 * 4. After completion, you can delete this file
 * 
 * SECURITY NOTE: Delete this file after use!
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/preferences.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) { // Assuming role_id 1 is admin
    echo '<div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; margin: 20px;">';
    echo '<h3>⚠️ Access Denied</h3>';
    echo '<p>This script is for administrators only.</p>';
    echo '</div>';
    exit;
}

// Handle migration
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['migrate'])) {
    $result = migrateMissingPreferences();
}

/**
 * Migrate preferences for all users who don't have them
 */
function migrateMissingPreferences()
{
    global $mysqli;

    // Get all users who don't have preferences
    $sql = "
        SELECT u.id, u.username, u.email 
        FROM users u 
        WHERE u.id NOT IN (SELECT DISTINCT user_id FROM user_preferences)
        ORDER BY u.id ASC
    ";

    $result = query($mysqli, $sql, []);

    if (!$result['success']) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $result['error'],
            'migrated' => 0
        ];
    }

    $usersWithoutPrefs = $result['data'] ?? [];
    $migrated = 0;
    $failed = 0;
    $errors = [];

    foreach ($usersWithoutPrefs as $user) {
        $userId = $user['id'];

        if (initializeUserPreferences($mysqli, $userId)) {
            $migrated++;
        } else {
            $failed++;
            $errors[] = "User #{$userId} ({$user['username']})";
        }
    }

    return [
        'success' => true,
        'migrated' => $migrated,
        'failed' => $failed,
        'errors' => $errors,
        'message' => "Migration complete! Added preferences for {$migrated} users."
    ];
}

// Get current stats
$statsQuery = "
    SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(DISTINCT user_id) FROM user_preferences) as users_with_prefs
";
$statsResult = query($mysqli, $statsQuery, []);
$stats = $statsResult['success'] && !empty($statsResult['data']) ? $statsResult['data'][0] : null;

$totalUsers = $stats ? (int) $stats['total_users'] : 0;
$usersWithPrefs = $stats ? (int) $stats['users_with_prefs'] : 0;
$usersWithoutPrefs = $totalUsers - $usersWithPrefs;

?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Preferences Migration</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .migration-container {
                background: white;
                border-radius: 10px;
                padding: 40px;
                max-width: 600px;
                width: 100%;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            }

            .stats-box {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 15px;
                margin-bottom: 30px;
            }

            .stat-item {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                text-align: center;
                border-left: 4px solid #667eea;
            }

            .stat-number {
                font-size: 28px;
                font-weight: bold;
                color: #667eea;
            }

            .stat-label {
                font-size: 12px;
                color: #6c757d;
                text-transform: uppercase;
                margin-top: 5px;
            }

            .alert-info {
                background-color: #e7f3ff;
                border-left: 4px solid #2196F3;
            }

            .btn-migrate {
                width: 100%;
                padding: 12px;
                font-size: 16px;
                font-weight: 600;
            }

            .success-box {
                margin-top: 30px;
                padding: 20px;
                background: #d4edda;
                border: 1px solid #c3e6cb;
                border-radius: 5px;
            }

            .success-box h4 {
                color: #155724;
                margin-bottom: 10px;
            }

            .error-list {
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                border-radius: 5px;
                padding: 15px;
                margin-top: 15px;
            }

            .delete-note {
                margin-top: 30px;
                padding: 15px;
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                border-radius: 5px;
            }

            .delete-note strong {
                color: #856404;
            }
        </style>
    </head>

    <body>
        <div class="migration-container">
            <h1 class="mb-3">
                <i class="bi bi-database"></i> User Preferences Migration
            </h1>
            <p class="text-muted mb-4">Add default preferences for existing users</p>

            <!-- Statistics -->
            <div class="stats-box">
                <div class="stat-item">
                    <div class="stat-number">
                        <?= htmlspecialchars($totalUsers) ?>
                    </div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-item" style="border-left-color: #28a745;">
                    <div class="stat-number" style="color: #28a745;">
                        <?= htmlspecialchars($usersWithPrefs) ?>
                    </div>
                    <div class="stat-label">With Preferences</div>
                </div>
                <div class="stat-item" style="border-left-color: #dc3545;">
                    <div class="stat-number" style="color: #dc3545;">
                        <?= htmlspecialchars($usersWithoutPrefs) ?>
                    </div>
                    <div class="stat-label">Without Preferences</div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info" role="alert">
                <strong>ℹ️ What this does:</strong>
                <p class="mb-0">This script will add default user preferences for all
                    <?= htmlspecialchars($usersWithoutPrefs) ?> users who don't yet have them initialized.
                </p>
            </div>

            <!-- Migration Form -->
            <?php if ($usersWithoutPrefs > 0): ?>
                <form method="POST" class="mt-4">
                    <button type="submit" name="migrate" class="btn btn-primary btn-lg btn-migrate"
                        onclick="return confirm('Are you sure? This will add preferences for <?= htmlspecialchars($usersWithoutPrefs) ?> users.')">
                        <i class="bi bi-arrow-repeat"></i> Migrate Preferences
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-success mt-4">
                    ✅ All users already have preferences initialized!
                </div>
            <?php endif; ?>

            <!-- Results -->
            <?php if ($result): ?>
                <?php if ($result['success']): ?>
                    <div class="success-box">
                        <h4>✅ Migration Successful!</h4>
                        <p><strong>Migrated:</strong>
                            <?= htmlspecialchars($result['migrated']) ?> users
                        </p>
                        <?php if ($result['failed'] > 0): ?>
                            <p><strong>Failed:</strong>
                                <?= htmlspecialchars($result['failed']) ?> users
                            </p>
                            <div class="error-list">
                                <strong>Failed Users:</strong>
                                <ul class="mb-0" style="margin-left: 20px;">
                                    <?php foreach ($result['errors'] as $error): ?>
                                        <li>
                                            <?= htmlspecialchars($error) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger mt-4">
                        <h4>❌ Migration Failed</h4>
                        <p>
                            <?= htmlspecialchars($result['message']) ?>
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Delete Instructions -->
            <div class="delete-note">
                <strong>⚠️ Security Note:</strong>
                <p class="mb-0">After migration is complete, <strong>delete this file</strong> from your server for
                    security reasons.</p>
                <small class="text-muted">File location: <code>/admin/migrate_preferences.php</code></small>
            </div>

            <!-- Footer -->
            <hr class="my-4">
            <p class="text-muted text-center mb-0">
                <small>This is a temporary migration script and should be deleted after use.</small>
            </p>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>

</html>