<?php
// Conexão com o banco
$servername = "localhost";
$username = "root";
$password = "jk123456";
$database = "troca_trocaJK";

$conn = new mysqli($servername, $username, $password, $database);

// Verifica conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $autor = $conn->real_escape_string($_POST['autor']);
    $genero = $conn->real_escape_string($_POST['genero']);
    $estado = $conn->real_escape_string($_POST['estado']);

    // Processamento de múltiplas imagens
    $imagensSalvas = [];
    if (!empty($_FILES['imagens']['name'][0])) {
        $diretorio = "uploads/";
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        foreach ($_FILES['imagens']['tmp_name'] as $index => $tmpName) {
            $nomeOriginal = basename($_FILES['imagens']['name'][$index]);
            $extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
            $nomeUnico = uniqid("img_", true) . "." . $extensao;
            $caminhoCompleto = $diretorio . $nomeUnico;

            if (move_uploaded_file($tmpName, $caminhoCompleto)) {
                $imagensSalvas[] = $nomeUnico;
            }
        }
    }

    // Concatena os nomes das imagens
    $imagensString = implode(",", $imagensSalvas);

    // Insere no banco
    $sql = "INSERT INTO livros (titulo, autor, genero, estado, imagens) VALUES ('$titulo', '$autor', '$genero', '$estado', '$imagensString')";

    if ($conn->query($sql) === TRUE) {
        header("Location: homepage.html");
        exit();
    } else {
        echo "Erro ao inserir: " . $conn->error;
    }
} else {
    echo "Requisição inválida.";
}

$conn->close();
?>
