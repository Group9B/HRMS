<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

$title = "Upgrade to Pro";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?> - StaffSync HRMS
    </title>
    <link rel="icon" href="/hrms/assets/img/SS.png" type="image/png">
    <link rel="stylesheet" href="/hrms/assets/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .pricing-card {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
            border-color: var(--bs-primary);
        }

        .check-list li {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .check-list i {
            color: var(--bs-success);
            margin-right: 0.5rem;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Simple Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="/hrms/">
                <img src="/hrms/assets/img/SS.png" alt="Logo" width="30" height="30" class="me-2"> StaffSync
            </a>
            <div class="ms-auto">
                <?php if (isLoggedIn()): ?>
                    <a href="/hrms/dashboard.php" class="btn btn-outline-primary btn-sm">Go to Dashboard</a>
                <?php else: ?>
                    <a href="/hrms/auth/login.php" class="btn btn-outline-primary btn-sm">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-primary">Simple, Transparent Pricing</h1>
            <p class="lead text-muted">Choose the plan that fits your business needs.</p>
        </div>

        <div class="row row-cols-1 row-cols-md-3 mb-3 text-center justify-content-center">

            <!-- Free / Demo -->
            <div class="col">
                <div class="card mb-4 rounded-3 shadow-sm pricing-card">
                    <div class="card-header py-3">
                        <h4 class="my-0 fw-normal">Trial</h4>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title pricing-card-title">$0<small class="text-muted fw-light">/mo</small></h1>
                        <ul class="list-unstyled mt-3 mb-4 text-start check-list ps-4">
                            <li><i class="ti ti-check"></i> 14 Days Full Access</li>
                            <li><i class="ti ti-check"></i> 5 Employees</li>
                            <li><i class="ti ti-check"></i> Basic Reports</li>
                            <li><i class="ti ti-check"></i> Email Support</li>
                        </ul>
                        <a href="/hrms/register.php" class="w-100 btn btn-lg btn-outline-primary">Start Free Trial</a>
                    </div>
                </div>
            </div>

            <!-- Pro Plan -->
            <div class="col">
                <div class="card mb-4 rounded-3 shadow-lg border-primary pricing-card">
                    <div class="card-header py-3 text-white bg-primary border-primary">
                        <h4 class="my-0 fw-normal">Pro</h4>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title pricing-card-title">$49<small class="text-muted fw-light">/mo</small></h1>
                        <ul class="list-unstyled mt-3 mb-4 text-start check-list ps-4">
                            <li><i class="ti ti-check"></i> Unlimited Employees</li>
                            <li><i class="ti ti-check"></i> Advanced Analytics</li>
                            <li><i class="ti ti-check"></i> Payroll Automation</li>
                            <li><i class="ti ti-check"></i> 24/7 Priority Support</li>
                            <li><i class="ti ti-check"></i> Custom Domain</li>
                        </ul>
                        <button type="button" class="w-100 btn btn-lg btn-primary"
                            onclick="alert('Payment Gateway Integration Coming Soon!')">Get Started</button>
                    </div>
                </div>
            </div>

            <!-- Enterprise -->
            <div class="col">
                <div class="card mb-4 rounded-3 shadow-sm pricing-card">
                    <div class="card-header py-3">
                        <h4 class="my-0 fw-normal">Enterprise</h4>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title pricing-card-title">Contact Us</h1>
                        <ul class="list-unstyled mt-3 mb-4 text-start check-list ps-4">
                            <li><i class="ti ti-check"></i> Dedicated Account Manager</li>
                            <li><i class="ti ti-check"></i> Custom Integrations</li>
                            <li><i class="ti ti-check"></i> On-premise Deployment</li>
                            <li><i class="ti ti-check"></i> SLA Guarantee</li>
                        </ul>
                        <button type="button" class="w-100 btn btn-lg btn-outline-primary"
                            onclick="window.location.href='mailto:sales@staffsync.com'">Contact Sales</button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['email'])): ?>
            <div class="alert alert-info text-center">
                Continuing upgrade for <strong>
                    <?= htmlspecialchars($_GET['email']) ?>
                </strong>
            </div>
        <?php endif; ?>

    </div>

    <footer class="pt-4 my-md-5 pt-md-5 border-top container">
        <div class="row">
            <div class="col-12 col-md text-center text-muted">
                <img class="mb-2" src="/hrms/assets/img/SS.png" alt="" width="24" height="24">
                <small class="d-block mb-3 text-muted">&copy; 2024 StaffSync</small>
            </div>
        </div>
    </footer>
</body>

</html>