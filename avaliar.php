<?php
session_start();
include 'conexao.php';


if (!isset($_SESSION['id'])) {
    die("Acesso negado.");
}

$id_usuario = $_SESSION['id'];
$id_troca = isset($_GET['id_troca']) ? intval($_GET['id_troca']) : 0;

// Buscar dados da troca pra saber quem é o outro usuário
$sql = "SELECT 
            t.id_solicitante, t.id_receptor, 
            u1.nome_usuario AS nome_solicitante, 
            u2.nome_usuario AS nome_receptor
        FROM trocas t
        JOIN usuarios u1 ON t.id_solicitante = u1.id
        JOIN usuarios u2 ON t.id_receptor = u2.id
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_troca);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Troca não encontrada.");
}

$troca = $result->fetch_assoc();

$id_avaliado = ($id_usuario == $troca['id_solicitante']) ? $troca['id_receptor'] : $troca['id_solicitante'];
$nome_avaliado = ($id_usuario == $troca['id_solicitante']) ? $troca['nome_receptor'] : $troca['nome_solicitante'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nota = intval($_POST['nota']);
    $comentario = trim($_POST['comentario']);

    $sqlInsert = "INSERT INTO avaliacoes (id_troca, id_avaliador, id_avaliado, nota, comentario) 
                  VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("iiiis", $id_troca, $id_usuario, $id_avaliado, $nota, $comentario);

    if ($stmtInsert->execute()) {
        header("Location: minhas_trocas.php");
        exit;
    } else {
        echo "Erro ao salvar avaliação: " . $stmtInsert->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Avaliar Troca</title>
    <link rel="stylesheet" href="avaliar.css">
</head>
<body>
    <div class="avaliar-container">
        <h2>Avaliar troca com @<?= htmlspecialchars($nome_avaliado) ?></h2>

        <form method="POST">
            <label>Nota:</label><br>
            <div class="estrelas">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="estrela<?= $i ?>" name="nota" value="<?= $i ?>" required>
                    <label for="estrela<?= $i ?>">⭐</label>
                <?php endfor; ?>
            </div>

            <label for="comentario">Comentário (opcional):</label><br>
            <textarea name="comentario" id="comentario" rows="4" placeholder="Conte como foi a troca..."></textarea><br>

            <button type="submit">Enviar Avaliação</button>
        </form>
    </div>
</body>
</html>
