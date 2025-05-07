<?php
session_start(); // Inicia a sessão para usar dados do usuário logado
include 'conexao.php';

// Primeiro, verifique a estrutura da sua tabela usuarios
$tabela_usuarios = $conn->query("DESCRIBE usuarios");
$colunas_usuarios = [];
while($coluna = $tabela_usuarios->fetch_assoc()) {
    $colunas_usuarios[] = $coluna['Field'];
}

// Determina qual campo usar para o nome de usuário
$campo_nome = in_array('nome', $colunas_usuarios) ? 'nome' : 
             (in_array('username', $colunas_usuarios) ? 'username' : 
             (in_array('user', $colunas_usuarios) ? 'user' : 'id'));

// Buscando livros com dados do usuário usando o campo correto
$query = "SELECT l.*, u.$campo_nome as nome_usuario FROM livros l 
          LEFT JOIN usuarios u ON l.id_usuario = u.id 
          ORDER BY l.id DESC";

$result = $conn->query($query);

// Resto do seu código permanece o mesmo
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Livro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <style>
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
            position: relative;
        }

        .popup-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .popup-header h2 {
            margin: 0;
        }

        .fechar-imagem {
            width: 24px;
            cursor: pointer;
        }

        .upload-area {
            background-color: #f0f0f0;
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .input-container {
            margin-bottom: 15px;
        }

        .input-container input,
        .input-container select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .postar {
            background-color: #5dbb63;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .preview-container img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid #ccc;
            transition: 0.2s;
        }

        .preview-container img:hover {
            transform: scale(1.05);
            border-color: red;
        }
    </style>
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

    <main id="livrosContainer" style="display: flex; flex-wrap: wrap; gap: 20px; padding: 20px; margin-top: 90px;">
        <!-- Aqui serão exibidos os livros do banco de dados -->
        <?php
        // Exibe os livros
        if ($result && $result->num_rows > 0) {
            while($livro = $result->fetch_assoc()) {
                // Se tiver username, use ele, senão use um padrão
                $usuario = !empty($livro['nome_usuario']) ? "@" . $livro['nome_usuario'] : "@usuarioTeste";
                
                // Obtém a primeira imagem se houver múltiplas separadas por vírgula
                $imagens = explode(",", $livro['imagens']);
                $primeiraImagem = $imagens[0];
                
                echo '<div class="card-livro" data-id="'.htmlspecialchars($livro['id']).'">';
                echo '<div class="header-usuario">';
                echo '<img src="imagens/icone-perfil.svg" class="perfil-icon" alt="Perfil">';
                echo '<span class="user">'.$usuario.'</span>';
                echo '</div>';
                echo '<img src="uploads/'.htmlspecialchars($primeiraImagem).'" class="imagem-livro" alt="Capa do Livro">';
                echo '<div class="info-livro">';
                echo '<p class="titulo"><strong>Título:</strong> '.htmlspecialchars($livro['titulo']).'</p>';
                echo '<p class="genero"><strong>Gênero:</strong> '.htmlspecialchars($livro['genero']).'</p>';
                echo '<p class="autor"><strong>Autor:</strong> '.htmlspecialchars($livro['autor']).'</p>';
                echo '<p class="estado"><strong>Estado:</strong> '.htmlspecialchars($livro['estado']).'</p>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>Nenhum livro encontrado.</p>';
        }
        $conn->close();
        ?>
    </main>

    <!-- Pop-up para publicar livro -->
    <div id="popupOverlay" class="popup-overlay">
        <div class="popup">
            <div class="popup-header">
                <span class="fechar" onclick="fecharPopup()">
                    <img src="imagens/icone-voltar.png" alt="Fechar" class="fechar-imagem">
                </span>
                <h2>Publicar Livro</h2>
            </div>

            <!-- Formulário - mantendo exatamente como no original para trabalhar com seu livro_crud.php -->
            <form action="livro_crud.php" method="POST" enctype="multipart/form-data">
                <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                    <p>Clique para adicionar imagens</p>
                    <input type="file" id="fileInput" name="imagens[]" multiple accept="image/*" style="display: none;">
                </div>
                <div id="previewContainer" class="preview-container"></div>

                <div class="input-container">
                    <input type="text" name="titulo" placeholder="Título" required>
                </div>
                <div class="input-container">
                    <input type="text" name="autor" placeholder="Autor" required>
                </div>
                <div class="input-container">
                    <input type="text" name="genero" placeholder="Gênero" required>
                </div>
                <div class="input-container">
                    <select name="estado" required>
                        <option value="">Estado</option>
                        <option value="Novo">Novo</option>
                        <option value="Seminovo">Seminovo</option>
                        <option value="Usado">Usado</option>
                    </select>
                </div>

                <div class="input-container">
                    <button type="submit" class="postar">Postar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para detalhes do livro -->
    <div id="modalLivro" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="fechar" onclick="fecharModal()">&times;</span>
            <div id="modalDetalhesLivro"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function abrirPopup() {
            document.getElementById("popupOverlay").style.display = "flex";
        }
    
        function fecharPopup() {
            document.getElementById("popupOverlay").style.display = "none";
        }
    
        const fileInput = document.getElementById('fileInput');
        const previewContainer = document.getElementById('previewContainer');
        let selectedFiles = [];
    
        fileInput.addEventListener('change', () => {
            const files = Array.from(fileInput.files);
            selectedFiles.push(...files);
            updatePreviews();
        });
    
        function updatePreviews() {
            previewContainer.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = () => {
                    const img = document.createElement('img');
                    img.src = reader.result;
                    img.title = "Clique para remover";
                    img.onclick = () => {
                        selectedFiles.splice(index, 1);
                        updatePreviews();
                    };
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
            updateFileInput();
        }
    
        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }

        function fecharModal() {
            document.getElementById("modalLivro").style.display = "none";
        }
    
        // Fechar modal ao clicar fora do conteúdo
        window.addEventListener("click", function (e) {
            const modal = document.getElementById("modalLivro");
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });

        // Adicionar evento de clique aos cards de livro existentes
        document.querySelectorAll('.card-livro').forEach(card => {
            card.addEventListener('click', function() {
                mostrarDetalhesLivro(this);
            });
        });

        // Função para mostrar detalhes do livro
        function mostrarDetalhesLivro(cardElement) {
            const modal = document.getElementById("modalLivro");
            const modalContent = document.getElementById("modalDetalhesLivro");

            // Extrai dados do card
            const usuario = cardElement.querySelector(".user").innerText;
            const imagemSrc = cardElement.querySelector(".imagem-livro").src;
            const tituloText = cardElement.querySelector(".titulo").innerText;
            const generoText = cardElement.querySelector(".genero").innerText;
            const autorText = cardElement.querySelector(".autor").innerText;
            const estadoText = cardElement.querySelector(".estado").innerText;

            // Insere conteúdo estruturado
            modalContent.innerHTML = `
                <div class="header-usuario">
                    <img src="imagens/icone-perfil.svg" class="perfil-icon" alt="Perfil">
                    <span class="user">${usuario}</span>
                </div>
                <img class="modal-img" src="${imagemSrc}" alt="Capa do Livro">
                <div class="modal-info">
                    <p>${tituloText}</p>
                    <p>${generoText}</p>
                    <p>${autorText}</p>
                    <p>${estadoText}</p>
                </div>
            `;

            modal.style.display = "flex";
        }
    </script>
</body>
</html>