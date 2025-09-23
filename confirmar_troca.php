<?php
session_start();
include 'conexao.php';

if (isset($_GET['id_troca'])) {
    $id_troca = intval($_GET['id_troca']);
    $id_usuario = $_SESSION['id'];

    // Buscar a troca
    $sql = "SELECT * FROM trocas WHERE id = ? AND (id_solicitante = ? OR id_receptor = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $id_troca, $id_usuario, $id_usuario);
    $stmt->execute();
    $troca = $stmt->get_result()->fetch_assoc();

    if ($troca) {
        // Atualiza a confirmação de quem clicou
        if ($troca['id_solicitante'] == $id_usuario) {
            $sqlUpdate = "UPDATE trocas SET confirm_solicitante = 1 WHERE id = ?";
        } else {
            $sqlUpdate = "UPDATE trocas SET confirm_receptor = 1 WHERE id = ?";
        }

        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("i", $id_troca);
        $stmtUpdate->execute();

        // Verificar se ambos confirmaram
        $sqlCheck = "SELECT confirm_solicitante, confirm_receptor FROM trocas WHERE id = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("i", $id_troca);
        $stmtCheck->execute();
        $check = $stmtCheck->get_result()->fetch_assoc();

        if ($check['confirm_solicitante'] == 1 && $check['confirm_receptor'] == 1) {
            $sqlFinal = "UPDATE trocas SET status = 'Concluída' WHERE id = ?";
            $stmtFinal = $conn->prepare($sqlFinal);
            $stmtFinal->bind_param("i", $id_troca);
            $stmtFinal->execute();

            echo "<p>✅ Troca concluída por ambas as partes!</p>";
        } else {
            echo "<p>⚠️ Sua confirmação foi registrada. A troca será concluída quando o outro usuário confirmar.</p>";
        }

        echo "<a href='minhas_trocas.php'>Voltar</a>";

    } else {
        echo "<p>❌ Troca não encontrada ou você não participa dela.</p>";
    }
} else {
    echo "<p>⚠️ ID da troca não informado.</p>";
}
?>
