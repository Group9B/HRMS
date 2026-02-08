<?php
$title = "StaffSync HRMS - Modern HR Solution";
$root_path = __DIR__;
require_once $root_path . '/components/layout/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ========================================
       PREMIUM LANDING PAGE STYLES
    ======================================== */
    
    body {
        font-family: 'Inter', sans-serif;
    }

    /* Gradient Text */
    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Hero Section */
    .hero-section {
        position: relative;
        overflow: hidden;
        min-height: 90vh;
        display: flex;
        align-items: center;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 80%;
        height: 150%;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.15) 0%, transparent 70%);
        animation: pulse-glow 8s ease-in-out infinite;
    }

    .hero-section::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 60%;
        height: 100%;
        background: radial-gradient(circle, rgba(118, 75, 162, 0.1) 0%, transparent 60%);
        animation: pulse-glow 10s ease-in-out infinite reverse;
    }

    @keyframes pulse-glow {
        0%, 100% { opacity: 0.5; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.1); }
    }

    /* Floating Elements */
    .floating {
        animation: floating 6s ease-in-out infinite;
    }

    .floating-delay {
        animation: floating 6s ease-in-out infinite 2s;
    }

    @keyframes floating {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }

    /* Glassmorphism Cards */
    .glass-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .glass-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border-color: rgba(102, 126, 234, 0.3);
    }

    [data-bs-theme="light"] .glass-card {
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(0, 0, 0, 0.08);
    }

    /* Feature Icon Wrapper */
    .feature-icon {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        font-size: 1.75rem;
        transition: all 0.3s ease;
    }

    .glass-card:hover .feature-icon {
        transform: scale(1.1);
    }

    /* Stats Section */
    .stats-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .stats-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .stat-item {
        position: relative;
        z-index: 1;
    }

    .stat-number {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1;
    }

    /* Testimonial Card */
    .testimonial-card {
        background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
        backdrop-filter: blur(20px);
    }

    [data-bs-theme="dark"] .testimonial-card {
        background: linear-gradient(145deg, rgba(30,30,30,0.9), rgba(20,20,20,0.7));
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(180deg, var(--bs-body-bg) 0%, rgba(102, 126, 234, 0.05) 100%);
    }

    /* Premium Button Styles */
    .btn-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }

    .btn-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }

    .btn-gradient:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
    }

    .btn-gradient:hover::before {
        opacity: 1;
    }

    /* Animated Badge */
    .badge-animated {
        animation: badge-pulse 2s ease-in-out infinite;
    }

    @keyframes badge-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(102, 126, 234, 0); }
    }

    /* Decorative Blobs */
    .blob-1 {
        position: absolute;
        width: 500px;
        height: 500px;
        background: linear-gradient(180deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
        filter: blur(60px);
        animation: blob-morph 10s ease-in-out infinite;
    }

    @keyframes blob-morph {
        0%, 100% { border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; }
        50% { border-radius: 60% 40% 30% 70% / 50% 60% 40% 60%; }
    }

    /* Hover Effects */
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    /* Trust Badges */
    .trust-badge {
        opacity: 0.5;
        filter: grayscale(100%);
        transition: all 0.3s ease;
    }

    .trust-badge:hover {
        opacity: 1;
        filter: grayscale(0%);
    }

    /* Footer Styles */
    .footer-premium {
        background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
    }

    .footer-link:hover {
        color: #667eea !important;
        padding-left: 5px;
    }

    /* Mobile Menu */
    #offcanvasNavbar {
        width: 70% !important;
    }

    /* Smooth Scrolling */
    html {
        scroll-behavior: smooth;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--bs-body-bg);
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 4px;
    }
</style>

<!-- Hero Section -->
<section class="hero-section py-5 bg-body position-relative">
    <div class="container position-relative z-2">
        <div class="row align-items-center g-5 min-vh-75">
            <div class="col-lg-6 text-center text-lg-start">
                <!-- Animated Badge -->
                <div class="badge bg-primary bg-opacity-10 text-primary mb-4 px-4 py-2 rounded-pill fw-semibold badge-animated"
                    data-aos="fade-up">
                    <i class="ti ti-sparkles me-2"></i> Next-Gen HR Platform
                </div>
                
                <!-- Main Heading -->
                <h1 class="display-3 fw-bold text-body-emphasis mb-4 lh-sm" data-aos="fade-up" data-aos-delay="100">
                    Transform How You <br>
                    <span class="gradient-text">Manage People</span>
                </h1>
                
                <!-- Subheading -->
                <p class="fs-5 text-secondary mb-5 pe-lg-5" data-aos="fade-up" data-aos-delay="200">
                    StaffSync unifies attendance, payroll, recruitment, and performance management 
                    into one powerful platform. Built for modern teams that move fast.
                </p>
                
                <!-- CTA Buttons -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start mb-4"
                    data-aos="fade-up" data-aos-delay="300">
                    <a href="/hrms/register.php" class="btn btn-gradient btn-lg px-5 py-3 rounded-pill shadow-lg fw-semibold">
                        Start Free Trial
                        <i class="ti ti-arrow-right ms-2"></i>
                    </a>
                    <a href="/hrms/features.php" class="btn btn-outline-secondary btn-lg px-5 py-3 rounded-pill fw-semibold hover-lift">
                        <i class="ti ti-player-play me-2"></i> Explore Features
                    </a>
                </div>
                
                <!-- Trust Indicators -->
                <div class="d-flex flex-wrap gap-4 justify-content-center justify-content-lg-start text-secondary small" data-aos="fade-up" data-aos-delay="400">
                    <span><i class="ti ti-shield-check text-success me-2"></i> SOC 2 Compliant</span>
                    <span><i class="ti ti-lock text-success me-2"></i> Bank-grade Security</span>
                    <span><i class="ti ti-headset text-success me-2"></i> 24/7 Support</span>
                </div>
            </div>
            
            <!-- Hero Visual -->
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="400">
                <div class="position-relative">
                    <!-- Main Dashboard Image -->
                    <div class="rounded-4 shadow-lg overflow-hidden floating">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/hr-management-8826276-7146033.png?f=webp"
                            class="img-fluid" alt="StaffSync Dashboard" loading="lazy">
                    </div>
                    
                    <!-- Floating Stats Card -->
                    <div class="position-absolute bottom-0 start-0 translate-middle-x bg-body rounded-3 shadow-lg p-3 border floating-delay d-none d-lg-block" style="left: 15%;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-2">
                                <i class="ti ti-trending-up fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-success">+45%</div>
                                <small class="text-secondary">Productivity</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating User Card -->
                    <div class="position-absolute top-0 end-0 bg-body rounded-3 shadow-lg p-3 border floating d-none d-lg-block" style="right: -10%;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex">
                                <img src="https://i.pravatar.cc/40?img=1" class="rounded-circle border border-2 border-white" style="margin-right: -10px;" alt="User">
                                <img src="https://i.pravatar.cc/40?img=2" class="rounded-circle border border-2 border-white" style="margin-right: -10px;" alt="User">
                                <img src="https://i.pravatar.cc/40?img=3" class="rounded-circle border border-2 border-white" alt="User">
                            </div>
                            <small class="text-secondary">10k+ users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trusted By Section -->
<section class="py-4 border-bottom bg-body-tertiary">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 text-center">
                <p class="text-secondary small text-uppercase letter-spacing-2 mb-3">Trusted by 500+ companies worldwide</p>
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-5 opacity-50">
                    <i class="ti ti-brand-google fs-1"></i>
                    <i class="ti ti-brand-amazon fs-1"></i>
                    <i class="ti ti-brand-microsoft fs-1"></i>
                    <i class="ti ti-brand-slack fs-1"></i>
                    <i class="ti ti-brand-spotify fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-body">
    <div class="container py-5">
        <!-- Section Header -->
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto" data-aos="fade-up">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3">Features</span>
                <h2 class="display-5 fw-bold mb-3">Everything You Need,<br><span class="gradient-text">Nothing You Don't</span></h2>
                <p class="lead text-secondary">
                    A complete HR platform designed to simplify your workflow and empower your team.
                </p>
            </div>
        </div>

        <!-- Feature Cards Grid -->
        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="glass-card rounded-4 p-4 h-100">
                    <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-3 mb-4">
                        <i class="ti ti-users"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Core HR</h4>
                    <p class="text-secondary mb-0">
                        Centralize employee records, documents, and organizational structure in one secure hub.
                    </p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="150">
                <div class="glass-card rounded-4 p-4 h-100">
                    <div class="feature-icon bg-success bg-opacity-10 text-success rounded-3 mb-4">
                        <i class="ti ti-clock"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Time & Attendance</h4>
                    <p class="text-secondary mb-0">
                        GPS-enabled clock-in, shift scheduling, and real-time attendance tracking made simple.
                    </p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="glass-card rounded-4 p-4 h-100">
                    <div class="feature-icon bg-warning bg-opacity-10 text-warning rounded-3 mb-4">
                        <i class="ti ti-receipt-2"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Payroll</h4>
                    <p class="text-secondary mb-0">
                        One-click payroll processing with automated tax calculations and digital payslips.
                    </p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="250">
                <div class="glass-card rounded-4 p-4 h-100">
                    <div class="feature-icon bg-info bg-opacity-10 text-info rounded-3 mb-4">
                        <i class="ti ti-user-search"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Recruitment</h4>
                    <p class="text-secondary mb-0">
                        Full ATS with job postings, applicant tracking, and seamless onboarding workflows.
                    </p>
                </div>
            </div>

            <!-- Feature 5 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="glass-card rounded-4 p-4 h-100">
                    <div class="feature-icon bg-danger bg-opacity-10 text-danger rounded-3 mb-4">
                        <i class="ti ti-chart-dots-2"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Performance</h4>
                    <p class="text-secondary mb-0">
                        Goal setting, OKRs, 360° reviews, and continuous feedback for growth-focused teams.
                    </p>
                </div>
            </div>

            <!-- Feature 6 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="350">
                <div class="glass-card rounded-4 p-4 h-100">
                    <div class="feature-icon bg-secondary bg-opacity-10 text-secondary rounded-3 mb-4">
                        <i class="ti ti-report-analytics"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Analytics</h4>
                    <p class="text-secondary mb-0">
                        Real-time dashboards and custom reports to make data-driven HR decisions.
                    </p>
                </div>
            </div>
        </div>

        <!-- View All Features CTA -->
        <div class="text-center mt-5 pt-4" data-aos="fade-up">
            <a href="/hrms/features.php" class="btn btn-outline-primary rounded-pill px-5 py-3 fw-semibold hover-lift">
                View All Features <i class="ti ti-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section py-5 text-white">
    <div class="container py-5 position-relative">
        <div class="row g-4">
            <div class="col-6 col-md-3 text-center stat-item" data-aos="zoom-in">
                <div class="stat-number mb-2">500+</div>
                <p class="mb-0 opacity-75">Companies</p>
            </div>
            <div class="col-6 col-md-3 text-center stat-item" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-number mb-2">50k+</div>
                <p class="mb-0 opacity-75">Employees</p>
            </div>
            <div class="col-6 col-md-3 text-center stat-item" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-number mb-2">99.9%</div>
                <p class="mb-0 opacity-75">Uptime</p>
            </div>
            <div class="col-6 col-md-3 text-center stat-item" data-aos="zoom-in" data-aos-delay="300">
                <div class="stat-number mb-2">4.9</div>
                <p class="mb-0 opacity-75">User Rating</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5 bg-body-tertiary">
    <div class="container py-5">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto" data-aos="fade-up">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3">How It Works</span>
                <h2 class="display-5 fw-bold mb-3">Get Started in <span class="gradient-text">3 Simple Steps</span></h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-4" style="width: 80px; height: 80px; font-size: 2rem; font-weight: 700;">1</div>
                    <h4 class="fw-bold mb-3">Sign Up Free</h4>
                    <p class="text-secondary">Create your company account in under 2 minutes. No credit card required.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-4" style="width: 80px; height: 80px; font-size: 2rem; font-weight: 700;">2</div>
                    <h4 class="fw-bold mb-3">Add Your Team</h4>
                    <p class="text-secondary">Import employee data or invite them directly. Setup takes minutes.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-4" style="width: 80px; height: 80px; font-size: 2rem; font-weight: 700;">3</div>
                    <h4 class="fw-bold mb-3">Go Live</h4>
                    <p class="text-secondary">Start managing attendance, leaves, payroll and more instantly.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-body">
    <div class="container py-5">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto" data-aos="fade-up">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3">Testimonials</span>
                <h2 class="display-5 fw-bold mb-3">Loved by <span class="gradient-text">HR Teams</span></h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="testimonial-card rounded-4 p-4 h-100 border shadow-sm">
                    <div class="d-flex mb-3">
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                    </div>
                    <p class="text-body-emphasis mb-4">
                        "StaffSync transformed how we handle HR. It saved us 20+ hours a week on payroll alone. The interface is so intuitive!"
                    </p>
                    <div class="d-flex align-items-center">
                        <img src="https://i.pravatar.cc/50?img=32" alt="User" class="rounded-circle me-3" width="50" height="50">
                        <div>
                            <h6 class="fw-bold mb-0">Sarah Jenkins</h6>
                            <small class="text-secondary">HR Director, TechFlow</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial-card rounded-4 p-4 h-100 border shadow-sm">
                    <div class="d-flex mb-3">
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                    </div>
                    <p class="text-body-emphasis mb-4">
                        "Finally, an HR system that doesn't feel like it was built in 1999. Clean, fast, and our employees actually use it."
                    </p>
                    <div class="d-flex align-items-center">
                        <img src="https://i.pravatar.cc/50?img=12" alt="User" class="rounded-circle me-3" width="50" height="50">
                        <div>
                            <h6 class="fw-bold mb-0">Michael Chen</h6>
                            <small class="text-secondary">CEO, StartupXYZ</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="testimonial-card rounded-4 p-4 h-100 border shadow-sm">
                    <div class="d-flex mb-3">
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                        <i class="ti ti-star-filled text-warning"></i>
                    </div>
                    <p class="text-body-emphasis mb-4">
                        "The recruitment module alone is worth it. We reduced our time-to-hire by 40% within the first quarter."
                    </p>
                    <div class="d-flex align-items-center">
                        <img src="https://i.pravatar.cc/50?img=25" alt="User" class="rounded-circle me-3" width="50" height="50">
                        <div>
                            <h6 class="fw-bold mb-0">Emily Watson</h6>
                            <small class="text-secondary">Talent Lead, GrowthCo</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-4">Ready to Transform Your HR?</h2>
                <p class="lead text-secondary mb-5">
                    Join 500+ companies already growing with StaffSync. Start your free 14-day trial today.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="/hrms/register.php" class="btn btn-gradient btn-lg px-5 py-3 rounded-pill shadow-lg fw-semibold">
                        Start Free Trial <i class="ti ti-arrow-right ms-2"></i>
                    </a>
                    <a href="/hrms/subscription/purchase.php" class="btn btn-outline-primary btn-lg px-5 py-3 rounded-pill fw-semibold hover-lift">
                        View Pricing
                    </a>
                </div>
                <p class="text-secondary small mt-4">
                    <i class="ti ti-circle-check text-success me-1"></i> No credit card required
                    <span class="mx-2">•</span>
                    <i class="ti ti-circle-check text-success me-1"></i> Cancel anytime
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Premium Footer -->
<footer class="footer-premium text-white pt-5 pb-3">
    <div class="container">
        <div class="row g-4 border-bottom border-secondary border-opacity-25 pb-5 mb-0">
            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none mb-3">
                    <img src="/hrms/assets/img/SS.png" alt="Logo" width="35" height="35" class="me-2">
                    <span class="fs-4 fw-bold">StaffSync</span>
                </a>
                <p class="text-white-50 small mb-4">
                    The complete HR Operating System for modern businesses. Manage your team, payroll, and culture in one place.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white-50 hover-white transition-all"><i class="ti ti-brand-twitter fs-4"></i></a>
                    <a href="#" class="text-white-50 hover-white transition-all"><i class="ti ti-brand-linkedin fs-4"></i></a>
                    <a href="#" class="text-white-50 hover-white transition-all"><i class="ti ti-brand-facebook fs-4"></i></a>
                    <a href="#" class="text-white-50 hover-white transition-all"><i class="ti ti-brand-instagram fs-4"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h6 class="text-white fw-bold mb-3 text-uppercase small">Product</h6>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li><a href="/hrms/features.php" class="text-decoration-none text-white-50 footer-link transition-all">Features</a></li>
                    <li><a href="/hrms/subscription/purchase.php" class="text-decoration-none text-white-50 footer-link transition-all">Pricing</a></li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Security</a></li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Updates</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h6 class="text-white fw-bold mb-3 text-uppercase small">Company</h6>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">About Us</a></li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Careers</a></li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Blog</a></li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Contact</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h6 class="text-white fw-bold mb-3 text-uppercase small">Resources</h6>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Help Center</a></li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">API Docs</a></li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Webinars</a></li>
                    <li><a href="/hrms/benefits.php" class="text-decoration-none text-white-50 footer-link transition-all">Benefits</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h6 class="text-white fw-bold mb-3 text-uppercase small">Legal</h6>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li><a href="/hrms/terms.php" class="text-decoration-none text-white-50 footer-link transition-all">Terms</a></li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Privacy</a></li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">Cookies</a></li>
                    <li><a href="#" class="text-decoration-none text-white-50 footer-link transition-all">GDPR</a></li>
                </ul>
            </div>
        </div>

        <div class="row pt-4 align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="mb-0 small text-white-50">&copy; <?= date('Y') ?> StaffSync. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <span class="small text-white-50">Made with <i class="ti ti-heart text-danger"></i> for HR Teams</span>
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

    // Add "Coming Soon" alert to all footer links with #
    document.querySelectorAll('footer a[href="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            alert('This feature will be available soon!');
        });
    });

    // Toggle Hamburger/Close Icon
    const offcanvasElement = document.getElementById('offcanvasNavbar');
    const togglerIcon = document.getElementById('navbarTogglerIcon');

    if (offcanvasElement && togglerIcon) {
        offcanvasElement.addEventListener('show.bs.offcanvas', function() {
            togglerIcon.classList.remove('ti-menu-2');
            togglerIcon.classList.add('ti-x');
        });

        offcanvasElement.addEventListener('hide.bs.offcanvas', function() {
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