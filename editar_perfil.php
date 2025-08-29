<?php
session_start();
include 'conexao.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// Busca os dados do usuário
$id = $_SESSION['id'];
$sql = "SELECT nome_usuario, instagram, whatsapp FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="edit_perfil.css">
    <link rel="stylesheet" href="style.css">
    <title>Editar Conta</title>
</head>
<body>
    <!-- Cabeçalho -->
    <header class="header">
        <a href="homepage.php">
            <img src="imagens/logo-trocatrocajk.png" alt="Logo" class="logo">
        </a>
        <div class="search-container">
            <form action="pesquisa.php" method="GET">
                <input type="text" name="q" class="search-bar" placeholder="Pesquise livros">
                <img src="imagens/icone-filtro.svg" alt="Filtrar" class="filter-icon">
            </form>
        </div>
        <div class="icons">
            <img src="imagens/icone-publicarlivro.svg" alt="Publicar livro" onclick="window.location.href='publicar.php'">
            <img src="imagens/icone-listadedesejo.svg" alt="Lista de desejos" onclick="window.location.href='listadedesejo.php'">
            <img src="imagens/icone-mensagem.svg" alt="Chat">
            <div class="profile-dropdown">
                <img src="<?php echo isset($_SESSION['foto_perfil']) ? 'imagens/perfis/' . $_SESSION['foto_perfil'] : 'imagens/icone-perfil.svg'; ?>" 
                     alt="Perfil" 
                     class="perfil-icon">
                <div class="profile-dropdown-content">
                    <a href="perfil.php">Meu Perfil</a>
                    <a href="editar_perfil.php">Editar Perfil</a>
                    <a href="logout.php">Sair</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo principal -->
    <main class="editar-perfil-container">
        <h2>Editar Perfil</h2>

        <form action="atualizar_perfil.php" method="POST" class="editar-perfil-form">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

           

            <label for="nome_usuario">Nome de Usuário:</label>
            <input type="text" id="nome_usuario" name="nome_usuario" value="<?php echo $usuario['nome_usuario']; ?>" required>

             <!-- <label for="senha">Nova Senha (opcional):</label>   
            <input type="password" id="senha" name="senha"> -->

            <label for="instagram">Instagram:</label>
            <input type="text" name="instagram" id="instagram" value="<?= htmlspecialchars($usuario['instagram']) ?>">

            <label for="whatsapp">WhatsApp:</label>
            <input type="text" name="whatsapp" id="whatsapp" value="<?= htmlspecialchars($usuario['whatsapp']) ?>">

            <div class="botoes-container">
                <button type="submit" class="botao-estilizado salvar">Salvar Alterações</button>
                <a href="perfil.php" class="botao-estilizado cancelar">Cancelar</a>
            </div>
        </form>

        <!-- Separador -->
        <div class="separador"></div>

        <!-- Botão de deletar conta -->
        <a href="confirmar_exclusao.html" class="botao-deletar-conta">
            Deletar Conta
        </a>
    </main>
</body>
</html>
