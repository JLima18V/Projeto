<?php
session_start();
include 'conexao.php';

$id_usuario = $_SESSION['id']; // usuário logado (receptor da troca)

if (isset($_POST['id_troca'], $_POST['resposta'])) {
    $id_troca = intval($_POST['id_troca']);
    $resposta = $_POST['resposta'];

    // Validar que a resposta é aceitável
    if (!in_array($resposta, ['aceita', 'recusada'])) {
        die("Resposta inválida!");
    }

    

    // 1️⃣ Verificar se a troca realmente pertence ao usuário
    $sql_check = "SELECT * FROM trocas WHERE id = ? AND id_receptor = ? AND status = 'pendente'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_troca, $id_usuario);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        die("Troca não encontrada ou já respondida.");
    }
    $stmt_check->close();

    // 2️⃣ Atualizar o status da troca e registrar a data/hora
$sql_update = "UPDATE trocas SET status = ?, data_status = NOW() WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("si", $resposta, $id_troca);


    if ($resposta === 'aceita') {
       
    $sql = "UPDATE trocas SET data_aceita = NOW() WHERE id = ?";

    } elseif ($resposta === 'recusada') {

        $sql = "UPDATE trocas SET status = 'recusada', data_recusada = NOW() WHERE id = ?";
    }

    if ($stmt_update->execute()) {
        $stmt_update->close();

        // 3️⃣ Mensagem de sucesso e redirecionamento
        echo "<script>
                // alert('Resposta da troca enviada com sucesso!');
                window.location.href = 'trocas_solicitadas.php'; // página de solicitações recebidas
              </script>";
        exit();
    } else {
        echo "Erro ao atualizar a troca: " . $stmt_update->error;
    }
} else {
    die("Dados incompletos.");
}
?>
