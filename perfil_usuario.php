<?php
include 'conexao.php';

if (!isset($_GET['id'])) {
    echo "ID do usuário não fornecido.";
    exit;
}

$id_usuario = intval($_GET['id']);

// Buscar dados do usuário
$sql = "SELECT nome, sobrenome, nome_usuario, foto_perfil, instagram, whatsapp FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    echo "Usuário não encontrado.";
    exit;
}

// Buscar média e quantidade de avaliações
$sql_avaliacoes = "SELECT AVG(nota) AS media, COUNT(*) AS total FROM avaliacoes WHERE id_avaliado = ?";
$stmt_aval = $conn->prepare($sql_avaliacoes);
$stmt_aval->bind_param("i", $id_usuario);
$stmt_aval->execute();
$result_aval = $stmt_aval->get_result();
$avaliacao = $result_aval->fetch_assoc();
$stmt_aval->close();

$media = $avaliacao['media'] ? number_format($avaliacao['media'], 1, ',', '') : 0;
$total = $avaliacao['total'];

// Buscar livros do usuário
$sql_livros = "SELECT titulo, autor, data_publicacao, imagens FROM livros WHERE id_usuario = ? ORDER BY data_publicacao DESC";
$stmt = $conn->prepare($sql_livros);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$livros = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?= htmlspecialchars($usuario['nome_usuario']) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="perfil.css">
    <style>
        .perfil-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }

        .perfil-social-links a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #333;
            margin: 4px 0;
        }

        .perfil-social-links img {
            width: 24px;
            height: 24px;
        }

        /* ⭐ Avaliação geral */
        .avaliacao-perfil {
            margin-top: 6px;
            font-size: 17px;
            color: #444;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .avaliacao-perfil strong {
            color: #f5a623;
            font-size: 19px;
        }

        .avaliacao-perfil span {
            color: #777;
            font-size: 15px;
        }

        /* 📦 Avaliações individuais */
        .avaliacoes-container {
            margin-top: 20px;
            background: #fff;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .avaliacao-card {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .avaliador-foto {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
        }

        .avaliador-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .avaliacao-nota {
            margin: 4px 0;
            color: #f5a623;
        }

        .avaliacao-comentario {
            font-style: italic;
            color: #555;
        }

        .avaliacao-fotos img {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            object-fit: cover;
            margin: 5px;
        }

        .ver-todas-btn {
            display: block;
            margin: 10px auto 0;
            padding: 8px 14px;
            background: #f0f0f0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .ver-todas-btn:hover {
            background: #ddd;
        }

        /* Popup */
        .popup-avaliacoes {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .popup-conteudo {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            width: 90%;
        }

        .popup-conteudo .fechar {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <header class="header">
        <a href="homepage.php">
            <img src="imagens/logo-trocatrocajk.png" alt="Logo" class="logo">
        </a>
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Pesquise livros">
            <img src="imagens/icone-filtro.svg" alt="Filtrar" class="filter-icon">
        </div>
        <div class="icons">
            <img src="imagens/icone-publicarlivro.svg" alt="Publicar livro" onclick="abrirPopup()">
            <img src="imagens/icone-listadedesejo.svg" alt="Lista de desejos" onclick="window.location.href='listadedesejo.php'">
            <img src="imagens/icone-mensagem.svg" alt="Trocas Solicitadas" onclick="window.location.href='trocas_solicitadas.php'">
            <div class="foto-perfil-container" onclick="window.location.href='perfil.php'">
                <img src="<?= $foto_perfil_logado ? 'imagens/perfis/' . htmlspecialchars($foto_perfil_logado) : 'imagens/icone-perfil.svg' ?>" 
                     alt="Perfil" 
                     class="perfil-icon" 
                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            </div>
        </div>
    </header>

    <!-- Conteúdo -->
    <div class="perfil-container">
        <div class="perfil-info">
            <img src="<?= $usuario['foto_perfil'] ? 'imagens/perfis/' . htmlspecialchars($usuario['foto_perfil']) : 'imagens/icone-perfil.svg' ?>" alt="Perfil" class="perfil-icon">
            <div class="perfil-details">
                <h2><?= htmlspecialchars($usuario['nome']) . ' ' . htmlspecialchars($usuario['sobrenome']) ?></h2>
                <p>@<?= htmlspecialchars($usuario['nome_usuario']) ?></p>

                <!-- ⭐ Avaliação geral -->
                <div class="avaliacao-perfil">
                    <?php if ($total > 0): ?>
                        <p>
                            ⭐ <strong><?= $media ?></strong> / 5 
                            <span>(<?= $total ?> <?= $total == 1 ? 'avaliação' : 'avaliações' ?>)</span>
                        </p>
                    <?php else: ?>
                        <p>⭐ Ainda sem avaliações</p>
                    <?php endif; ?>
                </div>

                <!-- Redes sociais -->
                <div class="perfil-social-links">
                    <?php if (!empty($usuario['instagram'])): ?>
                        <p>
                            <a href="https://instagram.com/<?= htmlspecialchars($usuario['instagram']) ?>" target="_blank">
                                <img src="imagens/icone-instagram.svg" alt="Instagram">
                                <span>@<?= htmlspecialchars($usuario['instagram']) ?></span>
                            </a>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($usuario['whatsapp'])): ?>
                        <p>
                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $usuario['whatsapp']) ?>" target="_blank">
                                <img src="imagens/icone-whatsapp.svg" alt="WhatsApp">
                                <span><?= htmlspecialchars($usuario['whatsapp']) ?></span>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 🧾 Avaliações Recentes -->
        <?php
        $sql_recent = "
            SELECT a.id, a.nota, a.comentario, a.data_avaliacao, u.nome_usuario, u.foto_perfil
            FROM avaliacoes a
            JOIN usuarios u ON a.id_avaliador = u.id
            WHERE a.id_avaliado = ?
            ORDER BY a.data_avaliacao DESC
            LIMIT 3";
        $stmt = $conn->prepare($sql_recent);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $avaliacoes = $stmt->get_result();
        ?>
        <div class="avaliacoes-container">
            <h3>Avaliações</h3>
            <?php if ($avaliacoes->num_rows > 0): ?>
                <?php while ($a = $avaliacoes->fetch_assoc()): ?>
                    <div class="avaliacao-card">
                        <div class="avaliador-info">
                            <img src="<?= $a['foto_perfil'] ? 'imagens/perfis/' . htmlspecialchars($a['foto_perfil']) : 'imagens/icone-perfil.svg' ?>" class="avaliador-foto">
                            <p><strong>@<?= htmlspecialchars($a['nome_usuario']) ?></strong></p>
                        </div>
                        <div class="avaliacao-nota"><?= str_repeat("⭐", $a['nota']) ?> <span class="data-avaliacao"><?= date("d/m/Y", strtotime($a['data_avaliacao'])) ?></span></div>
                        <?php if (!empty($a['comentario'])): ?>
                            <p class="avaliacao-comentario">"<?= htmlspecialchars($a['comentario']) ?>"</p>
                        <?php endif; ?>

                        <?php
                        $stmt_imgs = $conn->prepare("SELECT caminho_imagem FROM avaliacoes_imagens WHERE id_avaliacao = ?");
                        $stmt_imgs->bind_param("i", $a['id']);
                        $stmt_imgs->execute();
                        $imgs = $stmt_imgs->get_result();
                        ?>
                        <?php if ($imgs->num_rows > 0): ?>
                            <div class="avaliacao-fotos">
                                <?php while ($img = $imgs->fetch_assoc()): ?>
                                    <img src="uploads/avaliacoes/<?= htmlspecialchars($img['caminho_imagem']) ?>" class="foto-avaliacao">
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
                <button class="ver-todas-btn" onclick="abrirPopupAvaliacoes()">Ver todas as avaliações</button>
            <?php else: ?>
                <p>Ainda não há avaliações para este usuário.</p>
            <?php endif; ?>
        </div>

        <!-- 📚 Livros Publicados -->
        <div class="livros-publicados">
            <h3>Livros Publicados</h3>
            <?php if ($livros->num_rows > 0): ?>
                <ul>
                    <?php while ($livro = $livros->fetch_assoc()): ?>
                        <li class="livro-item">
                            <?php
                            $imagens = explode(',', $livro['imagens']);
                            $caminhoImagem = !empty($imagens[0]) ? 'uploads/' . $imagens[0] : 'imagens/sem-imagem.png';
                            ?>
                            <img src="<?= $caminhoImagem ?>" alt="Capa do livro" class="livro-capa" style="width: 100px; height: auto; border-radius: 5px; margin-bottom: 8px;">
                            <div>
                                <strong><?= htmlspecialchars($livro['titulo']) ?></strong><br>
                                Autor: <?= htmlspecialchars($livro['autor']) ?><br>
                                Publicado em: <?= date("d/m/Y", strtotime($livro['data_publicacao'])) ?>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Esse usuário ainda não publicou nenhum livro.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Popup -->
    <div id="popupAvaliacoes" class="popup-avaliacoes">
        <div class="popup-conteudo">
            <span class="fechar" onclick="fecharPopupAvaliacoes()">&times;</span>
            <h2>Todas as Avaliações</h2>
            <div id="todasAvaliacoesConteudo"></div>
        </div>
    </div>

    <script>
        function abrirPopupAvaliacoes() {
            const popup = document.getElementById('popupAvaliacoes');
            popup.style.display = 'flex';
            fetch('buscar_avaliacoes.php?id=<?= $id_usuario ?>')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('todasAvaliacoesConteudo').innerHTML = html;
                });
        }

        function fecharPopupAvaliacoes() {
            document.getElementById('popupAvaliacoes').style.display = 'none';
        }
    </script>
</body>
</html>
