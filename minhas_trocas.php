<?php
session_start();
include 'conexao.php';

$id_usuario = $_SESSION['id'];

// Buscar todas as trocas em que o usuário é solicitante ou receptor
$sql = "SELECT t.id, t.status, t.confirm_solicitante, t.confirm_receptor,
               l.id AS id_livro, l.titulo, l.imagens, 
               u.nome_usuario AS outro_usuario,
               u.instagram, u.whatsapp,
               CASE 
                   WHEN t.id_solicitante = ? THEN 'solicitante'
                   ELSE 'receptor'
               END AS papel
        FROM trocas t
        JOIN livros l ON t.id_livro_solicitado = l.id
        JOIN usuarios u 
             ON (CASE 
                     WHEN t.id_solicitante = ? THEN t.id_receptor 
                     ELSE t.id_solicitante 
                 END) = u.id
        WHERE t.id_solicitante = ? OR t.id_receptor = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $id_usuario, $id_usuario, $id_usuario, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

include("headertrocas.html");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        // Fundo do card: amarelo se precisa de confirmação, branco caso contrário
        $bgColor = "white";
        if ($row['status'] === 'aceita') {
            if (($row['papel'] === 'solicitante' && $row['confirm_solicitante'] == 0) ||
                ($row['papel'] === 'receptor' && $row['confirm_receptor'] == 0)) {
                $bgColor = "#fff9c4"; // aguardando confirmação
            }
        }

        echo "<div style='border:1px solid #ccc; border-radius:8px; padding:10px; margin-bottom:15px; display:flex; align-items:flex-start; background-color:$bgColor;'>";

        // Imagem do livro (capa)
        $capa = !empty($row['imagens']) ? "uploads/" . $row['imagens'] : "imagens/icone-livro.svg";
        echo "<div style='margin-right:15px;'>";
        echo "<img src='$capa' alt='Capa do livro' style='width:80px; height:120px; object-fit:cover; border-radius:4px;'>";
        echo "</div>";

        // Conteúdo do card
        echo "<div style='flex:1;'>";
        if ($row['papel'] === 'solicitante') {
            echo "<p>   <b>Você solicitou este livro para:</b> " . htmlspecialchars($row['outro_usuario']) . "</p>";
        } else {
            echo "<p><b>Este livro foi solicitado por:</b> " . htmlspecialchars($row['outro_usuario']) . "</p>";
        }

        echo "<p><b>Título:</b> " . htmlspecialchars($row['titulo']) . "</p>";
        echo "<p><b>Status:</b> " . ucfirst($row['status']) . "</p>";

        // Contatos apenas se a troca foi aceita
        if ($row['status'] === 'aceita') {
            echo "<b>Entre já em contato:</b>";
            if (!empty($row['whatsapp'])) {
                $whats = preg_replace('/\D/', '', $row['whatsapp']);
                echo "<p><a href='https://wa.me/$whats' target='_blank'> " . '<img src="imagens/icone-whatsapp.svg" alt="WhatsApp" style="width: 24px; vertical-align: middle;">' . htmlspecialchars($row['whatsapp']) . "</a></p>";
            }
            
            if (!empty($row['instagram'])) {
                echo "<p><a href='https://instagram.com/" . htmlspecialchars($row['instagram']) . "' target='_blank'>". '<img src="imagens/icone-instagram.svg" alt="Instagram" style="width: 24px; vertical-align: middle;">' . htmlspecialchars($row['instagram']) . "</a></p>";
            }
        }

        // Botão / mensagem de confirmação
        if ($row['status'] === 'aceita') {
            if ($row['papel'] === 'solicitante') {
                if ($row['confirm_solicitante'] == 0) {
                    echo "<a href='confirmar_troca.php?id_troca=" . $row['id'] . "' style='padding:5px 10px; background-color:blue; color:white; border-radius:5px; text-decoration:none;'>Concluir troca</a>";
                    echo "(só conclua quando estiver com o(s) livro(s) em mãos!";
                } else {
                    echo "<p>✅ Você já confirmou. Aguardando o outro usuário.</p>";
                }
            } else {
                if ($row['confirm_receptor'] == 0) {
                    echo "<a href='confirmar_troca.php?id_troca=" . $row['id'] . "' style='padding:5px 10px; background-color:blue; color:white; border-radius:5px; text-decoration:none;'>Concluir troca</a>";
                    echo "(só conclua quando estiver com o(s) livro(s) em mãos!";
                } else {
                    echo "<p>✅ Você já confirmou. Aguardando o outro usuário.</p>";
                }
            }
        } elseif ($row['status'] === 'Concluída') {
            echo "<p>🎉 Troca concluída com sucesso!</p>";
        }

        echo "</div>"; // fim do conteúdo do card
        echo "</div>"; // fim do card
    }
} else {
    echo "<p>Você ainda não participou de nenhuma troca.</p>";
}

echo "</body></html>";
?>
