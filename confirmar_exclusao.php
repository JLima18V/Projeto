<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Exclusão</title>
</head>
<body>
<?php
session_start();

?>

<h2>Tem certeza que deseja excluir sua conta?</h2>
<p>Essa ação não pode ser desfeita.</p>

<form action="deletar.php" method="POST">
    <input type="hidden" name="confirmar" value="1">
    <button type="submit">Sim, excluir minha conta</button>
</form>

<a href="perfil.php">Cancelar</a>

</body>
</html>