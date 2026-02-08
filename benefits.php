<?php
$title = "Benefits - StaffSync HRMS";
$root_path = __DIR__;
require_once $root_path . '/components/layout/header.php';
?>

<style>
    /* Page-specific styles */
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

    /* Dark mode specific overrides */
    [data-bs-theme="dark"] .card {
        border-color: rgba(255, 255, 255, 0.1);
    }

    [data-bs-theme="dark"] .text-secondary {
        color: rgba(255, 255, 255, 0.8) !important;
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

<!-- Hero Section -->
<section class="py-5 bg-body border-bottom">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center text-lg-start">
                <div class="badge bg-success bg-opacity-10 text-success mb-3 px-3 py-2 rounded-pill fw-semibold"
                    data-aos="fade-up">
                    <i class="ti ti-sparkles me-2"></i> Unlocking Potential
                </div>
                <h1 class="display-4 fw-bold text-body-emphasis mb-4 lh-tight" data-aos="fade-up" data-aos-delay="100">
                    Why Choose <span class="text-primary">StaffSync?</span>
                </h1>
                <p class="lead text-secondary mb-5 pe-lg-5" data-aos="fade-up" data-aos-delay="200">
                    Transform your HR operations from administrative burdens into strategic advantages.
                    Discover the magnet that attracts and retains top talent.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start"
                    data-aos="fade-up" data-aos-delay="300">
                    <a href="/hrms/register.php" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm fw-semibold">
                        Start Free Trial
                        <i class="ti ti-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <!-- Hero Image Placeholder -->
                <div class="p-4 bg-body-tertiary rounded-4 border shadow-sm">
                    <div class="bg-body rounded-3 p-4 text-center"
                        style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                        <div class="text-muted">
                            <i class="ti ti-trophy fs-1 mb-3 text-warning"></i>
                            <h5>Empower Your Workforce</h5>
                            <p class="small">Seamless. Smart. Secure.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Key Benefits Grid -->
<section class="py-5 bg-body-tertiary">
    <div class="container py-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <h6 class="text-primary fw-bold text-uppercase letter-spacing-2 mb-3">Core Advantages</h6>
            <h2 class="display-5 fw-bold text-body-emphasis">Everything You Need to Succeed</h2>
        </div>
        <div class="row g-4">
            <!-- Benefit 1 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-shadow transition-all bg-body">
                    <div class="mb-4 text-primary">
                        <i class="ti ti-bolt fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Lightning Fast Setup</h4>
                    <p class="text-secondary">
                        Get up and running in minutes, not months. Import your data easily and start managing your
                        team immediately.
                    </p>
                </div>
            </div>
            <!-- Benefit 2 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-shadow transition-all bg-body">
                    <div class="mb-4 text-success">
                        <i class="ti ti-pig-money fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Cost Efficient</h4>
                    <p class="text-secondary">
                        Reduce operational costs by automating manual tasks. No hidden fees, just transparent
                        pricing.
                    </p>
                </div>
            </div>
            <!-- Benefit 3 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-shadow transition-all bg-body">
                    <div class="mb-4 text-info">
                        <i class="ti ti-shield-lock fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Bank-Grade Security</h4>
                    <p class="text-secondary">
                        Your data is encrypted and protected with enterprise-level security protocols. GDPR
                        compliant.
                    </p>
                </div>
            </div>
            <!-- Benefit 4 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-shadow transition-all bg-body">
                    <div class="mb-4 text-warning">
                        <i class="ti ti-device-mobile fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Mobile First</h4>
                    <p class="text-secondary">
                        Empower your team to mark attendance, apply for leave, and view payslips from anywhere, on
                        any device.
                    </p>
                </div>
            </div>
            <!-- Benefit 5 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-shadow transition-all bg-body">
                    <div class="mb-4 text-danger">
                        <i class="ti ti-heart-handshake fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Employee Wellbeing</h4>
                    <p class="text-secondary">
                        Tools to track engagement and satisfaction, ensuring your team stays happy and productive.
                    </p>
                </div>
            </div>
            <!-- Benefit 6 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="600">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 hover-shadow transition-all bg-body">
                    <div class="mb-4 text-primary">
                        <i class="ti ti-headset fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-3">24/7 Support</h4>
                    <p class="text-secondary">
                        Our dedicated support team is always ready to assist you with any questions or issues.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Deep Dive Section -->
<section class="py-5 bg-body">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="display-5 fw-bold mb-4">Focus on People, Not Paperwork</h2>
                <p class="lead text-secondary mb-4">
                    StaffSync eliminates the chaos of spreadsheets and manual filings.
                    By centralizing your HR operations, you reclaim hours every week to focus on culture and
                    strategy.
                </p>
                <ul class="list-unstyled text-secondary d-flex flex-column gap-3">
                    <li class="d-flex align-items-center">
                        <i class="ti ti-check text-success me-3 fs-4"></i> Automated Leave Management
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="ti ti-check text-success me-3 fs-4"></i> Smart Attendance Tracking
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="ti ti-check text-success me-3 fs-4"></i> One-Click Payroll Generation
                    </li>
                </ul>
            </div>
            <div class="col-lg-6 text-center" data-aos="fade-left">
                <div class="p-4 bg-primary bg-opacity-10 rounded-pill d-inline-block">
                    <i class="ti ti-files-off fs-1 text-primary display-1"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary bg-gradient text-white">
    <div class="container py-5 text-center">
        <h2 class="display-5 fw-bold mb-4">Experience the Difference</h2>
        <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 600px;">
            Join the fast-growing companies that rely on StaffSync to drive their success.
        </p>
        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
            <a href="/hrms/register.php"
                class="btn btn-light btn-lg px-4 px-md-5 rounded-pill fw-bold text-primary shadow-sm">
                Book a Free Demo
            </a>
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
                <div class="card h-100 bg-body text-body border-0 rounded-4 p-4 shadow-sm hover-shadow transition-all">
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
                <div class="card h-100 bg-body text-body border-0 rounded-4 p-4 shadow-sm hover-shadow transition-all">
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
                <div class="card h-100 bg-body text-body border-0 rounded-4 p-4 shadow-sm hover-shadow transition-all">
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
                <div class="card h-100 bg-body text-body border-0 rounded-4 p-4 shadow-sm hover-shadow transition-all">
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

<!-- Visual Footer -->
<footer class="bg-dark text-white pt-5 pb-3">
    <div class="container pt-4">
        <div class="row g-5">
            <!-- Brand and Social Column -->
            <div class="col-lg-4 mb-4">
                <a href="index.php" class="navbar-brand d-flex align-items-center text-decoration-none">
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
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">People</a>
                    </li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Recruit</a>
                    </li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Payroll</a>
                    </li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Expense</a>
                    </li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Connect</a>
                    </li>
                </ul>
            </div>

            <!-- Resources Column -->
            <div class="col-6 col-md-3 col-lg-2">
                <h6 class="fw-bold text-white mb-3">Resources</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small">
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Blog</a>
                    </li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Webinars</a>
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
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Diversity
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
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Careers</a>
                    </li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Newsroom</a>
                    </li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Legal</a>
                    </li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Contact</a>
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

<?php
require_once $root_path . '/components/layout/footer.php';
?>

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
            togglerIcon.classList.remove('ti-menu-2');
            togglerIcon.classList.add('ti-x');
        });

        offcanvasElement.addEventListener('hide.bs.offcanvas', function () {
            togglerIcon.classList.remove('ti-x');
            togglerIcon.classList.add('ti-menu-2');
        });
    }
</script>

<?php
// Include Nexus Bot for guests
if (!isLoggedIn()) {
    include $root_path . '/nexusbot/chat_widget.php';
}
?>