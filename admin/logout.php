<?php
session_start();
session_destroy();
header("Location: homepage_admin.php");
exit;
?>
