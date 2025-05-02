<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <style>
        /* Estilos do pop-up */
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

<!-- Área de Livros -->
<div style="display: flex; flex-wrap: wrap; gap: 20px; padding: 20px;">
<?php

include 'conexao.php';

// Buscando livros
$result = $conn->query("SELECT * FROM livros");
while($livro = $result->fetch_assoc()) {
    
    echo "<div style='border: 1px solid #ccc; padding: 10px; border-radius: 10px; width: 200px;'>";
    echo "<img src='uploads/" . htmlspecialchars($livro['imagens']) . "' alt='Capa' style='width: 150px; height: 200px; object-fit: cover;  '>";
    echo "<h3>" . htmlspecialchars($livro['titulo']) . "</h3>";
    echo "<p>Autor: " . htmlspecialchars($livro['autor']) . "</p>";
    echo "<p>Gênero: " . htmlspecialchars($livro['genero']) . "</p>";
    echo "<p>Estado: " . htmlspecialchars($livro['estado']) . "</p>";
    echo "</div>";
}
$conn->close();
?>
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
