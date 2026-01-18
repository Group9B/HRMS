<?php
/**
 * Interview Scheduled Email Template
 * 
 * Variables available:
 * - $first_name: Candidate's first name
 * - $job_title: Job position title
 * - $company_name: Company name
 * - $interview_date: Formatted date string
 * - $interview_mode: "Online" or "In-Person"
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
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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

        .details-box {
            background: #e8f8f5;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #a3e4d7;
        }

        .detail-row {
            display: flex;
            margin: 10px 0;
        }

        .detail-label {
            font-weight: bold;
            min-width: 100px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📅 Interview Scheduled!</h1>
        </div>
        <div class="content">
            <p>Dear <strong>
                    <?= htmlspecialchars($first_name) ?>
                </strong>,</p>

            <p>Great news! Your interview for the position of <strong>
                    <?= htmlspecialchars($job_title) ?>
                </strong> at <strong>
                    <?= htmlspecialchars($company_name) ?>
                </strong> has been scheduled.</p>

            <div class="details-box">
                <h3 style="margin-top: 0;">📋 Interview Details</h3>
                <p><strong>📅 Date & Time:</strong>
                    <?= htmlspecialchars($interview_date) ?>
                </p>
                <p><strong>📍 Mode:</strong>
                    <?= htmlspecialchars($interview_mode) ?>
                </p>
            </div>

            <p><strong>Tips for your interview:</strong></p>
            <ul>
                <li>Be on time (log in 5 minutes early for online interviews)</li>
                <li>Prepare questions about the role and company</li>
                <li>Have your resume ready for reference</li>
            </ul>

            <p>Best of luck! We look forward to meeting you.</p>

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