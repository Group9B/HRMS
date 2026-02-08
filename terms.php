<?php
$title = "Terms and Conditions - StaffSync HRMS";
require_once "components/layout/header.php";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="mb-4">Terms and Conditions</h1>
            <p class="text-muted mb-5">Last updated:
                <?= date("F j, Y") ?>
            </p>

            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h4>1. Introduction</h4>
                    <p>Welcome to StaffSync HRMS. By accessing or using our website and services, you agree to be bound
                        by these Terms and Conditions.</p>

                    <h4>2. Use of Service</h4>
                    <p>You agree to use our services only for lawful purposes and in accordance with these Terms. You
                        are responsible for maintaining the confidentiality of your account credentials.</p>

                    <h4>3. Intellectual Property</h4>
                    <p>The Service and its original content, features, and functionality are and will remain the
                        exclusive property of StaffSync HRMS and its licensors.</p>

                    <h4>4. Termination</h4>
                    <p>We may terminate or suspend your account immediately, without prior notice or liability, for any
                        reason whatsoever, including without limitation if you breach the Terms.</p>

                    <h4>5. Limitation of Liability</h4>
                    <p>In no event shall StaffSync HRMS, nor its directors, employees, partners, agents, suppliers, or
                        affiliates, be liable for any indirect, incidental, special, consequential or punitive damages.
                    </p>

                    <h4>6. Changes</h4>
                    <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. What
                        constitutes a material change will be determined at our sole discretion.</p>

                    <h4>7. Contact Us</h4>
                    <p>If you have any questions about these Terms, please contact us at support@staffsync.com.</p>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="register.php" class="btn btn-primary">Back to Registration</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once "components/layout/footer.php";
?>