<?php
session_start();
include 'conexao.php';
include 'verifica_login.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    die("Acesso negado.");
}

// Recupera dados do formulário
$id_usuario = $_SESSION['id'];
$id_livro_solicitado = isset($_POST['id_livro_solicitado']) ? intval($_POST['id_livro_solicitado']) : null;
$livros_oferecidos = isset($_POST['livros_oferecidos']) ? $_POST['livros_oferecidos'] : [];

// Valida entrada
if (!$id_livro_solicitado || empty($livros_oferecidos)) {
    die("Selecione pelo menos um livro para oferecer.");
}

// Converte array de livros oferecidos em string separada por vírgula
$livros_oferecidos_str = implode(',', array_map('intval', $livros_oferecidos));

// 1️⃣ Pegar o dono do livro solicitado
$sql_receptor = "SELECT id_usuario FROM livros WHERE id = ?";
$stmt_receptor = $conn->prepare($sql_receptor);
$stmt_receptor->bind_param("i", $id_livro_solicitado);
$stmt_receptor->execute();
$result = $stmt_receptor->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id_receptor = $row['id_usuario']; // dono do livro
} else {
    die("Livro não encontrado!");
}

$stmt_receptor->close();

// 2️⃣ Inserir a solicitação na tabela 'trocas'
$sql = "INSERT INTO trocas (id_solicitante, id_receptor, id_livro_solicitado, status, data_solicitacao) 
        VALUES (?, ?, ?, 'pendente', NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $id_usuario, $id_receptor, $id_livro_solicitado);


if ($stmt->execute()) {
    $id_troca = $conn->insert_id;

    // 3️⃣ Inserir os livros oferecidos
    if (!empty($livros_oferecidos)) {
        $values = [];
        $types = "";
        $params = [];

        foreach ($livros_oferecidos as $livro_id) {
            $values[] = "(?, ?)";
            $types .= "ii";
            $params[] = $id_troca;
            $params[] = $livro_id;
        }

        $sql_oferecidos = "INSERT INTO trocas_livros_oferecidos (id_troca, id_livro_oferecido) 
                           VALUES " . implode(", ", $values);
        $stmt_oferecido = $conn->prepare($sql_oferecidos);
        
        $stmt_oferecido->bind_param($types, ...$params);
        $stmt_oferecido->execute();
        $stmt_oferecido->close();
    }

    // ✅ ENVIAR EMAIL - CHAMANDO A FUNÇÃO
    include 'enviar_email_interesse.php';
    $emailEnviado = enviarEmailInteresse($conn, $id_livro_solicitado, $id_usuario);

    // Opcional: você pode verificar se o email foi enviado
    if (!$emailEnviado) {
        error_log("Aviso: Email não pôde ser enviado, mas a troca foi registrada");
    }

    $_SESSION['mensagem'] = "Troca solicitada com sucesso!";
    header('Location: perfil.php');
    exit;

} else {
    echo "Erro ao enviar solicitação: " . $stmt->error;
}
$stmt->close();
$conn->close();

?>
