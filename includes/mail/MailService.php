<?php
// includes/mail/MailService.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../../includes/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../includes/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../includes/phpmailer/src/SMTP.php';

class MailService
{
    private $mailer;
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/mail.php';
        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
    }

    private function setupMailer()
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];

            // Default From address
            $this->mailer->setFrom($this->config['from_address'], $this->config['from_name']);
        } catch (Exception $e) {
            // Log error or handle it
            error_log("Mailer Setup Error: " . $e->getMessage());
        }
    }

    public function sendReceipt($toEmail, $toName, $receiptData)
    {
        try {
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Payment Receipt - ' . ($receiptData['receipt_number'] ?? 'N/A');

            ob_start();
            extract($receiptData);
            include __DIR__ . '/templates/receipt.php';
            $body = ob_get_clean();

            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    /**
     * Send a payslip email with optional PDF attachment
     * 
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $htmlBody HTML content of the email
     * @param string|null $pdfPath Optional path to PDF file to attach
     * @param string|null $pdfFilename Optional custom filename for the attachment
     * @return bool True on success, false on failure
     */
    public function sendPayslip($toEmail, $toName, $subject, $htmlBody, $pdfPath = null, $pdfFilename = null)
    {
        try {
            // Clear any existing recipients from previous sends
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = strip_tags($htmlBody);

            // Attach PDF if provided
            if ($pdfPath !== null && file_exists($pdfPath)) {
                $filename = $pdfFilename ?? basename($pdfPath);
                $this->mailer->addAttachment($pdfPath, $filename, 'base64', 'application/pdf');
            }

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Payslip email could not be sent to {$toEmail}. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    /**
     * Add an attachment to the email
     * 
     * @param string $filePath Path to the file
     * @param string|null $filename Optional custom filename
     * @param string $mimeType MIME type of the file
     * @return bool True on success, false on failure
     */
    public function addAttachment($filePath, $filename = null, $mimeType = 'application/octet-stream')
    {
        try {
            if (!file_exists($filePath)) {
                error_log("Attachment file not found: {$filePath}");
                return false;
            }
            $name = $filename ?? basename($filePath);
            $this->mailer->addAttachment($filePath, $name, 'base64', $mimeType);
            return true;
        } catch (Exception $e) {
            error_log("Failed to add attachment: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Generic email sending method
     * 
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $htmlBody HTML content
     * @param string|null $altBody Plain text alternative (optional)
     * @return bool True on success, false on failure
     */
    public function send($toEmail, $toName, $subject, $htmlBody, $altBody = null)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $altBody ?? strip_tags($htmlBody);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Payslip email could not be sent to {$toEmail}. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    /**
     * Send password reset email
     * 
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $resetLink The password reset link
     * @return bool True on success, false on failure
     */
    public function sendPasswordReset($toEmail, $toName, $resetLink)
    {
        $subject = "Password Reset Request - " . ($this->config['from_name'] ?? 'HRMS');

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;'>
            <h2 style='color: #333;'>Password Reset Request</h2>
            <p>Hello " . htmlspecialchars($toName) . ",</p>
            <p>We received a request to reset your password for your account.</p>
            <div style='margin: 30px 0; text-align: center;'>
                <a href='" . htmlspecialchars($resetLink) . "' style='background-color: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;'>Reset Password</a>
            </div>
            <p>If the button doesn't work, you can copy and paste the following link into your browser:</p>
            <p style='background-color: #f8f9fa; padding: 10px; word-break: break-all; font-size: 14px;'>" . htmlspecialchars($resetLink) . "</p>
            <p>For security, this link will expire in 1 hour.</p>
            <p>If you did not request a password reset, you can safely ignore this email.</p>
            <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;'>
            <p style='color: #6c757d; font-size: 12px;'>This is an automated message. Please do not reply.</p>
        </div>";

        $altBody = "Hello $toName,\n\nWe received a request to reset your password. Please visit the following link to reset it:\n$resetLink\n\nIf you did not request this, please ignore this email.";

        return $this->send($toEmail, $toName, $subject, $body, $altBody);
    }

    // =========================================================================
    //  EMAIL QUEUE SYSTEM
    // =========================================================================

    /**
     * Queue an email for asynchronous sending.
     * Inserts the email into the `email_queue` database table instead of
     * sending it immediately. A background worker script processes the queue.
     * 
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $htmlBody HTML body
     * @param string|null $altBody Plain text alternative
     * @return bool True if queued successfully
     */
    public function queue($toEmail, $toName, $subject, $htmlBody, $altBody = null)
    {
        global $mysqli;

        // Safety: ensure db connection exists
        if (!isset($mysqli) || !$mysqli) {
            require_once __DIR__ . '/../../config/db.php';
        }

        $alt = $altBody ?? strip_tags($htmlBody);

        $stmt = $mysqli->prepare(
            "INSERT INTO email_queue (to_email, to_name, subject, body, alt_body, status) 
             VALUES (?, ?, ?, ?, ?, 'pending')"
        );

        if (!$stmt) {
            error_log("[MailQueue] Failed to prepare INSERT statement: " . $mysqli->error);
            return false;
        }

        $stmt->bind_param("sssss", $toEmail, $toName, $subject, $htmlBody, $alt);
        $result = $stmt->execute();

        if (!$result) {
            error_log("[MailQueue] Failed to queue email to {$toEmail}: " . $stmt->error);
        }

        $stmt->close();
        return $result;
    }

    /**
     * Queue a password reset email (async version).
     * Same as sendPasswordReset() but non-blocking.
     * 
     * @param string $toEmail
     * @param string $toName
     * @param string $resetLink
     * @return bool
     */
    public function queuePasswordReset($toEmail, $toName, $resetLink)
    {
        $subject = "Password Reset Request - " . ($this->config['from_name'] ?? 'HRMS');

        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;'>
            <h2 style='color: #333;'>Password Reset Request</h2>
            <p>Hello " . htmlspecialchars($toName) . ",</p>
            <p>We received a request to reset your password for your account.</p>
            <div style='margin: 30px 0; text-align: center;'>
                <a href='" . htmlspecialchars($resetLink) . "' style='background-color: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;'>Reset Password</a>
            </div>
            <p>If the button doesn't work, you can copy and paste the following link into your browser:</p>
            <p style='background-color: #f8f9fa; padding: 10px; word-break: break-all; font-size: 14px;'>" . htmlspecialchars($resetLink) . "</p>
            <p>For security, this link will expire in 1 hour.</p>
            <p>If you did not request a password reset, you can safely ignore this email.</p>
            <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;'>
            <p style='color: #6c757d; font-size: 12px;'>This is an automated message. Please do not reply.</p>
        </div>";

        $altBody = "Hello $toName,\n\nWe received a request to reset your password. Please visit the following link to reset it:\n$resetLink\n\nIf you did not request this, please ignore this email.";

        return $this->queue($toEmail, $toName, $subject, $body, $altBody);
    }
}
