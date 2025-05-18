<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <title>Atualizar Perfil</title>
</head>
<body>
<?php
session_start();
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $nome_usuario = $_POST["nome_usuario"];
    $nova_senha = $_POST["senha"];

    if (!empty($nova_senha)) {
        $senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET nome_usuario = ?, senha_hash = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nome_usuario, $senha_hash, $id);
    } else {
        $sql = "UPDATE usuarios SET nome_usuario = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nome_usuario, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['nome_usuario'] = $nome_usuario;
        echo "Perfil atualizado com sucesso!";
    } else {
        echo "Erro ao atualizar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}


header("Location: login.html"); 

?>

</body>
</html>
