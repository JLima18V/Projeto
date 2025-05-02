<?php
session_start();
include 'conexao.php';

// Recuperar dados do usuário
$sql = "SELECT email, nome_sobrenome, nome_usuario, foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Recupera os livros publicados pelo usuário
$sql_livros = "SELECT id, titulo, autor, data_publicacao, imagens FROM livros WHERE id_usuario = ? ORDER BY data_publicacao DESC";

$stmt_livros = $conn->prepare($sql_livros);
$stmt_livros->bind_param("i", $_SESSION['id']);
$stmt_livros->execute();
$result_livros = $stmt_livros->get_result();
$stmt_livros->close();

// Verifica se o formulário foi enviado para atualizar a foto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['foto_perfil'])) {
    $foto = $_FILES['foto_perfil'];

    // Verifica se o arquivo foi enviado com sucesso
    if ($foto['error'] == 0) {
        $extensao = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $novo_nome = 'perfil_' . $_SESSION['id'] . '.' . $extensao;
        $diretorio = 'imagens/perfis/';

        // Verifica se a pasta existe, caso contrário, cria
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        // Move o arquivo para o diretório de perfil
        if (move_uploaded_file($foto['tmp_name'], $diretorio . $novo_nome)) {
            // Atualiza o caminho da foto no banco de dados
            $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $novo_nome, $_SESSION['id']);
            $stmt->execute();
            $stmt->close();
            $usuario['foto_perfil'] = $novo_nome; // Atualiza a foto no array de dados
        } else {
            echo "Erro ao enviar a foto!";
        }
    }
}
?>

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
        <a href="homepage.php">
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
            <!-- Exibe a foto de perfil -->
            <img src="<?= $usuario['foto_perfil'] ? 'imagens/perfis/' . $usuario['foto_perfil'] : 'imagens/icone-perfil.svg' ?>" alt="Perfil" class="perfil-icon">
            <div class="perfil-details">
                <h2><?= $_SESSION['nome_sobrenome'] ?></h2>
                <p>@<?= $_SESSION['nome_usuario'] ?></p>
                <a href="editar_perfil.php">
                    <button>Editar Perfil</button>
                </a>
                <a href="confirmar_exclusao.html">
                    <button>Deletar Perfil</button>
                </a>
            </div>
        </div>

        

        <!-- Livros Publicados -->
        <div class="livros-publicados">
            <h3>Livros Publicados</h3>
            <?php if ($result_livros->num_rows > 0): ?>
                <ul>
                    <?php while ($livro = $result_livros->fetch_assoc()): ?>
                        <li class="livro-item">
    <?php
        $imagens = explode(',', $livro['imagens']);
        $caminhoImagem = !empty($imagens[0]) ? 'uploads/' . $imagens[0] : 'imagens/sem-imagem.png';
    ?>
    <img src="<?= $caminhoImagem ?>" alt="Capa do livro" class="livro-capa" style="width: 100px; height: auto; border-radius: 5px; margin-bottom: 8px;">
    <div>
        <strong><?= htmlspecialchars($livro['titulo']) ?></strong><br>
        Autor: <?= htmlspecialchars($livro['autor']) ?><br>
        Publicado em: <?= date("d/m/Y", strtotime($livro['data_publicacao'])) ?>
    </div>
</li>

                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Você ainda não publicou nenhum livro.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pop-up para Publicar Livro -->
<div id="popupOverlay" class="popup-overlay">
    <div class="popup">
        <div class="popup-header">
            <span class="fechar" onclick="fecharPopup()">
                <img src="imagens/icone-voltar.png" alt="Fechar" class="fechar-imagem">
            </span>
            <h2>Publicar Livro</h2>
        </div>

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
</script>   

</body>
</html>
