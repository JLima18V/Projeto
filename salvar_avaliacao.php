<?php
session_start();
include 'conexao.php';
include 'verifica_login.php';


if (!isset($_SESSION['id'])) {
    die("Acesso negado.");
}

$id_avaliador = $_SESSION['id'];
$id_troca = intval($_POST['id_troca']);
$id_avaliado = intval($_POST['id_avaliado']);
$nota = intval($_POST['nota']);
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

// 🔹 Validação básica
if ($nota < 1 || $nota > 5) {
    die("Nota inválida.");
}

// 🔹 Insere a avaliação
$sql = "INSERT INTO avaliacoes (id_troca, id_avaliador, id_avaliado, nota, comentario) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiis", $id_troca, $id_avaliador, $id_avaliado, $nota, $comentario);
$stmt->execute();

$id_avaliacao = $stmt->insert_id;
$stmt->close();

// 🔹 Cria diretório se não existir
$uploadDir = "uploads/avaliacoes/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 🔹 Processa até 3 imagens
if (!empty($_FILES['imagens']['name'][0])) {
    $total = count($_FILES['imagens']['name']);
    $total = min($total, 3); // limite de 3 imagens

    for ($i = 0; $i < $total; $i++) {
        $tmpName = $_FILES['imagens']['tmp_name'][$i];
        $name = basename($_FILES['imagens']['name'][$i]);
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $novoNome = uniqid("aval_") . "." . strtolower($ext);

        // Só aceita formatos comuns de imagem
        $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($ext), $permitidos)) {
            move_uploaded_file($tmpName, $uploadDir . $novoNome);

            // Insere o nome da imagem vinculada à avaliação
            $sqlImg = "INSERT INTO avaliacoes_imagens (id_avaliacao, caminho_imagem) VALUES (?, ?)";
            $stmtImg = $conn->prepare($sqlImg);
            $stmtImg->bind_param("is", $id_avaliacao, $novoNome);
             $stmtImg->execute();
            $stmtImg->close();
        }
    }
}

$conn->close();

// Retorna pra página anterior (ou AJAX, se for o caso)
header("Location: minhas_trocas.php?avaliado={$id_avaliado}&avaliacao_sucesso=true");
exit;
?>
