<?php
/**
 * ============================================================
 *  HRMS Email Queue Worker
 * ============================================================
 * 
 * This script processes pending emails from the `email_queue` table.
 * It is designed to be run from the command line ONLY.
 * 
 * USAGE (local development – Option A):
 *   Open a PowerShell terminal and run:
 *   while ($true) { php c:\xampp\htdocs\HRMS\cron\process_queue.php; Start-Sleep -Seconds 10 }
 * 
 * The script will:
 *   1. Fetch a batch of pending emails.
 *   2. Mark them as "processing" to prevent duplicates.
 *   3. Attempt to send each one via MailService.
 *   4. Update the status to "sent" or "failed".
 *   5. Exit cleanly after each batch.
 * 
 * All output is timestamped for easy debugging in the terminal.
 * ============================================================
 */

// ── Security: CLI-only execution ────────────────────────────
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script can only be run from the command line.');
}

// ── Bootstrap ───────────────────────────────────────────────
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/mail/MailService.php';

// Prevent timeout for long batches
set_time_limit(120);

// ── Configuration ───────────────────────────────────────────
$BATCH_SIZE = 10;    // Emails to process per run
$MAX_ATTEMPTS = 3;     // Max retries before permanently failing

// ── Helper: timestamped log ─────────────────────────────────
function qlog($message, $level = 'INFO')
{
    $timestamp = date('Y-m-d H:i:s');
    $prefix = match ($level) {
        'OK' => "\033[32m[✓ OK]\033[0m",     // Green
        'FAIL' => "\033[31m[✗ FAIL]\033[0m",   // Red
        'WARN' => "\033[33m[⚠ WARN]\033[0m",   // Yellow
        'INFO' => "\033[36m[ℹ INFO]\033[0m",   // Cyan
        default => "[{$level}]",
    };
    echo "[{$timestamp}] {$prefix} {$message}" . PHP_EOL;
}

// ── 1. Fetch pending emails ─────────────────────────────────
qlog("Worker started. Checking queue...");

$stmt = $mysqli->prepare(
    "SELECT id, to_email, to_name, subject, body, alt_body, attempts 
     FROM email_queue 
     WHERE status = 'pending' AND attempts < ? 
     ORDER BY created_at ASC 
     LIMIT ?"
);
$stmt->bind_param("ii", $MAX_ATTEMPTS, $BATCH_SIZE);
$stmt->execute();
$result = $stmt->get_result();
$emails = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($emails)) {
    qlog("Queue is empty. Nothing to do.");
    exit(0);
}

qlog("Found " . count($emails) . " email(s) to process.");

// ── 2. Process each email ───────────────────────────────────
$mailService = new MailService();
$sentCount = 0;
$failCount = 0;

foreach ($emails as $email) {
    $id = $email['id'];
    $toEmail = $email['to_email'];

    // Mark as processing (prevents another worker instance from picking it up)
    $mysqli->query("UPDATE email_queue SET status = 'processing' WHERE id = {$id}");

    qlog("Sending #{$id} to {$toEmail}...");

    // Attempt to send
    $sent = $mailService->send(
        $email['to_email'],
        $email['to_name'] ?? '',
        $email['subject'],
        $email['body'],
        $email['alt_body']
    );

    if ($sent) {
        // ── Success ──
        $updateStmt = $mysqli->prepare(
            "UPDATE email_queue SET status = 'sent', processed_at = NOW() WHERE id = ?"
        );
        $updateStmt->bind_param("i", $id);
        $updateStmt->execute();
        $updateStmt->close();

        qlog("Email #{$id} sent to {$toEmail}", 'OK');
        $sentCount++;
    } else {
        // ── Failure ──
        $newAttempts = $email['attempts'] + 1;
        $errorMsg = "SMTP send failed. Check error.log for PHPMailer details.";
        $newStatus = ($newAttempts >= $MAX_ATTEMPTS) ? 'failed' : 'pending'; // Back to pending for retry

        $updateStmt = $mysqli->prepare(
            "UPDATE email_queue SET status = ?, attempts = ?, error_message = ?, processed_at = NOW() WHERE id = ?"
        );
        $updateStmt->bind_param("sisi", $newStatus, $newAttempts, $errorMsg, $id);
        $updateStmt->execute();
        $updateStmt->close();

        if ($newStatus === 'failed') {
            qlog("Email #{$id} to {$toEmail} PERMANENTLY FAILED after {$MAX_ATTEMPTS} attempts.", 'FAIL');
        } else {
            qlog("Email #{$id} to {$toEmail} failed (attempt {$newAttempts}/{$MAX_ATTEMPTS}). Will retry.", 'WARN');
        }
        $failCount++;
    }
}

// ── 3. Summary ──────────────────────────────────────────────
qlog("Batch complete. Sent: {$sentCount}, Failed: {$failCount}.");
exit(0);
