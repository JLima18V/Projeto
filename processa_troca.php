<?php
session_start();
include 'conexao.php';

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
$sql = "INSERT INTO trocas (id_solicitante, id_receptor, id_livro_solicitado, status) 
        VALUES (?, ?, ?, 'pendente')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $id_usuario, $id_receptor, $id_livro_solicitado);

if ($stmt->execute()) {
    $id_troca = $conn->insert_id; // pega o ID da troca recém-criada

    // 3️⃣ Inserir os livros oferecidos na tabela 'trocas_livros_oferecidos' de forma otimizada
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
        
        // Usando call_user_func_array para bind_param dinâmico
        $stmt_oferecido->bind_param($types, ...$params);
        $stmt_oferecido->execute();
        $stmt_oferecido->close();
    }

    
    // Redireciona de volta para a homepage ou perfil
    // Depois de executar a troca com sucesso
header("Location: perfil.php?status=sucesso");
exit();

} else {
    echo "Erro ao enviar solicitação: " . $stmt->error;
}
$stmt->close();
$conn->close();

?>
