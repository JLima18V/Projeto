<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Livro</title>
    <link rel="stylesheet" href="publicarlivro.css"> <!-- Conectando o CSS -->
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
</head>
<body>

    <!-- Botão para abrir o pop-up -->
    <button onclick="abrirPopup()">Adicionar Livro</button>

    <!-- Pop-up -->
    <div id="popupOverlay" class="popup-overlay">
        <div class="popup">
            <span class="fechar" onclick="fecharPopup()">×</span>
            <h2>Publicar Livro</h2>
            
            <!-- Formulário para publicar o livro -->
            <form action="publicar_livro.php" method="POST">
                <input type="text" name="titulo" placeholder="Título" required>
                <input type="text" name="autor" placeholder="Autor" required>
                <input type="text" name="genero" placeholder="Gênero" required>
                
                <select name="estado" required>
                    <option value="">Estado</option>
                    <option value="Novo">Novo</option>
                    <option value="Seminovo">Seminovo</option>
                    <option value="Usado">Usado</option>
                </select>

                <button type="submit">Postar</button>
            </form>
        </div>
    </div>
    <?php
session_start();
include 'conexao.php';

// Verifica se os dados foram enviados pelo formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $genero = $_POST['genero'];
    $estado = $_POST['estado'];

    // Verifica se os dados não estão vazios
    if (!empty($titulo) && !empty($autor) && !empty($genero) && !empty($estado)) {
        // Prepara a query para inserir os dados no banco
        $sql = "INSERT INTO livros (id_usuario, titulo, autor, genero, estado, data_publicacao) 
                VALUES (?, ?, ?, ?, ?, NOW())";

        // Prepara e executa a query
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issss", $_SESSION['id'], $titulo, $autor, $genero, $estado);
            if ($stmt->execute()) {
                // Redireciona para a homepage após o sucesso
                header("Location: homepage.php");
                exit();
            } else {
                echo "Erro ao publicar o livro!";
            }
            $stmt->close();
        } else {
            echo "Erro na preparação da query!";
        }
    } else {
        echo "Preencha todos os campos!";
    }
}

?>


    <script>
        function abrirPopup() {
            document.getElementById("popupOverlay").style.display = "flex";
        }
       
        
    </script>
    

    

</body>
</html>
