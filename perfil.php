<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="perfil.css">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <title>Perfil</title>
</head>
<body>
    <!-- Cabeçalho -->
    <header class="header">
        <a href="homepage.html"> <!-- Redireciona para a homepage -->
            <img src="imagens/logo-trocatrocajk.png" alt="Logo" class="logo">
        </a>
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Pesquise livros">
            <img src="imagens/icone-filtro.svg" alt="Filtrar" class="filter-icon" onclick="toggleFilter()">
        </div>
        <div class="icons">
            <img src="imagens/icone-publicarlivro.svg" alt="Publicar livro" onclick="abrirPopup()">
            <img src="imagens/icone-listadedesejo.svg" alt="Lista de desejos" onclick="window.location.href='listadedesejo.html'">
            <img src="imagens/icone-mensagem.svg" alt="Chat">
            <img src="imagens/icone-perfil.svg" alt="Perfil" onclick="window.location.href='perfil.php'">
        </div>
    </header>

    <!-- Página de Perfil -->
    <div class="perfil-container">
        <!-- Área do Perfil -->
        <div class="perfil-info">
            <img src="imagens/icone-perfil.svg" alt="Perfil" class="perfil-icon">
            <div class="perfil-details">

            <?php
            session_start();
            include 'conexao.php';

            

                // Busca os dados do usuário
                $sql = "SELECT email, nome_sobrenome, nome_usuario FROM usuarios WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $usuario = $result->fetch_assoc();
                $stmt->close();
                            
            echo"<h2>" . $_SESSION['nome_sobrenome'] . "</h2>";
            echo "<p>@" . $_SESSION['nome_usuario']. "</p>"
            ?>
            <a href="editar_perfil.php">
    <button>Editar Perfil</button>
    <a href="confirmar_exclusao.html">
    <button>Deletar Perfil</button>
    
</a>
                
                
            </div>
        </div>

        <!-- Livros Publicados -->
        <div class="livros-publicados">
            <h3>Livros Publicados</h3>
    </div>

    

    <script>
        // Função para abrir o pop-up (se necessário)
        function abrirPopup() {
            document.getElementById("popupOverlay").style.display = "flex";
        }

        // Função para fechar o pop-up
        function fecharPopup() {
            document.getElementById("popupOverlay").style.display = "none";
        }
    </script>
    
</body>
</html>
