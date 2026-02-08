<?php
// subscription/expired.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Expired - StaffSync HRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }

        .expired-card {
            max-width: 600px;
            padding: 3rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .icon-box {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>

    <div class="expired-card">
        <div class="icon-box">
            ⚠️
        </div>
        <h1 class="mb-3">Your Free Trial Has Ended</h1>
        <p class="lead text-muted mb-4">
            We hope you enjoyed using StaffSync HRMS. Your trial period has expired, but don't worry—your data is safe.
        </p>
        <p class="mb-5">
            To continue accessing your dashboard and managing your employees, please upgrade to a premium plan.
        </p>

        <div class="d-grid gap-2 col-8 mx-auto">
            <button class="btn btn-primary btn-lg" onclick="alert('Payment Integration Coming Soon!')">Upgrade
                Now</button>
            <a href="../auth/logout.php" class="btn btn-outline-secondary">Logout</a>
        </div>
    </div>

</body>

</html>