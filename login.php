<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="loginn.css">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <title>Login</title>
</head>
<body>
<!-- Tela de login -->
<div class="login-container">
    <!-- Layout branco -->
    <div class="login-box">
        <h1>Bem-vindo!</h1>
        <h2>Faça login</h2>
       
        
<?php
session_start();
include 'conexao.php'; // Arquivo que conecta ao banco

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Busca o usuário no banco de dados
    $sql = "SELECT id, nome_usuario, senha_hash FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Verifica se a senha está correta
        if (password_verify($senha, $row['senha_hash'])) {
            // Armazena os dados do usuário na sessão
            $_SESSION['id'] = $row['id'];
            $_SESSION['usuario'] = $row['nome_usuario'];
            
            header("Location: homepage.php"); // Redireciona para a homepage
            exit();
        } else {
            echo "<strong>Senha incorreta!</strong>";
        }
    } else {
        echo "<strong>Usuário não encontrado!</strong>";
    }

    $stmt->close();
    $conn->close();
}
?>


       
        
        <!-- Formulário de login -->
        <form action="login.php" method="POST">
            <div class="input-container">
                <label for="email">Email institucional</label>
                <input type="email" id="email" name="email" placeholder="Digite seu email" required>
            </div>
            <div class="input-container">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>
            <!-- Botão de envio -->
            <button type="submit" class="botao">Entrar</button>
        </form>
        
        <!-- Opção de cadastro -->
        <p>ou</p>
        <a href="cadastro.html" class="botao">Cadastre-se</a>
    </div> 
     
</div>
</body>
</html>
