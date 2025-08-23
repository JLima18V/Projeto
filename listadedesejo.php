<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="listadeesejo.css">
    
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <title>Lista de Desejo</title>
</head>
<body>
    <!-- Cabeçalho da Página -->
    <header class="header-lista">
        <img src="imagens/icone-voltar.png" alt="Voltar" class="voltar-icon" onclick="window.location.href='homepage.php'">
        <h1>Lista de Desejo</h1>
    </header>

    <?php
    session_start();
    include 'conexao.php';

    $id_usuario = $_SESSION['id'];

    // --- ADICIONAR LIVRO NA LISTA ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_livro'])) {
        $id_livro = intval($_POST['id_livro']);

        // Verifica se já está na lista para não duplicar
        $check = $conn->prepare("SELECT * FROM lista_desejos WHERE id_usuario = ? AND id_livro = ?");
        $check->bind_param("ii", $id_usuario, $id_livro);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO lista_desejos (id_usuario, id_livro) VALUES (?, ?)");
            $stmt->bind_param("ii", $id_usuario, $id_livro);
            $stmt->execute();
            $stmt->close();
            echo "<p class='msg-sucesso'>Livro adicionado à sua lista de desejos!</p>";
        } else {
            echo "<p class='msg-alerta'>Esse livro já está na sua lista de desejos.</p>";
        }
        $check->close();
    }

    // --- REMOVER LIVRO DA LISTA ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_livro'])) {
        $id_livro = intval($_POST['remover_livro']);
        $stmt = $conn->prepare("DELETE FROM lista_desejos WHERE id_usuario = ? AND id_livro = ?");
        $stmt->bind_param("ii", $id_usuario, $id_livro);
        $stmt->execute();
        $stmt->close();
        echo "<p class='msg-removido'>Livro removido da sua lista de desejos!</p>";
    }

    // --- PEGAR LIVROS DA LISTA DO USUÁRIO ---
    $sql = "SELECT livros.id, livros.titulo, livros.autor, livros.imagens 
            FROM lista_desejos 
            INNER JOIN livros ON lista_desejos.id_livro = livros.id 
            WHERE lista_desejos.id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $livros = $stmt->get_result();
    ?>

    <!-- Conteúdo da Lista de Desejos -->
  <div class="conteudo-lista" style="display: flex; flex-wrap: wrap; gap: 20px; padding: 20px;">
<?php

include 'conexao.php';

$id_usuario = $_SESSION['id'];

$sql = "SELECT l.*, u.nome_usuario, u.foto_perfil 
        FROM lista_desejos ld
        INNER JOIN livros l ON ld.id_livro = l.id
        LEFT JOIN usuarios u ON l.id_usuario = u.id
        WHERE ld.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($livro = $result->fetch_assoc()) {
        $usuario = !empty($livro['nome_usuario']) ? "@".htmlspecialchars($livro['nome_usuario']) : "@usuario";
        $foto_perfil = !empty($livro['foto_perfil']) ? 'imagens/perfis/' . htmlspecialchars($livro['foto_perfil']) : 'imagens/icone-perfil.svg';

        $imagens = explode(",", $livro['imagens']);
        $primeiraImagem = $imagens[0];

        echo '<div class="card-livro">';
        echo '<div class="header-usuario">';
        echo '<img src="' . $foto_perfil . '" class="perfil-icon" style="width:30px;height:30px;border-radius:50%;object-fit:cover;">';
           $link_perfil = ($livro['id_usuario'] == $_SESSION['id']) ? 'perfil.php' : 'perfil_usuario.php?id=' . $livro['id_usuario'];
            echo '<a class="user" href="' . $link_perfil . '">' . $usuario . '</a>';
        echo '</div>';

        echo '<img src="uploads/' . htmlspecialchars($primeiraImagem) . '" class="imagem-livro" alt="Capa do Livro">';
        echo '<div class="info-livro">';
        echo '<p class="titulo"><strong>Título:</strong> ' . htmlspecialchars($livro['titulo']) . '</p>';
        echo '<p class="autor"><strong>Autor:</strong> ' . htmlspecialchars($livro['autor']) . '</p>';
        echo '<p class="genero"><strong>Gênero:</strong> ' . htmlspecialchars($livro['genero']) . '</p>';
        echo '<p class="estado"><strong>Estado:</strong> ' . htmlspecialchars($livro['estado']) . '</p>';
        echo '</div>';

        // Botão remover
        echo '<form method="POST" action="">';
        echo '<input type="hidden" name="remover_livro" value="' . $livro['id'] . '">';
        echo '<button type="submit" class="postar" style="margin-top:10px;">Remover</button>';
        echo '</form>';

        echo '</div>';
    }
} else {
    echo '<p>Sua lista de desejos está vazia.</p>';
}

$stmt->close();
$conn->close();
?>

</div>



</body>
</html>
