<?php
/**
 * Welcome / Hired Email Template
 * 
 * Variables available:
 * - $first_name: New employee's first name
 * - $job_title: Job position title
 * - $company_name: Company name
 * - $email: Login email
 * - $password: Login password
 * - $login_url: URL to login page (optional)
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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

        .credentials-box {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #ffc107;
        }

        .credentials-box h3 {
            margin-top: 0;
            color: #856404;
        }

        .credential {
            background: #fff;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-family: monospace;
        }

        .warning {
            font-size: 12px;
            color: #856404;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welcome to the Team!</h1>
        </div>
        <div class="content">
            <p>Dear <strong>
                    <?= htmlspecialchars($first_name) ?>
                </strong>,</p>

            <p><strong>Congratulations!</strong> We are thrilled to welcome you to <strong>
                    <?= htmlspecialchars($company_name) ?>
                </strong> as our new <strong>
                    <?= htmlspecialchars($job_title) ?>
                </strong>.</p>

            <div class="credentials-box">
                <h3>🔐 Your Login Credentials</h3>
                <p><strong>Email:</strong></p>
                <div class="credential">
                    <?= htmlspecialchars($email) ?>
                </div>
                <p><strong>Password:</strong></p>
                <div class="credential">
                    <?= htmlspecialchars($password) ?>
                </div>
                <p class="warning">⚠️ Please change your password after your first login for security.</p>
            </div>

            <p><strong>What's next?</strong></p>
            <ul>
                <li>Log in to the HRMS portal</li>
                <li>Complete your profile</li>
                <li>Review company policies</li>
                <li>Connect with your team</li>
            </ul>

            <p>We're excited to have you on board!</p>

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