<?php
session_start();
include 'conexao.php';

// Modifiquei a query para incluir a foto_perfil e id_usuario
$termoPesquisa = isset($_POST['q']) ? trim($_POST['q']) : '';

if (!empty($termoPesquisa)) {
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
    $sql = "SELECT l.*, u.nome_usuario, u.foto_perfil, u.id as id_dono 
            FROM livros l 
            LEFT JOIN usuarios u ON l.id_usuario = u.id 
            ORDER BY l.id DESC";
    $result = $conn->query($sql);
}

// Obtém a foto de perfil do usuário logado
if (isset($_SESSION['id'])) {
    $sql_user = "SELECT foto_perfil FROM usuarios WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $_SESSION['id']);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_data = $result_user->fetch_assoc();
    $foto_perfil_logado = $user_data['foto_perfil'] ?? null;
    $stmt_user->close();

    // Obtém livros favoritados
    $livros_desejados = [];
    $sql_desejos = "SELECT id_livro FROM lista_desejos WHERE id_usuario = ?";
    $stmt_desejos = $conn->prepare($sql_desejos);
    $stmt_desejos->bind_param("i", $_SESSION['id']);
    $stmt_desejos->execute();
    $result_desejos = $stmt_desejos->get_result();
    while ($row = $result_desejos->fetch_assoc()) {
        $livros_desejados[] = $row['id_livro'];
    }
    $stmt_desejos->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
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

/* Navigation arrows */
.nav-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
    z-index: 10;
}

.nav-arrow:hover {
    background: rgba(0, 0, 0, 0.7);
}

.nav-arrow.left {
    left: 10px;
}

.nav-arrow.right {
    right: 10px;
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
        <input type="text" name="q" class="search-bar" placeholder="Pesquise livros" value="<?php echo htmlspecialchars($termoPesquisa); ?>">
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
            <img src="<?= $foto_perfil_logado ? 'imagens/perfis/' . htmlspecialchars($foto_perfil_logado) : 'imagens/icone-perfil.svg' ?>" alt="perfil" />
        </div>
    </div>
    </header>

    <main id="livrosContainer">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($livro = $result->fetch_assoc()) {
            $usuario = !empty($livro['nome_usuario']) ? "@" . htmlspecialchars($livro['nome_usuario']) : "@usuarioTeste";
            $foto_perfil = !empty($livro['foto_perfil']) ? 'imagens/perfis/' . htmlspecialchars($livro['foto_perfil']) : 'imagens/icone-perfil.svg';
            $imagens = explode(",", $livro['imagens']);
            $primeiraImagem = $imagens[0];
            $imagensJson = json_encode(array_map(function($img) { 
                return 'uploads/' . $img; 
            }, $imagens));

            echo '<div class="card-livro" data-imagens=\'' . $imagensJson . '\'>';
                echo '<div class="card-header">';
                    echo '<div class="header-usuario">';
                        echo '<img src="' . $foto_perfil . '" class="perfil-icon" alt="Perfil">';
                        echo '<a class="user" href="perfil_usuario.php?id=' . $livro['id_dono'] . '">' . $usuario . '</a>';
                    echo '</div>';
                echo '</div>';
                
                echo '<div class="imagem-container">';
                    echo '<img src="uploads/' . htmlspecialchars($primeiraImagem) . '" class="imagem-livro" alt="Capa do Livro">';
                    // Exibe o botão somente se o livro NÃO pertence ao usuário logado
                    if (isset($_SESSION['id']) && $livro['id_dono'] != $_SESSION['id']) {
                      // Se estiver nos favoritos, adicione a classe "active"
                      $isFavorito = in_array($livro['id'], $livros_desejados) ? ' active' : '';
                      echo '<button class="wishlist-btn' . $isFavorito . '" data-livro-id="' . $livro['id'] . '">';
                        echo '<i class="heart-icon">♥</i>';
                      echo '</button>';
                    }
                echo '</div>';
                
                echo '<div class="info-livro">';
                    echo '<p class="titulo"><strong>Título:</strong> ' . htmlspecialchars($livro['titulo']) . '</p>';
                    echo '<p class="genero"><strong>Gênero:</strong> ' . htmlspecialchars($livro['genero']) . '</p>';
                    echo '<p class="autor"><strong>Autor:</strong> ' . htmlspecialchars($livro['autor']) . '</p>';
                    echo '<p class="estado"><strong>Estado:</strong> ' . htmlspecialchars($livro['estado']) . '</p>';
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
            <form action="livro_crud.php" method="POST" enctype="multipart/form-data" onsubmit="return validarFormulario()">
                <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                    <p id="uploadText">Clique para adicionar imagens</p>
                    <input type="file" id="fileInput" name="imagens[]" multiple accept="image/*" style="display: none;" required>
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
        
        // Parse as imagens do atributo data-imagens
        const imagensArray = JSON.parse(cardElement.getAttribute('data-imagens'));
        
        // Pega o botão de favorito do card (se existir)
        const favoritoBtn = cardElement.querySelector('.wishlist-btn');
        const favoritoBtnHtml = favoritoBtn ? `
        <button class="wishlist-btn ${favoritoBtn.classList.contains('active') ? 'active' : ''}" 
                data-livro-id="${favoritoBtn.dataset.livroId}"
                onclick="event.stopPropagation(); toggleFavorito(this)">
            <i class="heart-icon">♥</i>
        </button>
    ` : '';

        // Initialize current images
        window.currentImages = imagensArray;
        window.currentImageIndex = 0;

        modalContent.innerHTML = `
            <div class="modal-header">
                <div class="header-usuario">
                    <img src="${cardElement.querySelector(".perfil-icon").src}" class="modal-perfil" alt="Perfil">
                    <span class="user">${cardElement.querySelector(".user").innerText}</span>
                </div>
            </div>
            <div class="modal-img-container">
                <img class="modal-img" src="${imagensArray[0]}" alt="Imagem do Livro" id="modalImage">
                ${favoritoBtnHtml}
                ${imagensArray.length > 1 ? `
                    <div class="nav-arrow left" onclick="previousImage(event)">
                        <span class="nav-text">&lt;</span>
                    </div>
                    <div class="nav-arrow right" onclick="nextImage(event)">
                        <span class="nav-text">&gt;</span>
                    </div>
                    <div class="image-counter">
                        <span id="imageCounter">1 / ${imagensArray.length}</span>
                    </div>
                ` : ''}
            </div>
            <div class="modal-info">
                <p>${cardElement.querySelector(".titulo").innerText}</p>
                <p>${cardElement.querySelector(".genero").innerText}</p>
                <p>${cardElement.querySelector(".autor").innerText}</p>
                <p>${cardElement.querySelector(".estado").innerText}</p>
            </div>
        `;

        modal.style.display = "flex";
    }

    document.addEventListener('DOMContentLoaded', () => {
    // envia requisição para toggle e atualiza botão
    async function sendToggleRequest(livroId, button) {
        try {
            const resp = await fetch('toggle_favorito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_livro: livroId })
            });

            const text = await resp.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error('Resposta não JSON de toggle_favorito.php:', text);
                showToast('Resposta inválida do servidor');
                return;
            }

            if (data.success) {
                button.classList.toggle('active');
                showToast(data.message || (button.classList.contains('active') ? 'Adicionado à lista de desejos' : 'Removido da lista de desejos'));
            } else {
                console.warn('toggle_favorito retornou sucesso=false:', data);
                showToast(data.message || 'Erro ao atualizar lista de desejos');
            }
        } catch (err) {
            console.error('Erro na requisição toggle_favorito:', err);
            showToast('Erro de rede ao atualizar lista de desejos');
        }
    }

    // bind para todos os botões existentes
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const livroId = this.dataset.livroId;
            if (!livroId) {
                console.error('wishlist-btn sem data-livro-id', this);
                showToast('ID do livro não encontrado');
                return;
            }
            sendToggleRequest(livroId, this);
        });
    });

    // compatibilidade para onclick inline (se ainda existir onclick="toggleFavorito(this)")
    window.toggleFavorito = function (el) {
        if (!el || !el.dataset) return;
        const livroId = el.dataset.livroId;
        sendToggleRequest(livroId, el);
    };

    // toast simples — usa .toast e .toast.show do style.css
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        // forçar reflow para animação
        requestAnimationFrame(() => toast.classList.add('show'));
        // remover após 2.2s
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 2200);
    }
});

// Add these functions at the bottom of your script section
function nextImage(e) {
    e.stopPropagation();
    if (window.currentImages && window.currentImages.length > 1) {
        window.currentImageIndex = (window.currentImageIndex + 1) % window.currentImages.length;
        updateModalImage();
    }
}

function previousImage(e) {
    e.stopPropagation();
    if (window.currentImages && window.currentImages.length > 1) {
        window.currentImageIndex = (window.currentImageIndex - 1 + window.currentImages.length) % window.currentImages.length;
        updateModalImage();
    }   
}

function updateModalImage() {
    const modalImage = document.getElementById('modalImage');
    const imageCounter = document.getElementById('imageCounter');
    
    if (modalImage && window.currentImages.length > 0) {
        modalImage.src = window.currentImages[window.currentImageIndex];
        if (imageCounter) {
            imageCounter.textContent = `${window.currentImageIndex + 1} / ${window.currentImages.length}`;
        }
    }
}

// Função para validar formulário antes do envio
function validarFormulario() {
    const fileInput = document.getElementById('fileInput');
    const uploadText = document.getElementById('uploadText');

    // Verifica se pelo menos um arquivo foi selecionado
    if (fileInput.files.length === 0) {
        uploadText.textContent = 'Por favor, adicione pelo menos uma imagem.';
        uploadText.style.color = 'red';
        return false; // Impede o envio do formulário
    }

    // Se tudo estiver ok, permite o envio do formulário
    return true;
}
</script>
</body>
</html>