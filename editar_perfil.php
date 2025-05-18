<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="edit_perfil.css">
    <title>Editar Perfil</title>
</head>
<body>
    <!-- Cabeçalho -->
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
            <img src="imagens/icone-listadedesejo.svg" alt="Lista de desejos" onclick="window.location.href='listadedesejo.html'">
            <img src="imagens/icone-mensagem.svg" alt="Chat">
            <img src="imagens/icone-perfil.svg" alt="Perfil" onclick="window.location.href='perfil.php'">
        </div>
    </header>

    <!-- Conteúdo principal -->
    <main class="editar-perfil-container">
        <h2>Editar Perfil</h2>
        <?php
        session_start();
        include 'conexao.php';

        $id = $_SESSION['id'];

        // Busca os dados do usuário
        $sql = "SELECT nome_usuario FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();
        ?>

        <form action="atualizar_perfil.php" method="POST" class="editar-perfil-form">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

           

            <label for="nome_usuario">Nome de Usuário:</label>
            <input type="text" id="nome_usuario" name="nome_usuario" value="<?php echo $usuario['nome_usuario']; ?>" required>

            <label for="senha">Nova Senha (opcional):</label>
            <input type="password" id="senha" name="senha">

            <div class="botoes-container">
                <button type="submit" class="botao-estilizado salvar">Salvar Alterações</button>
                <a href="perfil.php" class="botao-estilizado cancelar">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>
