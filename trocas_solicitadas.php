<?php
session_start();
include 'conexao.php';
include 'verifica_login.php';

$id_usuario = $_SESSION['id'];

// Modificada a query para incluir mais informações
$sql = "SELECT t.id AS troca_id, t.id_solicitante, t.id_livro_solicitado, 
               u.nome AS nome_solicitante, u.nome_usuario, u.foto_perfil,
               l.titulo AS titulo_solicitado, l.autor, l.genero, l.estado, l.imagens
        FROM trocas t
        JOIN usuarios u ON t.id_solicitante = u.id
        JOIN livros l ON t.id_livro_solicitado = l.id
        WHERE t.id_receptor = ? AND t.status = 'pendente'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trocas Pendentes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="trocas_solicitadas.css">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
</head>
<body>
    <header class="header-trocas">
        <img src="imagens/icone-voltar.png" alt="Voltar" class="voltar-icon" onclick="window.location.href='homepage.php'">
        <h1>Trocas Pendentes</h1>
    </header>

    <div class="trocas-container">
        <?php
        if ($result->num_rows > 0) {
            while ($troca = $result->fetch_assoc()) {
                $troca_id = $troca['troca_id'];
                $foto_perfil = !empty($troca['foto_perfil']) ? 'imagens/perfis/' . htmlspecialchars($troca['foto_perfil']) : 'imagens/icone-perfil.svg';
                $imagens_livro = explode(',', $troca['imagens']);
                $primeira_imagem = !empty($imagens_livro[0]) ? 'uploads/' . $imagens_livro[0] : 'imagens/sem-imagem.jpg';

                echo '<div class="troca-card">';
                    // Informações do solicitante com link para perfil
                    echo '<div class="solicitante-info">';
                        echo '<a href="perfil_usuario.php?id=' . $troca['id_solicitante'] . '" class="user-link">';
                            echo '<img src="' . $foto_perfil . '" class="solicitante-foto" alt="Foto do Solicitante">';
                            echo '<div class="solicitante-dados">';
                                echo '<span class="solicitante-nome">' . htmlspecialchars($troca['nome_solicitante']) . '</span>';
                                echo '<span class="solicitante-usuario">@' . htmlspecialchars($troca['nome_usuario']) . '</span>';
                            echo '</div>';
                        echo '</a>';
                    echo '</div>';
                    
                    echo '<div class="troca-detalhes">';
                        // Livro solicitado com imagem e detalhes
                        echo '<div class="livro-info livro-solicitado">';
                            echo '<h3>Livro Solicitado:</h3>';
                            echo '<div class="livro-card">';
                                echo '<img src="' . $primeira_imagem . '" class="livro-imagem" alt="Capa do Livro">';
                                echo '<div class="livro-dados">';
                                    echo '<p class="titulo"><strong>Título:</strong> ' . htmlspecialchars($troca['titulo_solicitado']) . '</p>';
                                    echo '<p class="autor"><strong>Autor:</strong> ' . htmlspecialchars($troca['autor']) . '</p>';
                                    echo '<p class="genero"><strong>Gênero:</strong> ' . htmlspecialchars($troca['genero']) . '</p>';
                                    echo '<p class="estado"><strong>Estado:</strong> ' . htmlspecialchars($troca['estado']) . '</p>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                        
                        // Livros oferecidos
                        echo '<div class="livro-info livros-oferecidos">';
                            echo '<h3>Livros Oferecidos:</h3>';
                            echo '<ul class="lista-oferecidos">';
                            $sql_oferecidos = "SELECT l.titulo, l.autor, l.genero, l.estado, l.imagens
                                             FROM trocas_livros_oferecidos tlo
                                             JOIN livros l ON tlo.id_livro_oferecido = l.id
                                             WHERE tlo.id_troca = ?";
                            $stmt_oferecidos = $conn->prepare($sql_oferecidos);
                            $stmt_oferecidos->bind_param("i", $troca_id);
                            $stmt_oferecidos->execute();
                            $result_oferecidos = $stmt_oferecidos->get_result();
                            
                            while ($livro = $result_oferecidos->fetch_assoc()) {
                                $imagens_oferecido = explode(',', $livro['imagens']);
                                $imagem_oferecido = !empty($imagens_oferecido[0]) ? 'uploads/' . $imagens_oferecido[0] : 'imagens/sem-imagem.jpg';
                                
                                echo '<li class="livro-oferecido">';
                                    echo '<img src="' . $imagem_oferecido . '" class="livro-imagem-mini" alt="Capa do Livro">';
                                    echo '<div class="livro-dados-mini">';
                                        echo '<p class="titulo"><strong>Título:</strong> ' . htmlspecialchars($livro['titulo']) . '</p>';
                                        echo '<p class="autor"><strong>Autor:</strong> ' . htmlspecialchars($livro['autor']) . '</p>';
                                        echo '<p class="genero"><strong>Gênero:</strong> ' . htmlspecialchars($livro['genero']) . '</p>';
                                        echo '<p class="estado"><strong>Estado:</strong> ' . htmlspecialchars($livro['estado']) . '</p>';
                                    echo '</div>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        echo '</div>';
                    echo '</div>';

                    echo '<form method="POST" action="processa_resposta_troca.php" class="troca-acoes">';
                        echo '<input type="hidden" name="id_troca" value="' . $troca_id . '">';
                        echo '<button type="submit" name="resposta" value="aceita" class="btn-aceitar">Aceitar</button>';
                        echo '<button type="submit" name="resposta" value="recusada" class="btn-recusar">Recusar</button>';
                    echo '</form>';
                echo '</div>';
            }
        } else {
            echo '<div class="sem-trocas">';
                echo '<h2>Nenhuma solicitação de troca pendente.</h2>';

            echo '</div>';
        }

        $stmt->close();
        ?>
    </div>
</body>
</html>