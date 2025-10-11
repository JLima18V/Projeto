<?php
session_start();
include 'conexao.php';

$id_usuario = $_SESSION['id'];

// Buscar todas as trocas em que o usuário é solicitante ou receptor
$sql = "SELECT 
            t.id, 
            t.status, 
            t.confirm_solicitante, 
            t.confirm_receptor,
            t.data_solicitacao,
            t.data_status,
            l.id AS id_livro, 
            l.titulo, 
            l.imagens, 
            u.nome_usuario AS outro_usuario,
            u.instagram, 
            u.whatsapp,
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
               // Formatando as datas
                $dataSolicitacao = !empty($row['data_solicitacao']) ? date("d/m/Y \à\s H:i", strtotime($row['data_solicitacao'])) : '';
                $dataStatus = !empty($row['data_status']) ? date("d/m/Y \à\s H:i", strtotime($row['data_status'])) : '';

                if ($row['papel'] === 'solicitante') {
                    echo "<p><b>Você solicitou este livro para:</b> @" . htmlspecialchars($row['outro_usuario']);
                    if ($dataSolicitacao) echo " <small>(em $dataSolicitacao)</small>";
                    echo "</p>";
                } else {
                    echo "<p><b>Este livro foi solicitado por:</b> @" . htmlspecialchars($row['outro_usuario']);
                    if ($dataSolicitacao) echo " <small>(em $dataSolicitacao)</small>";
                    echo "</p>";
                }

                echo "<p><b>Título:</b> " . htmlspecialchars($row['titulo']) . "</p>";

                echo "<p><b>Status:</b> " . ucfirst($row['status']);
                if (in_array(strtolower($row['status']), ['aceita', 'recusada', 'concluída']) && $dataStatus) {
                    echo " <small>(em $dataStatus)</small>";
                }
                echo "</p>";

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
                } elseif (strtolower($row['status']) === 'concluída') {
                echo "<div class='confirmacao-msg'>Troca concluída com sucesso!</div>";

                                // Verificar se já avaliou
                $sqlCheckAvaliacao = "SELECT nota, comentario FROM avaliacoes WHERE id_troca = ? AND id_avaliador = ?";
                $stmtCheck = $conn->prepare($sqlCheckAvaliacao);
                $stmtCheck->bind_param("ii", $row['id'], $id_usuario);
                $stmtCheck->execute();
                $resultCheck = $stmtCheck->get_result();

                if ($resultCheck->num_rows == 0) {
                    // Descobrir quem é o outro usuário
                    $idAvaliador = $id_usuario;
                    $sqlOther = "SELECT id_solicitante, id_receptor FROM trocas WHERE id = ?";
                    $stmtOther = $conn->prepare($sqlOther);
                    $stmtOther->bind_param("i", $row['id']);
                    $stmtOther->execute();
                    $resOther = $stmtOther->get_result()->fetch_assoc();
                    $idAvaliado = ($idAvaliador == $resOther['id_solicitante']) ? $resOther['id_receptor'] : $resOther['id_solicitante'];

                    echo "
                        <div class='avaliacao-container' id='avaliacao-{$row['id']}'>
                            <button class='btn-avaliar' onclick='mostrarFormulario({$row['id']}, {$idAvaliado})'>
                                Avaliar usuário/troca
                            </button>
                        </div>
                    ";
                } else {
                    $avaliacao = $resultCheck->fetch_assoc();
                    echo "
                        <div class='avaliacao-container'>
                            <p><b>Sua avaliação:</b> " . str_repeat('⭐', intval($avaliacao['nota'])) . "</p>
                            <p>" . htmlspecialchars($avaliacao['comentario']) . "</p>
                        </div>
                    ";
                }
                $stmtCheck->close();

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

   function mostrarFormulario(idTroca, idAvaliado) {
    const container = document.getElementById('avaliacao-' + idTroca);
    container.innerHTML = `
        <form onsubmit="enviarAvaliacao(event, ${idTroca}, ${idAvaliado})" enctype="multipart/form-data">
            <label>Nota:</label><br>
            <div class="estrelas">
                ${[1,2,3,4,5].map(i => `
                    <input type='radio' id='estrela${i}-${idTroca}' name='nota' value='${i}' required>
                    <label for='estrela${i}-${idTroca}'>⭐</label>
                `).join('')}
            </div>

            <textarea name='comentario' placeholder='Deixe um comentário (opcional)...'></textarea><br>

            <label for="imagens-${idTroca}" class="label-upload">
                📸 Adicionar até 3 fotos (opcional)
            </label>
            <input 
                type="file" 
                id="imagens-${idTroca}" 
                name="imagens[]" 
                accept="image/*" 
                multiple 
                style="display:none"
                onchange="mostrarPreview(${idTroca}, this.files)"
            >

            <div id="preview-${idTroca}" class="preview-imagens"></div>

            <button type='submit' class='btn-avaliar'>Enviar Avaliação</button>
        </form>
    `;
}

// Mostra miniaturas das imagens escolhidas
function mostrarPreview(idTroca, files) {
    const previewContainer = document.getElementById('preview-' + idTroca);
    previewContainer.innerHTML = '';

    Array.from(files).slice(0, 3).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '60px';
            img.style.height = '60px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '8px';
            img.style.margin = '5px';
            previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

// função pra garantir que não passe de 3 imagens
function validarLimiteImagens(input, limite) {
    if (input.files.length > limite) {
        alert(`Você só pode enviar até ${limite} imagens.`);
        input.value = ""; // limpa o campo
    }
}


function enviarAvaliacao(e, idTroca, idAvaliado) {
    e.preventDefault();
    const form = e.target;
    const dados = new FormData(form);
    dados.append('id_troca', idTroca);
    dados.append('id_avaliado', idAvaliado);

    fetch('salvar_avaliacao.php', {
        method: 'POST',
        body: dados
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'sucesso') {
            form.parentElement.innerHTML = `
                <p><b>Sua avaliação:</b> ${'⭐'.repeat(dados.get('nota'))}</p>
                <p>${dados.get('comentario')}</p>
            `;
        } else {
            alert(res.mensagem || 'Erro ao salvar avaliação.');
        }
    })
    .catch(err => alert('Erro de conexão.'));
}

function enviarAvaliacao(event, idTroca, idAvaliado) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    // Adiciona campos obrigatórios
    formData.append('id_troca', idTroca);
    formData.append('id_avaliado', idAvaliado);

    fetch('salvar_avaliacao.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error("Erro ao enviar avaliação.");
        return response.text();
    })
    .then(data => {
        // Substitui o formulário por mensagem de sucesso
        const container = document.getElementById('avaliacao-' + idTroca);
        container.innerHTML = `
            <div class="avaliacao-sucesso">
                <p>✅ Avaliação enviada com sucesso!</p>
            </div>
        `;
    })
    .catch(error => {
        alert("Erro: " + error.message);
    });
}
    </script>
</body>
</html>