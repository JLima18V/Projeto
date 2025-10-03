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
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Trocas</title>
    <link rel="stylesheet" href="minhas_trocas.css">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
</head>
<body>
    <header class="header-trocas">
        <img src="imagens/icone-voltar.png" alt="Voltar" class="voltar-icon" onclick="window.location.href='homepage.php'">
        <h1>Minhas Trocas</h1>
    </header>

    <div class="minhas-trocas-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $cardClass = "troca-card";
                if ($row['status'] === 'aceita') {
                    if (($row['papel'] === 'solicitante' && $row['confirm_solicitante'] == 0) ||
                        ($row['papel'] === 'receptor' && $row['confirm_receptor'] == 0)) {
                        $cardClass .= " aguardando";
                    }
                }

                echo "<div class='$cardClass'>";
                
                // Container da imagem do livro
                $capa = !empty($row['imagens']) ? "uploads/" . $row['imagens'] : "imagens/icone-livro.svg";
                echo "<div class='livro-imagem-container'>";
                echo "<img src='$capa' alt='Capa do livro' class='livro-imagem'>";
                echo "</div>";

                // Informações da troca
                echo "<div class='info-troca'>";
                if ($row['papel'] === 'solicitante') {
                    echo "<p><b>Você solicitou este livro para:</b> @" . htmlspecialchars($row['outro_usuario']) . "</p>";
                } else {
                    echo "<p><b>Este livro foi solicitado por:</b> @" . htmlspecialchars($row['outro_usuario']) . "</p>";
                }
                echo "<p><b>Título:</b> " . htmlspecialchars($row['titulo']) . "</p>";
                echo "<p><b>Status:</b> " . ucfirst($row['status']) . "</p>";
                echo "</div>";

                // Contatos
                if ($row['status'] === 'aceita') {
                    echo "<div class='contatos-troca'>";
                    echo "<p><b>Entre em contato:</b></p>";
                    if (!empty($row['whatsapp'])) {
                        $whats = preg_replace('/\D/', '', $row['whatsapp']);
                        echo "<a href='https://wa.me/$whats' target='_blank' class='contato-link'>";
                        echo "<img src='imagens/icone-whatsapp.svg' alt='WhatsApp'>";
                        echo htmlspecialchars($row['whatsapp']);
                        echo "</a>";
                    }
                    
                    if (!empty($row['instagram'])) {
                        echo "<a href='https://instagram.com/" . htmlspecialchars($row['instagram']) . "' target='_blank' class='contato-link'>";
                        echo "<img src='imagens/icone-instagram.svg' alt='Instagram'>";
                        echo "@" . htmlspecialchars($row['instagram']);
                        echo "</a>";
                    }
                    echo "</div>";

                    // Botão de conclusão ou mensagem de confirmação
                    if (($row['papel'] === 'solicitante' && $row['confirm_solicitante'] == 0) ||
                        ($row['papel'] === 'receptor' && $row['confirm_receptor'] == 0)) {
                        echo "<a href='confirmar_troca.php?id_troca=" . $row['id'] . "' class='btn-concluir'>Concluir troca</a>";
                        echo "<small style='display:block;text-align:center;margin-top:8px;color:#666;'>*Só conclua quando estiver com o(s) livro(s) em mãos!</small>";
                    } else {
                        echo "<div class='confirmacao-msg'>Você já confirmou. Aguardando o outro usuário.</div>";
                    }
                } elseif ($row['status'] === 'Concluída') {
                    echo "<div class='confirmacao-msg'>Troca concluída com sucesso!</div>";
                }

                echo "</div>"; // fim do card
            }
        } else {
            echo "<div class='sem-trocas'>";
            echo "<h2>Você ainda não participou de nenhuma troca.</h2>";
            echo "<p>Que tal começar a trocar seus livros agora?</p>";
            echo "</div>";
        }
        ?>
    </div>

    <div id="toastNotification" class="toast-notification">
        <span class="icon">⚠️</span>
        <span class="message">Sua confirmação foi registrada. A troca será concluída quando o outro usuário confirmar.</span>
    </div>

    <script>
        function showToast() {
            const toast = document.getElementById('toastNotification');
            toast.classList.add('show');
            
            // Hide toast after 5 seconds
            setTimeout(() => {
                toast.classList.remove('show');
            }, 5000);
        }

        // Show toast if there's a confirmation parameter in URL
        if (window.location.search.includes('confirmed=true')) {
            showToast();
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</body>
</html>