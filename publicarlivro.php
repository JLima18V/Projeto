<<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Livro</title>
    <link rel="stylesheet" href="publicarlivro.css"> <!-- Conectando o CSS -->
</head>
<body>

    <!-- Botão para abrir o pop-up -->
    <button onclick="abrirPopup()">Adicionar Livro</button>

    <!-- Pop-up -->
    <div id="popupOverlay" class="popup-overlay">
        <div class="popup">
            <span class="fechar" onclick="fecharPopup()">×</span>
            <h2>Publicar Livro</h2>
            <input type="text" placeholder="Título">
            <input type="text" placeholder="Autor">
            <input type="text" placeholder="Gênero">
            <select>
                <option>Estado</option>
                <option>Novo</option>
                <option>Seminovo</option>
                <option>Usado</option>
            </select>
            <button>Postar</button>
        </div>
    </div>
    <script>
        function abrirPopup() {
            document.getElementById("popupOverlay").style.display = "flex";
        }
        
        function fecharPopup() {
            document.getElementById("popupOverlay").style.display = "none";
        }
    </script>
    
</body>
</html>
