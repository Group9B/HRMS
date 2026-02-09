<?php
// register.php
// Public page for "Book a Demo" / Self-Service Registration
// functions.php is included via header.php to avoid redeclaration issues
$hideHeader = true;
require_once "../components/layout/header.php";

$title = "Book a Free Demo";
?>
<div class="body d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card login-card shadow-sm">
                    <div class="card-body p-4">

                        <h5 class="text-center mb-4 border-bottom pb-2 border-2 border-primary-subtle">
                            Book Your Free Demo
                            <br>
                            <small class="text-muted fw-normal fs-6">Experience StaffSync HRMS for 14 days</small>
                        </h5>

                        <form id="registerForm">
                            <div id="alertMessage" class="alert d-none" role="alert"></div>

                            <div class="mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-building"></i></span>
                                    <input type="text" class="form-control" id="company_name" name="company_name"
                                        required placeholder="e.g. Acme Corp">
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Work Email</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="email" class="form-control" id="email" name="email" required
                                        placeholder="you@company.com">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" required
                                        placeholder="+1 234 567 8900">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Create Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                    <input type="password" class="form-control" id="passwordInput" name="password"
                                        required minlength="8" placeholder="Min. 8 characters">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="ti ti-eye fs-4" id="icon"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="termsAgree" name="terms_agree"
                                    required>
                                <label class="form-check-label small text-muted" for="termsAgree">
                                    I agree to the <a href="terms.php" target="_blank"
                                        class="text-decoration-none">Terms and Conditions</a>
                                </label>
                            </div>

                            <div class="view-user-btn d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary" id="submitBtn">Start My Demo</button>
                            </div>

                            <div class="text-center">
                                <span class="small text-muted">Already have an account?</span>
                                <a href="auth/login.php" class="text-decoration-none small fw-bold">Login here</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3 text-muted small">
                    &copy; <?= date("Y") ?> StaffSync HRMS
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$additionalScripts = ['register.js'];
require_once "../components/layout/footer.php";
?>