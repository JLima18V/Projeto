<?php
session_start();
include 'conexao.php';
include  'verifica_login.php';




$id_usuario = $_SESSION['id'];

// Query para obter os livros da lista de desejos do usuário
$sql = "SELECT l.*, u.nome_usuario, u.foto_perfil, u.id as id_dono 
        FROM lista_desejos ld
        INNER JOIN livros l ON ld.id_livro = l.id
        LEFT JOIN usuarios u ON l.id_usuario = u.id
        WHERE ld.id_usuario = ?
        ORDER BY l.id DESC";
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
  <title>Lista de Desejos</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="listadedesejo.css">
  <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
</head>
<body>
  <!-- Cabeçalho -->
  <header class="header-lista">
    <img src="imagens/icone-voltar.png" alt="Voltar" class="voltar-icon" onclick="window.location.href='homepage.php'">
    <h1>Lista de Desejos</h1>
  </header>

  <!-- Conteúdo da Lista -->
  <main class="conteudo-lista">
    <?php
    if ($result->num_rows > 0) {
        while ($livro = $result->fetch_assoc()) {
            $usuario = !empty($livro['nome_usuario']) ? "@" . htmlspecialchars($livro['nome_usuario']) : "@usuarioTeste";
            $foto_perfil = !empty($livro['foto_perfil']) ? 'imagens/perfis/' . htmlspecialchars($livro['foto_perfil']) : 'imagens/icone-perfil.svg';
            $imagens = explode(",", $livro['imagens']);
            $primeiraImagem = $imagens[0];
            
            echo '<div class="card-livro">';
              echo '<div class="card-header">';
                echo '<div class="header-usuario">';
                  echo '<img src="'. $foto_perfil .'" class="perfil-icon" alt="Perfil">';
                  echo '<a class="user" href="perfil_usuario.php?id=' . $livro['id_dono'] . '">' . $usuario . '</a>';
                echo '</div>';
              echo '</div>';
              
              echo '<div class="imagem-container">';
                echo '<img src="uploads/' . htmlspecialchars($primeiraImagem) . '" class="imagem-livro" alt="Capa do Livro">';
                // Botão de favorito - já está na lista, portanto "active"
                echo '<button class="wishlist-btn active" data-livro-id="' . $livro['id'] . '">';
                  echo '<i class="heart-icon">♥</i>';
                echo '</button>';
              echo '</div>';
              
              echo '<div class="info-livro">';
                echo '<p class="titulo"><strong>Título:</strong> ' . htmlspecialchars($livro['titulo']) . '</p>';
                echo '<p class="genero"><strong>Gênero:</strong> ' . htmlspecialchars($livro['genero']) . '</p>';
                echo '<p class="autor"><strong>Autor:</strong> ' . htmlspecialchars($livro['autor']) . '</p>';
                echo '<p class="estado"><strong>Estado:</strong> ' . htmlspecialchars($livro['estado']) . '</p>';
              echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p style="padding:20px;">Sua lista de desejos está vazia.</p>';
    }
    $stmt->close();
    $conn->close();
    ?>
  </main>

  <!-- Script para remover o livro da lista via toggle_favorito.php -->
  <script>
    // Função para mostrar toast
    function showToast(message) {
      const toast = document.createElement('div');
      toast.className = 'toast';
      toast.textContent = message;
      document.body.appendChild(toast);
      requestAnimationFrame(() => {
          toast.classList.add('show');
          setTimeout(() => {
              toast.classList.remove('show');
              setTimeout(() => toast.remove(), 300);
          }, 2200);
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.wishlist-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
          e.stopPropagation();
          const livroId = button.dataset.livroId;
          try {
            const res = await fetch('toggle_favorito.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ id_livro: livroId })
            });
            const data = await res.json();
            if (data.success) {
              // Remove o card da lista
              const card = button.closest('.card-livro');
              card.remove();
              showToast("Livro removido da lista de desejos");
            } else {
              showToast(data.message || "Erro ao atualizar favoritos");
            }
          } catch (error) {
            console.error(error);
            showToast("Erro na requisição");
          }
        });
      });
    });
  </script>
</body>
</html>
