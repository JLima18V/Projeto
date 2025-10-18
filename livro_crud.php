<?php
session_start(); // Adicionado para usar a sessão
include 'verifica_login.php';

// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "jk123456";
$database = "troca_trocaJK";

$conn = new mysqli($servername, $username, $password, $database);

// Verifica conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    die("Erro: Usuário não está logado.");
}
$id_usuario = $_SESSION['id'];

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Pega os dados do formulário
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $autor = $conn->real_escape_string($_POST['autor']);
    $genero = $conn->real_escape_string($_POST['genero']);
    $estado = $conn->real_escape_string($_POST['estado']);

    // Verifica se o ID do usuário é válido
    $sqlCheckUser = "SELECT id FROM usuarios WHERE id = '$id_usuario'";
    $result = $conn->query($sqlCheckUser);

    if ($result->num_rows === 0) {
        die("Erro: Usuário inválido.");
    }

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

    $imagensString = implode(",", $imagensSalvas);

    // Agora insere com o ID do usuário
    $sql = "INSERT INTO livros (titulo, autor, genero, estado, imagens, id_usuario) 
            VALUES ('$titulo', '$autor', '$genero', '$estado', '$imagensString', '$id_usuario')";

    if ($conn->query($sql) === TRUE) {
        header("Location: homepage.php");
        exit();
    } else {
        echo "Erro ao inserir no banco de dados: " . $conn->error;
    }
} else {
    echo "Requisição inválida.";
}

$conn->close();
?>
