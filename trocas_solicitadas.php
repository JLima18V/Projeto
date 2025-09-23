<?php
session_start();
include 'conexao.php';


$id_usuario = $_SESSION['id']; // usuário logado (receptor da troca)

// 1️⃣ Buscar todas as trocas pendentes onde o usuário é receptor
$sql = "SELECT t.id AS troca_id, t.id_solicitante, t.id_livro_solicitado, u.nome AS nome_solicitante, l.titulo AS titulo_solicitado
        FROM trocas t
        JOIN usuarios u ON t.id_solicitante = u.id
        JOIN livros l ON t.id_livro_solicitado = l.id
        WHERE t.id_receptor = ? AND t.status = 'pendente'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows > 0) {
    while ($troca = $result->fetch_assoc()) {
        $troca_id = $troca['troca_id'];

        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px;'>";
        echo "<p><strong>Solicitante:</strong> " . htmlspecialchars($troca['nome_solicitante']) . "</p>";
        echo "<p><strong>Livro solicitado:</strong> " . htmlspecialchars($troca['titulo_solicitado']) . "</p>";

        // 2️⃣ Buscar os livros oferecidos para esta troca
        $sql_oferecidos = "SELECT l.titulo 
                           FROM trocas_livros_oferecidos tlo
                           JOIN livros l ON tlo.id_livro_oferecido = l.id
                           WHERE tlo.id_troca = ?";
        $stmt_oferecidos = $conn->prepare($sql_oferecidos);
        $stmt_oferecidos->bind_param("i", $troca_id);
        $stmt_oferecidos->execute();
        $result_oferecidos = $stmt_oferecidos->get_result();

        echo "<p><strong>Livros oferecidos:</strong></p><ul>";
        while ($livro = $result_oferecidos->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($livro['titulo']) . "</li>";
        }
        echo "</ul>";
        $stmt_oferecidos->close();

        // 3️⃣ Botões para aceitar ou recusar
        echo "<form method='POST' action='processa_resposta_troca.php'>";
        echo "<input type='hidden' name='id_troca' value='$troca_id'>";
        echo "<button type='submit' name='resposta' value='aceita'>Aceitar</button> ";
        echo "<button type='submit' name='resposta' value='recusada'>Recusar</button>";
        echo "</form>";
        echo "</div>";
    }
} else {
    echo "<h2>Nenhuma solicitação de troca pendente.</h2>";
}

$stmt->close();
?>
