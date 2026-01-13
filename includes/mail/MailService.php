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
            error_log("Email could not be sent to {$toEmail}. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
}
