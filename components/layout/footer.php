<?php require_once __DIR__ . '/../modal/confirmation_modal.php'; ?>
<script src="/hrms/assets/js/jquery.js"></script>
<script src="/hrms/assets/js/bootstrap.js"></script>
<script src="/hrms/assets/js/datatable.js"></script>
<script src="/hrms/assets/js/datatable_bootstrap.js"></script>
<script src="/hrms/assets/js/datatable_responsive.js"></script>
<script src="/hrms/assets/js/datatable_bootstrap_responsive.js"></script>
<script src="/hrms/assets/js/chart.js"></script>
<script src="/hrms/assets/js/main.js"></script>
<?php
if (isset($additionalScripts) && is_array($additionalScripts)) {
    foreach ($additionalScripts as $script) {
        echo '<script src="/hrms/assets/js/' . htmlspecialchars($script) . '"></script>' . PHP_EOL;
    }
}
?>
<?php
if (isLoggedIn()):
    ?>
    <script>
        createAvatar({ id: <?= $_SESSION['user_id'] ?? 0 ?>, username: "<?= $_SESSION['username'] ?? 'User' ?>" });
    </script>
    <?php
endif;
?>
</body>

</html>