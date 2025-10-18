<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    // Redireciona para login.php com mensagem de erro
    header("Location: login.php?erro=1");
    exit();
}
?>
