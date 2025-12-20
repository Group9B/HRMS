<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /hrms/auth/login.php");
    exit();
}
require_once '../components/layout/header.php';
?>
<div class="container-md text-center my-5 pt-5">
    <h1 class="text-center">404 - Page Not Found</h1>
    <img src="/hrms/assets/img/404_image.png" alt="404_image" class="img-fluid mx-auto d-block" style="width: 300px;"
        loading="lazy">
    <p class="text-center">The page you are looking for does not exist or has been moved.</p>
    <div class="text-center">
        <a href="/hrms/includes/redirect.php" class="btn btn-primary"><i class="ti ti-arrow-left pe-2"></i>Go to
            Dashboard</a>
    </div>
</div>
<?php
require_once '../components/layout/footer.php';
?>