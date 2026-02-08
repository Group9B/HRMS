<?php
$title = "Features - StaffSync HRMS";
$root_path = __DIR__;
require_once '../components/layout/header.php';
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

    /* Dark mode specific overrides */
    [data-bs-theme="dark"] .bg-light-subtle {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }
</style>

<!-- Hero Section -->
<section class="py-5 bg-body border-bottom">
    <div class="container text-center">
        <div class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill fw-semibold"
            data-aos="fade-up">
            <i class="ti ti-bolt me-2"></i> Powerful Capabilities
        </div>
        <h1 class="display-4 fw-bold text-body-emphasis mb-4" data-aos="fade-up" data-aos-delay="100">
            Tools That Empower Your <span class="text-primary">Entire Organization</span>
        </h1>
        <p class="lead text-secondary mb-5 mx-auto" style="max-width: 700px;" data-aos="fade-up" data-aos-delay="200">
            From hiring to retiring, StaffSync provides a complete suite of tools to manage your workforce
            efficiently and effectively.
        </p>
    </div>
</section>

<!-- Main Features -->
<section class="py-5 bg-body-tertiary">
    <div class="container py-5">

        <!-- Feature Block 1: Core HR -->
        <div class="row align-items-center mb-5 pb-5 border-bottom border-light-subtle">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="pe-lg-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fs-2 rounded-3 mb-3"
                        style="width: 50px; height: 50px;">
                        <i class="ti ti-users"></i>
                    </div>
                    <h2 class="fw-bold mb-3">Core HR Management</h2>
                    <p class="lead text-secondary mb-4">
                        Centralize all your employee data in one secure location. Say goodbye to scattered spreadsheets
                        and filing cabinets.
                    </p>
                    <ul class="list-unstyled text-secondary">
                        <li class="mb-2 d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>Digital Employee Profiles & Document Storage</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>Organization Chart & Hierarchy View</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>Asset & Inventory Management</span>
                        </li>
                        <li class="d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>Self-Service Portal for Employees</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="bg-body rounded-4 shadow-sm p-4 border text-center"
                    style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-muted opacity-50">
                        <i class="ti ti-address-book fs-1 mb-2"></i>
                        <h5>Employee Directory Visual</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature Block 2: Time & Attendance -->
        <div class="row align-items-center mb-5 pb-5 border-bottom border-light-subtle flex-lg-row-reverse">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-left">
                <div class="ps-lg-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success fs-2 rounded-3 mb-3"
                        style="width: 50px; height: 50px;">
                        <i class="ti ti-clock"></i>
                    </div>
                    <h2 class="fw-bold mb-3">Time & Attendance</h2>
                    <p class="lead text-secondary mb-4">
                        Capture accurate time records and streamline leave management with our intuitive tracking
                        system.
                    </p>
                    <ul class="list-unstyled text-secondary">
                        <li class="mb-2 d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>Check-in/out with Geolocation & Geofencing</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>Biometric Integration Support</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>Automated Leave Accruals & Balances</span>
                        </li>
                        <li class="d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>Overtime Calculation & Approval Workflows</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-right">
                <div class="bg-body rounded-4 shadow-sm p-4 border text-center"
                    style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-muted opacity-50">
                        <i class="ti ti-calendar-time fs-1 mb-2"></i>
                        <h5>Attendance Dashboard Visual</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature Block 3: Payroll -->
        <div class="row align-items-center mb-5 pb-5">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="pe-lg-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning fs-2 rounded-3 mb-3"
                        style="width: 50px; height: 50px;">
                        <i class="ti ti-receipt"></i>
                    </div>
                    <h2 class="fw-bold mb-3">Automated Payroll</h2>
                    <p class="lead text-secondary mb-4">
                        Run payroll with confidence in just a few clicks. Ensure compliance and timely payments every
                        month.
                    </p>
                    <ul class="list-unstyled text-secondary">
                        <li class="mb-2 d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>One-click Payroll Generation</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>Automated Tax & Deduction Calculations</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>Direct Bank Transfer Files</span>
                        </li>
                        <li class="d-flex align-items-start"><i class="ti ti-check text-success me-2 mt-1"></i>
                            <span>Digital Payslips via Email & App</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="bg-body rounded-4 shadow-sm p-4 border text-center"
                    style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-muted opacity-50">
                        <i class="ti ti-coin fs-1 mb-2"></i>
                        <h5>Payroll Processing Visual</h5>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Additional Features Grid -->
<section class="py-5 bg-body">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">More To Explore</h2>
            <p class="text-secondary">Comprehensive features designed for every aspect of HR.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 bg-light-subtle rounded-4 p-3 hover-shadow transition-all">
                    <div class="card-body">
                        <div class="mb-3 text-info"><i class="ti ti-briefcase fs-2"></i></div>
                        <h5 class="fw-bold">Recruitment (ATS)</h5>
                        <p class="text-secondary small mb-0">Track candidates, schedule interviews, and manage job
                            postings from a single dashboard.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 bg-light-subtle rounded-4 p-3 hover-shadow transition-all">
                    <div class="card-body">
                        <div class="mb-3 text-danger"><i class="ti ti-chart-arrows fs-2"></i></div>
                        <h5 class="fw-bold">Performance Reviews</h5>
                        <p class="text-secondary small mb-0">Set OKRs, conduct 360-degree feedback, and track employee
                            growth and appraisals.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 bg-light-subtle rounded-4 p-3 hover-shadow transition-all">
                    <div class="card-body">
                        <div class="mb-3 text-primary"><i class="ti ti-ticket fs-2"></i></div>
                        <h5 class="fw-bold">Help Desk</h5>
                        <p class="text-secondary small mb-0">Internal ticketing system for IT support, HR queries, and
                            facility management.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100 border-0 bg-light-subtle rounded-4 p-3 hover-shadow transition-all">
                    <div class="card-body">
                        <div class="mb-3 text-success"><i class="ti ti-report-money fs-2"></i></div>
                        <h5 class="fw-bold">Expense Claims</h5>
                        <p class="text-secondary small mb-0">Submit and approve expense claims digitally with receipt
                            uploads and policy enforcement.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
                <div class="card h-100 border-0 bg-light-subtle rounded-4 p-3 hover-shadow transition-all">
                    <div class="card-body">
                        <div class="mb-3 text-warning"><i class="ti ti-device-mobile fs-2"></i></div>
                        <h5 class="fw-bold">Mobile App</h5>
                        <p class="text-secondary small mb-0">Stay connected on the go. Available for iOS and Android for
                            all employees.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="600">
                <div class="card h-100 border-0 bg-light-subtle rounded-4 p-3 hover-shadow transition-all">
                    <div class="card-body">
                        <div class="mb-3 text-secondary"><i class="ti ti-shield-lock fs-2"></i></div>
                        <h5 class="fw-bold">Role-Based Security</h5>
                        <p class="text-secondary small mb-0">Granular access controls ensure data privacy and security
                            across the organization.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary bg-gradient text-white">
    <div class="container py-5 text-center">
        <h2 class="display-5 fw-bold mb-4">See Features in Action</h2>
        <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 600px;">
            Experience the full power of StaffSync with our no-obligation free trial.
        </p>
        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
            <a href="/hrms/register.php"
                class="btn btn-light btn-lg px-4 px-md-5 rounded-pill fw-bold text-primary shadow-sm">Start Free
                Trial</a>
        </div>
    </div>
</section>

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
</script>

<?php
// Include Nexus Bot for guests
if (!isLoggedIn()) {
    include $root_path . '/nexusbot/chat_widget.php';
}
?>