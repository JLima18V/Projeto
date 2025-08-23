<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_livro = intval($_POST['id_livro']);
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $genero = $_POST['genero'];
    $estado = $_POST['estado'];

    // Primeiro verificar se o livro pertence ao usuário
    $queryCheck = "SELECT imagens FROM livros WHERE id = ? AND id_usuario = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("ii", $id_livro, $id_usuario);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 0) {
        die("Livro não encontrado ou você não tem permissão para editar.");
    }

    $oldImagensStr = $resultCheck->fetch_assoc()['imagens'];
    $stmtCheck->close();

    $imagensNovas = [];

    // Tratamento de upload das imagens
    if (!empty($_FILES['imagens']['name'][0])) {
        // Excluir imagens antigas do servidor (opcional)
        $oldImagens = explode(",", $oldImagensStr);
        foreach ($oldImagens as $img) {
            $imgPath = 'uploads/' . $img;
            if (file_exists($imgPath) && !empty($img)) {
                unlink($imgPath);
            }
        }

        // Salvar novas imagens
        $totalFiles = count($_FILES['imagens']['name']);
        for ($i = 0; $i < $totalFiles; $i++) {
            $tmpName = $_FILES['imagens']['tmp_name'][$i];
            $name = basename($_FILES['imagens']['name'][$i]);

            // Evitar sobrescrever com nomes iguais
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $novoNome = uniqid() . '.' . $ext;
            $destino = 'uploads/' . $novoNome;

            if (move_uploaded_file($tmpName, $destino)) {
                $imagensNovas[] = $novoNome;
            }
        }
    } else {
        // Se não enviou imagens novas, manter as antigas
        $imagensNovas = explode(",", $oldImagensStr);
    }

    $imagensStr = implode(",", $imagensNovas);

    // Atualizar o livro
    $queryUpdate = "UPDATE livros SET titulo = ?, autor = ?, genero = ?, estado = ?, imagens = ? WHERE id = ? AND id_usuario = ?";
    $stmtUpdate = $conn->prepare($queryUpdate);
    $stmtUpdate->bind_param("sssssii", $titulo, $autor, $genero, $estado, $imagensStr, $id_livro, $id_usuario);

    if ($stmtUpdate->execute()) {
        header("Location: perfil.php?edit=success");
    } else {
        echo "Erro ao atualizar o livro.";
    }
    $stmtUpdate->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="edit_perfil.css">
    <title>Editar Livro</title>
</head>
<body>

<header class="header">
    <a href="homepage.php">
        <img src="imagens/logo-trocatrocajk.png" alt="Logo" class="logo">
    </a>
    <div class="search-container">
        <input type="text" class="search-bar" placeholder="Pesquise livros">
        <img src="imagens/icone-filtro.svg" alt="Filtrar" class="filter-icon">
    </div>
    <div class="icons">
        <img src="imagens/icone-publicarlivro.svg" alt="Publicar livro" onclick="abrirPopup()">
        <img src="imagens/icone-listadedesejo.svg" alt="Lista de desejos" onclick="window.location.href='listadedesejo.php'">
        <img src="imagens/icone-mensagem.svg" alt="Chat">
        <img src="imagens/icone-perfil.svg" alt="Perfil" onclick="window.location.href='perfil.php'">
    </div>
</header>

<main class="editar-perfil-container">
    <h2>Editar Livro</h2>

    <?php if (isset($erro)): ?>
        <p style="color: red;"><?php echo $erro; ?></p>
    <?php endif; ?>

    <form action="editar_livro.php" method="POST" class="editar-livro-form">
        <input type="hidden" name="id_livro" value="<?php echo $id_livro; ?>">

        <label for="titulo">Título:</label>
        <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($livro['titulo']); ?>" required>

        <label for="autor">Autor:</label>
        <input type="text" id="autor" name="autor" value="<?php echo htmlspecialchars($livro['autor']); ?>" required>

        <label for="genero">Gênero:</label>
        <input type="text" id="genero" name="genero" value="<?php echo htmlspecialchars($livro['genero']); ?>" required>

        <label for="estado">Estado:</label>
        <input type="text" id="estado" name="estado" value="<?php echo htmlspecialchars($livro['estado']); ?>" required>

        <div class="botoes-container">
            <button type="submit" class="botao-estilizado salvar">Salvar Alterações</button>
            <a href="perfil.php" class="botao-estilizado cancelar">Cancelar</a>
        </div>
    </form>
</main>

</body>
</html>
