<?php
session_start().
print_r(in_array($_SESSION['role_id'], [2, 3]));
?>
