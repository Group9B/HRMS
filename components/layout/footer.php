<script src="/hrms/assets/js/jquery.js"></script>
<script src="/hrms/assets/js/bootstrap.js"></script>
<script src="/hrms/assets/js/datatable.js"></script>
<script src="/hrms/assets/js/datatable_bootstrap.js"></script>
<script src="/hrms/assets/js/datatable_responsive.js"></script>
<script src="/hrms/assets/js/datatable_bootstrap_responsive.js"></script>
<script src="/hrms/assets/js/chart.js"></script>
<script src="/hrms/assets/js/main.js"></script>
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