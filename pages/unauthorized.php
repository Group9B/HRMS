<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /hrms/auth/login.php");
    exit();
}
require_once '../components/layout/header.php';
?>
<div class="container-md text-center my-5 pt-5">
    <h1 class="text-center">401 - Unauthorized Access</h1>
    <img src="/hrms/assets/img/401_image.png" alt="404_image" class="img-fluid mx-auto d-block" style="width: 300px;"
        loading="lazy">
    <p class="text-center">You do not have permission to view this page. Please contact the administrator if you believe
        this is a mistake.</p>
    <div class="text-center">
        <a href="/hrms/includes/redirect.php" class="btn btn-primary"><i class="fas fa-arrow-left pe-2"></i>Go to
            Dashboard</a>
    </div>
</div>
<?php
require_once '../components/layout/footer.php';
?>