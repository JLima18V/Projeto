<?php
include 'conexao.php';

if (!isset($_GET['id'])) {
    echo "ID do usuário não informado.";
    exit;
}

$id_usuario = intval($_GET['id']);

// Buscar avaliações + dados do avaliador + imagens
$sql = "
    SELECT 
        a.id AS id_avaliacao,
        a.nota,
        a.comentario,
        a.data_avaliacao,
        u.nome_usuario,
        u.foto_perfil,
        (
            SELECT GROUP_CONCAT(ai.caminho_imagem SEPARATOR ',')
            FROM avaliacoes_imagens ai
            WHERE ai.id_avaliacao = a.id
        ) AS imagens
    FROM avaliacoes a
    JOIN usuarios u ON a.id_avaliador = u.id
    WHERE a.id_avaliado = ?
    ORDER BY a.data_avaliacao DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Esse usuário ainda não recebeu avaliações.</p>";
    exit;
}

while ($row = $result->fetch_assoc()):
    $imagens = !empty($row['imagens']) ? explode(',', $row['imagens']) : [];
?>
    <div class="avaliacao-item">
        <div style="display:flex; align-items:center; gap:10px;">
            <img src="<?= $row['foto_perfil'] ? 'imagens/perfis/' . htmlspecialchars($row['foto_perfil']) : 'imagens/icone-perfil.svg' ?>" 
                 alt="Foto do avaliador" 
                 style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
            <strong>@<?= htmlspecialchars($row['nome_usuario']) ?></strong>
        </div>
        <p style="margin:5px 0;">⭐ <?= intval($row['nota']) ?> / 5</p>
        <?php if (!empty($row['comentario'])): ?>
            <p style="font-size:14px; color:#555;"><?= nl2br(htmlspecialchars($row['comentario'])) ?></p>
        <?php endif; ?>
        <?php if (!empty($imagens)): ?>
            <div style="display:flex; gap:5px; flex-wrap:wrap; margin-top:6px;">
                <?php foreach ($imagens as $img): ?>
                    <img src="uploads/avaliacoes/<?= htmlspecialchars($img) ?>" 
                         alt="Imagem da avaliação" 
                         style="width:80px; height:80px; object-fit:cover; border-radius:6px;">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <small style="color:#999;"><?= date("d/m/Y H:i", strtotime($row['data_avaliacao'])) ?></small>
    </div>
<?php
endwhile;
$stmt->close();
?>
