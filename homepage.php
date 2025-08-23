<?php
session_start();
include 'conexao.php';

// Modifiquei a query para incluir a foto_perfil e id_usuario
$termoPesquisa = isset($_POST['q']) ? trim($_POST['q']) : '';

if (!empty($termoPesquisa)) {
    // Pesquisa filtrada
    $sql = "SELECT l.*, u.nome_usuario, u.foto_perfil, u.id as id_dono 
            FROM livros l 
            LEFT JOIN usuarios u ON l.id_usuario = u.id 
           WHERE l.titulo LIKE ? OR l.autor LIKE ? OR l.genero LIKE ?
            ORDER BY l.id DESC";
    $stmt = $conn->prepare($sql);
    $likeTerm = "%{$termoPesquisa}%";
$stmt->bind_param("sss", $likeTerm, $likeTerm, $likeTerm);

    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Mostra todos os livros
    $sql = "SELECT l.*, u.nome_usuario, u.foto_perfil, u.id as id_dono 
            FROM livros l 
            LEFT JOIN usuarios u ON l.id_usuario = u.id 
            ORDER BY l.id DESC";
    $result = $conn->query($sql);
}


// Obtém a foto de perfil do usuário logado
$sql_user = "SELECT foto_perfil FROM usuarios WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $_SESSION['id']);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$foto_perfil_logado = $user_data['foto_perfil'] ?? null;
$stmt_user->close();
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

        .header-usuario {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.perfil-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
}

.user {
    text-decoration: none;
    color: inherit;
    font-weight: bold;
}

.user:hover {
    text-decoration: underline;
}

.modal-content {
    padding: 20px;
    background: white;
    border-radius: 10px;
    max-width: 500px;
    width: 90%;
}

.modal-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.modal-perfil {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.modal-img {
    max-width: 100%;
    border-radius: 8px;
    margin-bottom: 15px;
}

.modal-info {
    line-height: 1.6;
}

.fechar {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
}

    </style>
</head>
<body>
    <header class="header">
        <a href="homepage.php
        ">
            <img src="imagens/logo-trocatrocajk.png" alt="Logo" class="logo">
        </a>
        <div class="search-container">
    <form method="POST" action="homepage.php" style="display: flex; align-items: center;">
        <input type="text" name="q" class="search-bar" placeholder="Pesquise livros">
        <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer;">
            <img src="imagens/icone-filtro.svg" alt="Pesquisar" class="filter-icon">
        </button>
    </form>
</div>

        <div class="icons">
            <img src="imagens/icone-publicarlivro.svg" alt="Publicar livro" onclick="abrirPopup()">
            <img src="imagens/icone-listadedesejo.svg" alt="Lista de desejos" onclick="window.location.href='listadedesejo.php'">
            <img src="imagens/icone-mensagem.svg" alt="Chat">
            <div class="foto-perfil-container" onclick="window.location.href='perfil.php'">
                <img src="<?= $foto_perfil_logado ? 'imagens/perfis/' . htmlspecialchars($foto_perfil_logado) : 'imagens/icone-perfil.svg' ?>" 
                     alt="Perfil" 
                     class="perfil-icon" 
                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            </div>
        </div>
    </header>

    <main id="livrosContainer" style="display: flex; flex-wrap: wrap; gap: 20px; padding: 20px; margin-top: 90px;">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($livro = $result->fetch_assoc()) {
            $usuario = !empty($livro['nome_usuario']) ? "@" . htmlspecialchars($livro['nome_usuario']) : "@usuarioTeste";
            
            // Obtém a foto de perfil ou usa o ícone padrão
            $foto_perfil = !empty($livro['foto_perfil']) ? 
                'imagens/perfis/' . htmlspecialchars($livro['foto_perfil']) : 
                'imagens/icone-perfil.svg';
            
            // Obtém a primeira imagem do livro
            $imagens = explode(",", $livro['imagens']);
            $primeiraImagem = $imagens[0];
            
            echo '<div class="card-livro" data-id="' . htmlspecialchars($livro['id']) . '" data-usuario="' . htmlspecialchars($usuario) . '">';
            echo '<div class="header-usuario">';
            echo '<img src="' . $foto_perfil . '" class="perfil-icon" alt="Perfil" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">';
            
            $link_perfil = ($livro['id_usuario'] == $_SESSION['id']) ? 'perfil.php' : 'perfil_usuario.php?id=' . $livro['id_usuario'];
            echo '<a class="user" href="' . $link_perfil . '">' . $usuario . '</a>';
            
            echo '</div>';
            echo '<img src="uploads/' . htmlspecialchars($primeiraImagem) . '" class="imagem-livro" alt="Capa do Livro">';
            echo '<div class="info-livro">';
            echo '<p class="titulo"><strong>Título:</strong> ' . htmlspecialchars($livro['titulo']) . '</p>';
            echo '<p class="genero"><strong>Gênero:</strong> ' . htmlspecialchars($livro['genero']) . '</p>';
            echo '<p class="autor"><strong>Autor:</strong> ' . htmlspecialchars($livro['autor']) . '</p>';
            echo '<p class="estado"><strong>Estado:</strong> ' . htmlspecialchars($livro['estado']) . '</p>';
            echo '</div>';

            if ($livro['id_usuario'] != $_SESSION['id']) {
    echo '<form action="listadedesejo.php" method="POST">';
    echo '<input type="hidden" name="id_livro" value="' . htmlspecialchars($livro['id']) . '">';
    echo '<button type="submit" class="btn-card">Adicionar à lista de desejos</button>';
    echo '</form>';
}
            
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
                    <input list="genero" name="genero" placeholder="Gênero" required>
                    <datalist id="genero">
                        <option value="Romance"></option>
                        <option value="Terror"></option>
                        <option value="Suspense"></option>
                        <option value="Comédia"></option>
                        <option value="Comédia Romântica"></option>
                    </datalist>
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


 <!-- Modal para detalhes do livro atualizado -->
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
            card.addEventListener('click', function () {
                mostrarDetalhesLivro(this);
            });
        });

      
    // Função para mostrar detalhes do livro
    function mostrarDetalhesLivro(cardElement) {
        const modal = document.getElementById("modalLivro");
        const modalContent = document.getElementById("modalDetalhesLivro");

        // Extrai dados do card
        const usuario = cardElement.getAttribute("data-usuario");
        const imagemSrc = cardElement.querySelector(".imagem-livro").src;
        const tituloText = cardElement.querySelector(".titulo").innerText;
        const generoText = cardElement.querySelector(".genero").innerText;
        const autorText = cardElement.querySelector(".autor").innerText;
        const estadoText = cardElement.querySelector(".estado").innerText;
        
        // Obtém a foto de perfil do elemento clicado
        const fotoPerfilSrc = cardElement.querySelector(".perfil-icon").src;

        // Insere conteúdo estruturado com a foto de perfil
        modalContent.innerHTML = `
            <div class="header-usuario" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                <img src="${fotoPerfilSrc}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" alt="Perfil">
                <span class="user" style="font-weight: bold;">${usuario}</span>
            </div>
            <img class="modal-img" src="${imagemSrc}" alt="Capa do Livro" style="max-width: 100%; border-radius: 8px; margin-bottom: 15px;">
            <div class="modal-info" style="line-height: 1.6;">
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