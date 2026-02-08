<?php
// Fix includes to work from both root and subdirectories
$root_path = dirname(__DIR__); // Goes up 1 level from subscription/
require_once $root_path . '/components/layout/header.php';

$title = "Upgrade to Pro";
?>

<style>
    .pricing-card {
        transition: all 0.3s ease;
        border: 1px solid var(--bs-border-color);
        background-color: var(--bs-body-bg);
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
        color: var(--bs-body-color);
    }

    .check-list i {
        color: var(--bs-success);
        margin-right: 0.5rem;
    }

    /* Ensure text colors adapt to dark mode */
    [data-bs-theme="dark"] .pricing-card {
        border-color: var(--bs-border-color-translucent);
    }

    [data-bs-theme="dark"] .text-muted {
        color: rgba(255, 255, 255, 0.6) !important;
    }
</style>



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

<?php
require_once $root_path . '/components/layout/footer.php';

// Include Nexus Bot for guests (Footer includes it only for logged-in users)
if (!isLoggedIn()) {
    // Only include if not already defining Chat Widget functions?
    // chat_widget.php defines functions if inside class? No, it's a widget file.
    include $root_path . '/nexusbot/chat_widget.php';
}
?>