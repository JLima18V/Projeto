
<?php
include 'conexao.php';

if (!isset($_GET['id'])) {
    echo "ID do usuário não fornecido.";
    exit;
}

$id_usuario = intval($_GET['id']);

// Agora buscando também instagram e whatsapp
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

// Buscar livros desse usuário
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
            <img src="imagens/icone-mensagem.svg" alt="Chat">
            <div class="foto-perfil-container" onclick="window.location.href='perfil.php'">
                <img src="<?= $foto_perfil_logado ? 'imagens/perfis/' . htmlspecialchars($foto_perfil_logado) : 'imagens/icone-perfil.svg' ?>" 
                     alt="Perfil" 
                     class="perfil-icon" 
                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            </div>
        </div>
    </header>

    <!-- Conteúdo da Página -->
    <div class="perfil-container">
        <!-- Área do Perfil -->
        <div class="perfil-info">
            <img src="<?= $usuario['foto_perfil'] ? 'imagens/perfis/' . htmlspecialchars($usuario['foto_perfil']) : 'imagens/icone-perfil.svg' ?>" 
                 alt="Perfil" 
                 class="perfil-icon">
            <div class="perfil-details">
                <h2><?= htmlspecialchars($usuario['nome']) . ' ' . htmlspecialchars($usuario['sobrenome']) ?></h2>
                <p>@<?= htmlspecialchars($usuario['nome_usuario']) ?></p>

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

        <!-- Livros Publicados -->
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
</body>
</html>
