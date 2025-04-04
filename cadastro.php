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
header("Location: login.html");
exit;   
}
?>