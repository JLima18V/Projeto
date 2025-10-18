<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se não houver sessão de admin, redireciona pro login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
