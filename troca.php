<?php
session_start();
include 'conexao.php';

// ID do livro que o usuário deseja (passado por GET na URL, ex: trocar.php?id=5)
$id_livro_solicitado = $_GET['id'];

// Buscar dono do livro solicitado
$sql = "SELECT usuarios.id AS id_receptor, livros.titulo 
        FROM livros 
        JOIN usuarios ON livros.id_usuario = usuarios.id 
        WHERE livros.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_livro_solicitado);
$stmt->execute();
$result = $stmt->get_result();
$livro = $result->fetch_assoc();
$stmt->close();

$id_receptor = $livro['id_receptor'];
$titulo_livro = $livro['titulo'];

// Buscar os livros do usuário logado
$sql = "SELECT id, titulo FROM livros WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$meus_livros = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Solicitar Troca</title>
</head>
<body>
  <h2>Solicitar troca pelo livro: <b><?php echo $titulo_livro; ?></b></h2>

  <form action="processa_troca.php" method="POST">
      <!-- Dados ocultos -->
      <input type="hidden" name="id_receptor" value="<?php echo $id_receptor; ?>">
      <input type="hidden" name="id_livro_solicitado" value="<?php echo $id_livro_solicitado; ?>">

      <p>Selecione os seus livros que deseja oferecer:</p>
      <?php while($livro = $meus_livros->fetch_assoc()): ?>
          <label>
              <input type="checkbox" name="livros_oferecidos[]" value="<?php echo $livro['id']; ?>">
              <?php echo $livro['titulo']; ?>
          </label><br>
      <?php endwhile; ?>

      <br>
      <button type="submit">Enviar Solicitação</button>
  </form>
</body>
</html>
