<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cadastrologin.css">
    <title>Cadastro</title>

</head>
<body>

 <!-- Tela de fundo -->
 <div class="login-container">
        <!-- Layout branco -->
        <div class="login-box">
            <h1>Bem-vindo!</h1>
            <h2>Faça seu cadastro</h2>

<?php
session_start();
include 'conexao.php'; // Inclui o arquivo de conexão

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $nome_sobrenome = $_POST['nome'];
    $nome_usuario = $_POST['nome_usuario'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT); // Hash da senha

    $sql = "INSERT INTO usuarios (email,  nome_usuario, senha_hash, nome_sobrenome) VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $email, $nome_usuario, $senha, $nome_sobrenome);

    if ($stmt->execute()) {
        $_SESSION['nome_usuario'] = $nome_usuario;
        $_SESSION['nome_sobrenome'] = $nome_sobrenome;
        $_SESSION['id'] = $id;

        //echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

}

// No PHP que processa o formulário (ex: processa_cadastro.php)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Processa os dados do formulário...
    
    // Depois de processar, redireciona
    // Após processar o cadastro com sucesso
header("Location: cadastro_sucesso.php");
exit;   
}
?>
 
  <!-- Formulário de cadastro -->
  <form action="cadastrar.php" method="POST">
                <div class="input-container">
                    <label for="email">Email institucional</label>
    <input type="email" name="email" placeholder="Email institucional" required>
                </div>
                <div class="input-container">
                    <label for="nome">Nome e Sobrenome</label>
                    <input type="text" name="nome" placeholder="Nome e Sobrenome" required>
                </div>
                <div class="input-container">
                    <label for="username">Nome de usuário</label>
                    <input type="text" name="nome_usuario" placeholder="(O que aparecerá para os outros usuários)" required>
                </div>
                
                <div class="input-container">
                    <label for="senha">Senha</label>
                    <input type="password" name="senha" placeholder="Crie uma senha" required>
                </div>
                <button type="submit" class="botao">Cadastrar</button>
                                <form onsubmit="return false;">
            </form>

            <p class="cadastro-link">Já tem uma conta? <a href="login.php">Faça login</a></p>


            </div>
    </div>
       


</body>
</html>