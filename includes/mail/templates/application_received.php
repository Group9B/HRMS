<?php
/**
 * Application Received Email Template
 * 
 * Variables available:
 * - $first_name: Candidate's first name
 * - $job_title: Job position title
 * - $company_name: Company name
 */
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            background: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
        }

        .footer {
            background: #333;
            color: #999;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            border-radius: 0 0 8px 8px;
        }

        .highlight {
            background: #e8f4fd;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📩 Application Received!</h1>
        </div>
        <div class="content">
            <p>Dear <strong>
                    <?= htmlspecialchars($first_name) ?>
                </strong>,</p>

            <p>Thank you for applying for the position of <strong>
                    <?= htmlspecialchars($job_title) ?>
                </strong> at <strong>
                    <?= htmlspecialchars($company_name) ?>
                </strong>.</p>

            <div class="highlight">
                <p><strong>What happens next?</strong></p>
                <p>Our recruitment team will review your application and get back to you soon. You can check your
                    application status anytime by visiting our portal.</p>
            </div>

            <p>We appreciate your interest in joining our team!</p>

            <p>Best regards,<br>
                <strong>HR Team</strong><br>
                <?= htmlspecialchars($company_name) ?>
            </p>
        </div>
        <div class="footer">
            <p>This is an automated email. Please do not reply directly to this message.</p>
        </div>
    </div>
</body>

</html>