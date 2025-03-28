<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="publicarlivro.css"> 
    <title>Homepage</title>
</head>
<body>
    <!-- Cabeçalho -->
    <header class="header">
        <a href="atualizar.php"> <!-- Redireciona para a homepage -->
            <img src="imagens/logo-trocatrocajk.png" alt="Logo" class="logo">
        </a>
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Pesquise livros">
            <img src="imagens/icone-filtro.png" alt="Filtrar" class="filter-icon" onclick="toggleFilter()">
        </div>
        <div class="icons">
            <img src="imagens/icone-publicarlivro.png" alt="Publicar livro" onclick="abrirPopup()">
            <img src="imagens/icone-listadedesejo.png" alt="Lista de desejos" onclick="window.location.href='listadedesejo.php'">
            <img src="imagens/icone-mensagem.png" alt="Chat">
            <img src="imagens/icone-perfil.png" alt="Perfil" onclick="window.location.href='perfil.php'">
        </div>
    </header>

<!-- Pop-up -->
<div id="popupOverlay" class="popup-overlay">
    <div class="popup">
        <div class="popup-header">
            <!-- Botão de fechar (X) com imagem -->
            <span class="fechar" onclick="fecharPopup()">
                <img src="imagens/icone-voltar.png" alt="Fechar" class="fechar-imagem">
            </span>
            <h2>Publicar Livro</h2>
            <button class="postar">Postar</button>
        </div>

        <!-- Área para adicionar imagem (agora no topo) -->
        <div class="upload-area" onclick="document.getElementById('fileInput').click()">
            <p>Clique para adicionar imagem</p>
            <input type="file" id="fileInput" style="display: none;" accept="image/*">
        </div>

        <!-- Inputs do formulário -->
        <div class="input-container">
            <input type="text" placeholder="Título">
        </div>
        <div class="input-container">
            <input type="text" placeholder="Autor">
        </div>
        <div class="input-container">
            <input type="text" placeholder="Gênero">
        </div>
        <div class="input-container">
            <select>
                <option>Estado</option>
                <option>Novo</option>
                <option>Seminovo</option>
                <option>Usado</option>
            </select>
        </div>
    </div>
</div>



    <script>
        // Função para abrir o pop-up
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
