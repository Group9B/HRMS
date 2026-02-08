<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
if (isLoggedIn()) {
    redirect('includes/redirect.php');
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>StaffSync HRMS - Modern HR Solution</title>
        <link rel="icon" href="/hrms/assets/img/SS.png" type="image/png">
        <!-- Using existing local Bootstrap -->
        <link rel="stylesheet" href="/hrms/assets/css/bootstrap.css">
        <!-- tabler for Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
        <!-- AOS Animation CSS -->
        <!-- AOS Animation CSS -->
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        <!-- Google Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
            rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', sans-serif;
            }

            .hover-shadow:hover {
                transform: translateY(-5px);
                box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
            }

            .transition-all {
                transition: all 0.3s ease;
            }

            .letter-spacing-1 {
                letter-spacing: 1px;
            }

            .letter-spacing-2 {
                letter-spacing: 2px;
            }

            /* Dark mode specific overrides if needed can go here */
            [data-bs-theme="dark"] .card {
                border-color: rgba(255, 255, 255, 0.1);
            }

            /* Brighten text in dark mode */
            [data-bs-theme="dark"] .text-secondary {
                color: rgba(255, 255, 255, 0.8) !important;
            }

            [data-bs-theme="dark"] body {
                color: rgba(255, 255, 255, 0.95) !important;
            }

            [data-bs-theme="dark"] .text-body {
                color: rgba(255, 255, 255, 0.95) !important;
            }
        </style>

    <body class="bg-body-tertiary">

        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg bg-body sticky-top shadow-sm py-3">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center fw-bold text-primary" href="#">
                    <img src="/hrms/assets/img/SS.png" alt="Logo" width="30" height="30"
                        class="d-inline-block align-text-top me-2">
                    StaffSync
                </a>

                <!-- Custom Toggler Trigger -->
                <button class="navbar-toggler border-0 shadow-none z-3" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                    <i class="ti ti-menu-2 ti-lg" id="navbarTogglerIcon"></i>
                </button>

                <!-- Offcanvas Menu (Right Side) -->
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                    aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header justify-content-between">
                        <h5 class="offcanvas-title fw-bold text-primary" id="offcanvasNavbarLabel">StaffSync</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-end flex-grow-1 align-items-center gap-3">
                            <li class="nav-item">
                                <a class="nav-link text-body fw-semibold" href="#features">Features</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-body fw-semibold" href="/hrms/benefits.php">Benefits</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-body fw-semibold"
                                    href="/hrms/subscription/purchase.php">Pricing</a>
                            </li>
                            <li class="nav-item ms-lg-2 w-20 w-lg-auto">
                                <a href="/hrms/auth/login.php"
                                    class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-semibold w-100 w-lg-auto">Log
                                    In</a>
                            </li>
                            <li class="nav-item w-20 w-lg-auto">
                                <a href="/hrms/register.php"
                                    class="btn btn-primary btn-sm px-3 rounded-pill fw-semibold shadow-sm w-100 w-lg-auto">Sign
                                    Up
                                    Free</a>
                            </li>
                        </ul>
                        <button class="btn btn-link text-primary p-0 ms-3 me-auto text-decoration-none" id="themeToggle"
                            aria-label="Toggle theme">
                            <i class="ti ti-moon" style="font-size: 1.2rem;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="py-5 bg-body border-bottom">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 text-center text-lg-start">
                        <div class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill fw-semibold"
                            data-aos="fade-up">
                            <i class="ti ti-star me-2"></i> #1 HR Management Software
                        </div>
                        <h1 class="display-4 fw-bold text-body-emphasis mb-4 lh-tight" data-aos="fade-up"
                            data-aos-delay="100">
                            The Smartest Way to Manage Your <span class="text-primary">Workforce</span>
                        </h1>
                        <p class="lead text-secondary mb-5 pe-lg-5" data-aos="fade-up" data-aos-delay="200">
                            Simplify attendance, payroll, and performance management with StaffSync.
                            Designed for modern businesses to automate HR processes and focus on people.
                        </p>
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start"
                            data-aos="fade-up" data-aos-delay="300">
                            <a href="/hrms/register.php"
                                class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm fw-semibold">
                                Get Started
                                <i class="ti ti-arrow-right ms-2"></i>
                            </a>
                            <a href="#features" class="btn btn-outline-secondary btn-lg px-5 rounded-pill fw-semibold">
                                Learn More
                            </a>
                        </div>
                        <div class="mt-4 text-secondary small">
                            <i class="ti ti-circle-check text-success me-1"></i> No credit card required &nbsp;&nbsp;
                            <i class="ti ti-circle-check text-success me-1"></i> 14-day free trial
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="position-relative p-4 bg-body-tertiary rounded-4 border" data-aos="fade-left"
                            data-aos-duration="1000">
                            <div class="bg-body rounded-3  shadow-sm p-4 mb-4 border-start border-4 border-primary">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary bg-opacity-10 badge py-2 rounded-circle text-primary me-3">
                                        <i class="ti ti-users fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold text-body">Total Employees</h5>
                                        <small class="text-secondary">Active workforce status</small>
                                    </div>
                                    <h3 class="ms-auto mb-0 fw-bold text-primary">1,245</h3>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <div
                                        class="bg-body rounded-3 shadow-sm p-3 border-start border-4 border-success h-100">
                                        <small class="text-secondary d-block mb-1">On Time</small>
                                        <h4 class="fw-bold text-body mb-0">95%</h4>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div
                                        class="bg-body rounded-3 shadow-sm p-3 border-start border-4 border-warning h-100">
                                        <small class="text-secondary d-block mb-1">On Leave</small>
                                        <h4 class="fw-bold text-body mb-0">12</h4>
                                    </div>
                                </div>
                            </div>

                            <!-- Decorative elements -->
                            <div
                                class="position-absolute top-0 end-0 translate-middle-y me-4 mt-2 bg-success text-white px-3 py-1 rounded-pill shadow-sm small fw-bold">
                                <i class="ti ti-chart-line me-1"></i> +24% Productivity
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-5 bg-body-tertiary" id="features">
            <div class="container py-5">
                <div class="text-center mb-5 mw-100 mx-auto" style="max-width: 700px;">
                    <h6 class="text-primary fw-bold text-uppercase letter-spacing-2">Key Features</h6>
                    <h2 class="fw-bold text-body-emphasis mb-3">Everything You Need to Manage Your Team</h2>
                    <p class="text-secondary lead">One platform to handle all your HR needs, from onboarding to
                        retirement.
                    </p>
                </div>

                <div class="row g-4">
                    <!-- Feature 1 -->
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="0">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 bg-body hover-shadow transition-all">
                            <div class="card-body">
                                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 p-3 mb-4"
                                    style="width: 60px; height: 60px;">
                                    <i class="ti ti-calendar-check fs-3"></i>
                                </div>
                                <h4 class="fw-bold mb-3 text-body">Smart Attendance</h4>
                                <p class="text-secondary mb-0">
                                    Track employee attendance in real-time with geofencing and biometric integration
                                    options.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 hover-shadow transition-all bg-body">
                            <div class="card-body">
                                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-3 p-3 mb-4"
                                    style="width: 60px; height: 60px;">
                                    <i class="ti ti-currency-dollar fs-3"></i>
                                </div>
                                <h4 class="fw-bold mb-3 text-body">Payroll Automation</h4>
                                <p class="text-secondary mb-0">
                                    Automate salary calculations, deductions, and tax compliance with just a few clicks.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 hover-shadow transition-all bg-body">
                            <div class="card-body">
                                <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-3 p-3 mb-4"
                                    style="width: 60px; height: 60px;">
                                    <i class="ti ti-chart-pie fs-3"></i>
                                </div>
                                <h4 class="fw-bold mb-3 text-body">Performance Analytics</h4>
                                <p class="text-secondary mb-0">
                                    Gain insights into employee performance and productivity with detailed reports.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 hover-shadow transition-all bg-body">
                            <div class="card-body">
                                <div class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-3 p-3 mb-4"
                                    style="width: 60px; height: 60px;">
                                    <i class="ti ti-user-plus fs-3"></i>
                                </div>
                                <h4 class="fw-bold mb-3 text-body">Easy Onboarding</h4>
                                <p class="text-secondary mb-0">
                                    Seamlessly onboard new hires with digital document signing and automated workflows.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 5 -->
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 hover-shadow transition-all bg-body">
                            <div class="card-body">
                                <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-3 p-3 mb-4"
                                    style="width: 60px; height: 60px;">
                                    <i class="ti ti-checklist fs-3"></i>
                                </div>
                                <h4 class="fw-bold mb-3 text-body">Task Management</h4>
                                <p class="text-secondary mb-0">
                                    Assign tasks, track progress, and collaborate with your team efficiently.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 6 -->
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 hover-shadow transition-all bg-body">
                            <div class="card-body">
                                <div class="d-inline-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary rounded-3 p-3 mb-4"
                                    style="width: 60px; height: 60px;">
                                    <i class="ti ti-shield-check fs-3"></i>
                                </div>
                                <h4 class="fw-bold mb-3 text-body">Secure Data</h4>
                                <p class="text-secondary mb-0">
                                    Enterprise-grade security to keep your employee data safe and compliant.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trusted & Ratings Section -->
        <section class="py-4 bg-body-tertiary border-bottom">
            <div class="container text-center">
                <p class="text-uppercase text-secondary fw-bold small letter-spacing-2 mb-3">Trusted by leading
                    companies
                </p>
                <div class="row align-items-center justify-content-center g-4 grayscale opacity-75">
                    <div class="col-6 col-md-3 col-lg-2">
                        <span class="h4 fw-bold text-body"><i class="ti ti-star text-warning "></i> G2</span>
                        <div class="small">High Performer 2024</div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <span class="h4 fw-bold text-body"><i class="ti ti-trophy text-warning "></i>
                            Capterra</span>
                        <div class="small">Best Ease of Use</div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <span class="h4 fw-bold text-body"><i class="ti ti-brand-google-filled text-primary "></i>
                            Google</span>
                        <div class="small">4.8/5 Rating</div>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <span class="h4 fw-bold text-body"><i class="ti ti-shield-check text-success "></i>
                            ISO</span>
                        <div class="small">27001 Certified</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Detailed Feature: Core HR -->
        <section class="py-5 bg-body">
            <div class="container py-5">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6" data-aos="fade-right">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 p-3 mb-4"
                            style="width: 60px; height: 60px;">
                            <i class="ti ti-users-group fs-3"></i>
                        </div>
                        <h2 class="display-6 fw-bold text-body-emphasis mb-4">Simplify Core HR Operations</h2>
                        <p class="lead text-secondary mb-4">
                            Stop juggling spreadsheets. Centralize your employee data in one secure location.
                            Manage documentation, assets, and policies effortlessly.
                        </p>
                        <ul class="list-unstyled text-secondary d-flex flex-column gap-3 mb-4">
                            <li class="d-flex align-items-start">
                                <i class="ti ti-circle-check text-success mt-1 me-3"></i>
                                <div>
                                    <strong class="d-block text-body">Employee Database</strong>
                                    A 360-degree view of every employee's profile and history.
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <i class="ti ti-circle-check text-success mt-1 me-3"></i>
                                <div>
                                    <strong class="d-block text-body">Document Management</strong>
                                    Securely store and share digitally signed documents.
                                </div>
                            </li>
                        </ul>
                        <a href="#" class="btn btn-outline-primary rounded-pill fw-semibold px-4">Explore Core HR</a>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left">
                        <div class="p-4 bg-body-tertiary rounded-4 border">
                            <div class="bg-body rounded-3 shadow-sm p-4 text-center">
                                <div class="display-1 text-primary mb-3"><i class="far fa-address-card"></i></div>
                                <h4 class="mb-2 text-body">Employee Directory</h4>
                                <p class="text-secondary small">Access team details anytime, anywhere.</p>
                                <div class="d-flex justify-content-center gap-2 mt-3">
                                    <div class="bg-body-secondary rounded p-2" style="width: 40px; height: 40px;"></div>
                                    <div class="bg-body-secondary rounded p-2" style="width: 140px; height: 40px;">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center gap-2 mt-2">
                                    <div class="bg-body-secondary rounded p-2" style="width: 40px; height: 40px;"></div>
                                    <div class="bg-body-secondary rounded p-2" style="width: 140px; height: 40px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 bg-body-tertiary">
            <div class="container py-5">
                <div class="row align-items-center g-5 flex-lg-row-reverse">
                    <div class="col-lg-6" data-aos="fade-left">
                        <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-3 p-3 mb-4"
                            style="width: 60px; height: 60px;">
                            <i class="ti ti-chart-line fs-3"></i>
                        </div>
                        <h2 class="display-6 fw-bold text-body-emphasis mb-4">Drive High Performance</h2>
                        <p class="lead text-secondary mb-4">
                            Align individual goals with organizational objectives. Run continuous feedback
                            cycles and 360-degree appraisals without the paperwork.
                        </p>
                        <ul class="list-unstyled text-secondary d-flex flex-column gap-3 mb-4">
                            <li class="d-flex align-items-start">
                                <i class="ti ti-circle-check text-success mt-1 me-3"></i>
                                <div>
                                    <strong class="d-block text-body">KRA & KPI Tracking</strong>
                                    Set clear targets and monitor progress in real-time.
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <i class="ti ti-circle-check text-success mt-1 me-3"></i>
                                <div>
                                    <strong class="d-block text-body">Skill Matrix</strong>
                                    Identify skill gaps and plan training programs effectively.
                                </div>
                            </li>
                        </ul>
                        <a href="#" class="btn btn-outline-primary rounded-pill fw-semibold px-4">See Performance
                            Tools</a>
                    </div>
                    <div class="col-lg-6" data-aos="fade-right">
                        <div class="p-4 bg-body rounded-4 border shadow-sm position-relative">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="p-3 bg-body-tertiary rounded-3 text-center h-100 border">
                                        <h2 class="text-primary fw-bold mb-0">98%</h2>
                                        <small class="text-secondary">Goals Met</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-body-tertiary rounded-3 text-center h-100 border">
                                        <div class="text-warning h2 mb-0"><i class="ti ti-star"></i> 4.8</div>
                                        <small class="text-secondary">Avg Rating</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success">
                                        <h6 class="text-success mb-1 fw-bold">Performance Review</h6>
                                        <p class="small text-secondary mb-0">Completed on time for 45 employees.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Detailed Feature: Analytics (New Section) -->
        <section class="py-5 bg-body">
            <div class="container py-5">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6" data-aos="fade-right">
                        <div class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-3 p-3 mb-4"
                            style="width: 60px; height: 60px;">
                            <i class="ti ti-chart-pie fs-3"></i>
                        </div>
                        <h2 class="display-6 fw-bold text-body-emphasis mb-4">Powerful HR Analytics</h2>
                        <p class="lead text-secondary mb-4">
                            Make data-driven decisions with real-time insights. diverse reports to understand attrition,
                            attendance patterns, and workforce costs.
                        </p>
                        <ul class="list-unstyled text-secondary d-flex flex-column gap-3 mb-4">
                            <li class="d-flex align-items-start">
                                <i class="ti ti-circle-check text-success mt-1 me-3"></i>
                                <div>
                                    <strong class="d-block text-body">Visual Dashboards</strong>
                                    Instantly see the big picture with intuitive charts and graphs.
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <i class="ti ti-circle-check text-success mt-1 me-3"></i>
                                <div>
                                    <strong class="d-block text-body">Custom Reports</strong>
                                    Build reports that matter most to your organization.
                                </div>
                            </li>
                        </ul>
                        <a href="#" class="btn btn-outline-primary rounded-pill fw-semibold px-4">See Analytics
                            Features</a>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left">
                        <!-- Placeholder Image for Analytics -->
                        <div class="position-relative p-2 bg-body-secondary rounded-4 border">
                            <img src="/hrms/assets/img/hrms_analytics_dashboard.png"
                                alt="HR Analytics Dashboard Screenshot" class="img-fluid rounded-3 shadow-sm w-100">
                            <!-- Caption for User to Replace Image -->
                            <div
                                class="position-absolute bottom-0 start-50 translate-middle-x mb-3 bg-dark bg-opacity-75 text-white px-3 py-1 rounded-pill small fst-italic">
                                * Placeholder: Replace with your system screenshot
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trusted By / Stats Section -->
        <section class="py-5 bg-body border-top border-bottom">
            <div class="container py-4">
                <div class="row text-center g-4">
                    <div class="col-md-4">
                        <div class="p-3">
                            <h2 class="display-5 fw-bold text-primary mb-2">10k+</h2>
                            <h6 class="text-uppercase text-secondary letter-spacing-1">Active Users</h6>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border-start border-end">
                            <h2 class="display-5 fw-bold text-primary mb-2">99.9%</h2>
                            <h6 class="text-uppercase text-secondary letter-spacing-1">Uptime Guaranteed</h6>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3">
                            <h2 class="display-5 fw-bold text-primary mb-2">500+</h2>
                            <h6 class="text-uppercase text-secondary letter-spacing-1">Companies Trust Us</h6>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Integrations Section -->
        <section class="py-5 bg-body-tertiary">
            <div class="container py-5 text-center">
                <h6 class="text-primary fw-bold text-uppercase letter-spacing-2 mb-3">Integrations</h6>
                <h2 class="fw-bold text-body-emphasis mb-5">Works With Your Favorite Tools</h2>

                <div class="row justify-content-center g-4">
                    <div class="col-6 col-md-4 col-lg-2">
                        <div
                            class="bg-body p-2 rounded-4 shadow-sm border d-flex justify-content-center align-items-center h-100">
                            <i class="ti ti-brand-open-source fs-1 text-primary"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div
                            class="bg-body p-2 rounded-4 shadow-sm border d-flex justify-content-center align-items-center h-100">
                            <i class="ti ti-brand-google-filled fs-1 text-danger"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div
                            class="bg-body p-2 rounded-4 shadow-sm border d-flex justify-content-center align-items-center h-100">
                            <i class="ti ti-brand-speedtest fs-1 text-primary"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div
                            class="bg-body p-2 rounded-4 shadow-sm border d-flex justify-content-center align-items-center h-100">
                            <i class="ti ti-brand-deezer fs-1 text-primary"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div
                            class="bg-body p-2 rounded-4 shadow-sm border d-flex justify-content-center align-items-center h-100">
                            <i class="ti ti-brand-chrome fs-1 text-secondary"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <a href="#" class="btn btn-link text-decoration-none fw-semibold">View all integrations <i
                            class="ti ti-arrow-right ms-1"></i></a>
                </div>
            </div>
        </section>

        <!-- Detailed Feature: Payroll & Expenses (New Dark Section) -->
        <section class="py-5 bg-body text-body">
            <div class="container py-5">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6" data-aos="fade-right">
                        <h2 class="display-5 fw-bold mb-4">Integrated payroll and expenses</h2>
                        <p class="lead text-secondary mb-5">
                            Effortlessly navigate complex payroll processing, manage compensation,
                            and organize expenses and reimbursements through quick and easy
                            integrations with StaffSync Payroll and Expenses.
                        </p>
                        <div class="d-flex flex-wrap gap-3 mb-5">
                            <a href="#" class="btn btn-outline-primary rounded-pill fw-semibold px-4 py-2">Explore
                                Payroll
                                <i class="ti ti-arrow-right ms-2 small"></i></a>
                            <a href="#" class="btn btn-outline-secondary rounded-pill fw-semibold px-4 py-2">Explore
                                Travel
                                and
                                expense <i class="ti ti-arrow-right ms-2 small"></i></a>
                        </div>
                        <div class="row g-4 text-secondary small">
                            <div class="col-sm-6 d-flex align-items-center">
                                <i class="ti ti-circle-check text-success me-2"></i> Payroll processing
                            </div>
                            <div class="col-sm-6 d-flex align-items-center">
                                <i class="ti ti-circle-check text-success me-2"></i> Statutory compliance and reporting
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left">
                        <!-- Placeholder visual for Payroll -->
                        <div class="position-relative p-2 bg-body rounded-4 border border-secondary">
                            <img src="/hrms/assets/img/hrms_payroll_dashboard_v2.png" alt="Payroll Dashboard"
                                class="img-fluid rounded-3 w-100 opacity-75">
                            <!-- Fallback/Caption if image fails or for replacement -->
                            <div class="position-absolute top-50 start-50 translate-middle text-center p-3 w-100"
                                style="pointer-events: none;">
                                <div class="mb-3"><i class="ti ti-receipt fa-3x text-primary"></i></div>
                                <!-- <h5 class="text-white">Payroll Dashboard Preview</h5> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Built for your people Section -->
        <section class="py-5 bg-body-tertiary text-body">
            <div class="container py-5">
                <h2 class="display-5 fw-bold mb-5" data-aos="fade-up">Built for your people</h2>
                <div class="row g-4">
                    <!-- Card 1 -->
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="0">
                        <div
                            class="card h-100 bg-body text-body border-0 rounded-4 p-4 shadow-sm hover-shadow transition-all">
                            <div class="card-body p-0">
                                <div class="mb-4">
                                    <i class="ti ti-mood-smile-beam fs-2 text-primary"></i>
                                </div>
                                <p class="card-text text-body mb-0">
                                    <span class="text-body fw-bold">User-friendly</span> interface that's quick to
                                    deploy
                                    and easy to set up.
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Card 2 -->
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                        <div
                            class="card h-100 bg-body text-body border-0 rounded-4 p-4 shadow-sm hover-shadow transition-all">
                            <div class="card-body p-0">
                                <div class="mb-4">
                                    <i class="ti ti-file-invoice fs-2 text-primary"></i>
                                </div>
                                <p class="card-text text-body mb-0">
                                    Expansive <span class="text-body fw-bold">resource library</span> to help you get
                                    started.
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Card 3 -->
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                        <div
                            class="card h-100 bg-body text-body border-0 rounded-4 p-4 shadow-sm hover-shadow transition-all">
                            <div class="card-body p-0">
                                <div class="mb-4">
                                    <i class="ti ti-chart-line fs-2 text-primary"></i>
                                </div>
                                <p class="card-text text-body mb-0">
                                    HR solution that can be fine-tuned to <span class="text-body fw-bold">changing
                                        business
                                        demands</span>.
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Card 4 -->
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                        <div
                            class="card h-100 bg-body text-body border-0 rounded-4 p-4 shadow-sm hover-shadow transition-all">
                            <div class="card-body p-0">
                                <div class="mb-4">
                                    <i class="ti ti-robot fs-2 text-primary"></i>
                                </div>
                                <p class="card-text text-body mb-0">
                                    Friendly <span class="text-body fw-bold">HR chatbot Nexus</span> to help you with
                                    your
                                    daily HR tasks.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-5 bg-body text-body">
            <div class="container py-5">
                <div class="row g-5">
                    <div class="col-lg-5">
                        <h2 class="display-4 fw-bold mb-4">Frequently Asked Questions</h2>
                        <p class="lead text-body">
                            Everything you need to know about StaffSync and how it can help your organization.
                        </p>
                    </div>
                    <div class="col-lg-7">
                        <div class="accordion accordion-flush" id="faqAccordion">
                            <!-- Item 1 -->
                            <div class="accordion-item bg-body border-bottom border-secondary">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-body text-body shadow-none fs-5 py-4"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        What is an HRMS?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-body pb-4">
                                        An HRMS (Human Resource Management System) is a software solution that helps
                                        organizations manage their internal HR functions. It streamlines processes like
                                        employee data management, payroll, recruitment, benefits administration,
                                        attendance,
                                        and more.
                                    </div>
                                </div>
                            </div>
                            <!-- Item 2 -->
                            <div class="accordion-item bg-body border-bottom border-secondary">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-body text-body shadow-none fs-5 py-4"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        How do I choose the best HRMS?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-body pb-4">
                                        To choose the best HRMS, assess your organization's specific needs, look for
                                        essential features like ease of use, scalability, integration capabilities (with
                                        payroll, etc.), customer support, and ensure it fits within your budget.
                                    </div>
                                </div>
                            </div>
                            <!-- Item 3 -->
                            <div class="accordion-item bg-body border-bottom border-secondary">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-body text-body shadow-none fs-5 py-4"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        What are the key features of an HRMS?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-body pb-4">
                                        Key features generally include Employee Information Management, Attendance &
                                        Leave
                                        Tracking, Payroll Management, Performance Appraisals, Recruitment & Onboarding,
                                        Self-Service Portals, and Analytics.
                                    </div>
                                </div>
                            </div>
                            <!-- Item 4 -->
                            <div class="accordion-item bg-body border-bottom border-secondary">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-body text-body shadow-none fs-5 py-4"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        What is StaffSync?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-body pb-4">
                                        StaffSync is a comprehensive cloud-based HRMS designed to simplify your HR
                                        operations. From hiring to retiring, we provide all the tools you need to manage
                                        your diverse workforce effectively.
                                    </div>
                                </div>
                            </div>
                            <!-- Item 5 -->
                            <div class="accordion-item bg-body border-bottom border-secondary">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-body text-body shadow-none fs-5 py-4"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                        How do I get started with StaffSync?
                                    </button>
                                </h2>
                                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-body pb-4">
                                        Getting started is easy! Simply sign up for our 14-day free trial. Our
                                        onboarding
                                        team will guide you through the setup process, importing your data, and
                                        configuring
                                        the system to match your policies.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-5 bg-primary bg-gradient text-white">
            <div class="container py-5 text-center">
                <h2 class="display-5 fw-bold mb-4">Ready to Transform Your HR?</h2>
                <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 600px;">
                    Join thousands of companies using StaffSync to build better workplaces.
                    Start your free trial today.
                </p>
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="/hrms/register.php"
                        class="btn btn-light btn-lg px-4 px-md-5 rounded-pill fw-bold text-primary shadow-sm">Get
                        Started Now</a>
                    <a href="#" class="btn btn-outline-light btn-lg px-4 px-md-5 rounded-pill fw-semibold">Contact
                        Sales</a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-dark text-white pt-5 pb-3">
            <div class="container pt-4">
                <div class="row g-5">
                    <!-- Brand and Social Column -->
                    <div class="col-lg-4 mb-4">
                        <a class="d-flex align-items-center mb-4 text-decoration-none" href="#">
                            <img src="/hrms/assets/img/SS.png" alt="Logo" width="30" height="30" class="me-2">
                            <span class="h5 fw-bold text-white mb-0">StaffSync</span>
                        </a>
                        <p class="text-white-50 mb-4">
                            A comprehensive HR management solution designed to streamline your business operations and
                            empower your workforce.
                        </p>
                        <div class="d-flex gap-3">
                            <a href="#" class="btn btn-icon btn-sm btn-outline-light rounded-circle fs-5"><i
                                    class="ti ti-brand-facebook"></i></a>
                            <a href="#" class="btn btn-icon btn-sm btn-outline-light rounded-circle fs-5"><i
                                    class="ti ti-brand-twitter"></i></a>
                            <a href="#" class="btn btn-icon btn-sm btn-outline-light rounded-circle fs-5"><i
                                    class="ti ti-brand-linkedin"></i></a>
                            <a href="#" class="btn btn-icon btn-sm btn-outline-light rounded-circle fs-5"><i
                                    class="ti ti-brand-instagram"></i></a>
                            <a href="#" class="btn btn-icon btn-sm btn-outline-light rounded-circle fs-5"><i
                                    class="ti ti-brand-youtube"></i></a>
                        </div>
                    </div>

                    <!-- Product Column -->
                    <div class="col-6 col-md-3 col-lg-2">
                        <h6 class="fw-bold text-white mb-3">Product</h6>
                        <ul class="list-unstyled d-flex flex-column gap-2 small">
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">People</a>
                            </li>
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Recruit</a>
                            </li>
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Payroll</a>
                            </li>
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Expense</a>
                            </li>
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Connect</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Resources Column -->
                    <div class="col-6 col-md-3 col-lg-2">
                        <h6 class="fw-bold text-white mb-3">Resources</h6>
                        <ul class="list-unstyled d-flex flex-column gap-2 small">
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Blog</a>
                            </li>
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Webinars</a>
                            </li>
                            <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Case
                                    Studies</a></li>
                            <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Help
                                    Center</a></li>
                            <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">API
                                    Docs</a></li>
                        </ul>
                    </div>

                    <!-- Solutions Column -->
                    <div class="col-6 col-md-3 col-lg-2">
                        <h6 class="fw-bold text-white mb-3">Solutions</h6>
                        <ul class="list-unstyled d-flex flex-column gap-2 small">
                            <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Remote
                                    Work</a></li>
                            <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Return
                                    to
                                    Office</a></li>
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Diversity
                                    & Inclusion</a></li>
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Performance</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Company Column -->
                    <div class="col-6 col-md-3 col-lg-2">
                        <h6 class="fw-bold text-white mb-3">Company</h6>
                        <ul class="list-unstyled d-flex flex-column gap-2 small">
                            <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">About
                                    Us</a></li>
                            <li><a href="/hrms/careers.php"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Careers</a>
                            </li>
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Newsroom</a>
                            </li>
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Legal</a>
                            </li>
                            <li><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Contact</a>
                            </li>
                        </ul>
                    </div>
                </div>


                <div class="row pt-4 border-top border-secondary border-opacity-25 align-items-center">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        <p class="mb-0 small text-white-50">&copy; 2024 StaffSync. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <ul class="list-inline mb-0 small">
                            <li class="list-inline-item"><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Privacy
                                    Policy</a></li>
                            <li class="list-inline-item"><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Terms of
                                    Service</a></li>
                            <li class="list-inline-item"><a href="#"
                                    class="text-decoration-none text-white-50 footer-link transition-all">Cookie
                                    Settings</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- AOS Animation Library -->
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <script>
            AOS.init({
                duration: 800,
                once: true,
                offset: 100
            });

            // Add "Coming Soon" alert to all footer links
            document.querySelectorAll('footer a[href="#"]').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    alert('This feature will be available soon!');
                });
            });

            // Toggle Hamburger/Close Icon
            const offcanvasElement = document.getElementById('offcanvasNavbar');
            const togglerIcon = document.getElementById('navbarTogglerIcon');

            if (offcanvasElement && togglerIcon) {
                offcanvasElement.addEventListener('show.bs.offcanvas', function () {
                    togglerIcon.classList.remove('fa-bars');
                    togglerIcon.classList.add('fa-times');
                });

                offcanvasElement.addEventListener('hide.bs.offcanvas', function () {
                    togglerIcon.classList.remove('fa-times');
                    togglerIcon.classList.add('fa-bars');
                });
            }

            // Dark Mode Logic
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = themeToggle.querySelector('i');
            const htmlElement = document.documentElement;

            // Check local storage or system preference
            const currentTheme = localStorage.getItem('theme') || 'light';
            setTheme(currentTheme);

            themeToggle.addEventListener('click', () => {
                const newTheme = htmlElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                setTheme(newTheme);
                localStorage.setItem('theme', newTheme);
            });

            function setTheme(theme) {
                htmlElement.setAttribute('data-bs-theme', theme);
                if (theme === 'dark') {
                    themeIcon.classList.remove('ti-moon');
                    themeIcon.classList.add('ti-sun');
                } else {
                    themeIcon.classList.remove('ti-sun');
                    themeIcon.classList.add('ti-moon');
                }
            }
        </script>
        <!-- Nexus Bot -->
        <?php include __DIR__ . '/nexusbot/chat_widget.php'; ?>
        <style>
            html,
            body {
                max-width: 100%;
                overflow-x: hidden;
                position: relative;
            }

            .hover-shadow:hover {
                transform: translateY(-5px);
                box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
            }

            .transition-all {
                transition: all 0.3s ease;
            }

            /* Mobile Menu Width Adjustment */
            #offcanvasNavbar {
                width: 60% !important;
            }

            .footer-link:hover {
                color: #fff !important;
                padding-left: 5px;
            }
        </style>
    </body>

</html>