<?php
include '../verifica_admin.php';
// session_start();
// include '../conexao.php';

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     $email = $_POST['email'];
//     $senha = $_POST['senha'];

//     $sql = "SELECT * FROM admins WHERE email = '$email' AND senha = SHA2('$senha', 256)";
//     $resultado = mysqli_query($conn, $sql);

//     if (mysqli_num_rows($resultado) == 1) {
//         $_SESSION['admin'] = $email;
//         header("Location: painel.php");
//         exit;
//     } else {
//         $erro = "Usuário ou senha incorretos.";
//     }
// }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<div class="login-container">
    <h2>Login do Administrador</h2>
    <form method="POST">
        <input type="text" name="email" placeholder="Email" required><br>
        <input type="password" name="senha" placeholder="Senha" required><br>
        <button type="submit">Entrar</button>
    </form>
    <?php if(isset($erro)) echo "<p class='erro'>$erro</p>"; ?>
</div>
</body>
</html>
