<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <title>Editar Perfil</title>
</head>
<body>
<?php
session_start();
include 'conexao.php';


$id = $_SESSION['id'];

// Busca os dados do usuário
$sql = "SELECT nome_sobrenome, nome_usuario FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();
?>

<h2>Editar Perfil</h2>
<form action="atualizar_perfil.php" method="POST">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    
    <label>Nome e Sobremome:</label>
    <input type="text" name="nome_sobrenome" value="<?php echo $usuario['nome_sobrenome']; ?>" required>

    <label>Nome de Usuário:</label>
    <input type="text" name="nome_usuario" value="<?php echo $usuario['nome_usuario']; ?>" required>

    <label>Nova Senha (opcional):</label>
    <input type="password" name="senha">

    <button type="submit">Salvar Alterações</button>
</form>

</body>
</html>