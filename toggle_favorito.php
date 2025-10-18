<?php
session_start();
include 'conexao.php';
include 'verifica_login.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_livro = $data['id_livro'] ?? null;

if (!$id_livro) {
    echo json_encode(['success' => false, 'message' => 'ID do livro não fornecido']);
    exit;
}

$id_usuario = $_SESSION['id'];

try {
    // Check if already exists
    $check_sql = "SELECT id FROM lista_desejos WHERE id_usuario = ? AND id_livro = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $id_usuario, $id_livro);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Remove from wishlist
        $sql = "DELETE FROM lista_desejos WHERE id_usuario = ? AND id_livro = ?";
    } else {
        // Add to wishlist
        $sql = "INSERT INTO lista_desejos (id_usuario, id_livro) VALUES (?, ?)";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_usuario, $id_livro);
    $success = $stmt->execute();

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Lista de desejos atualizada com sucesso' : 'Erro ao atualizar lista de desejos'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
}

$conn->close();