<?php
include 'conexao.php';

$erro = "";
$token_valido = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $token_valido = true;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $senha = $_POST['senha'];
        $confirmar_senha = $_POST['confirmar_senha'];

        if (strlen($senha) < 8 || strlen($senha) > 30) {
            $erro = "A senha deve ter entre 8 e 30 caracteres.";
        } elseif ($senha !== $confirmar_senha) {
            $erro = "As senhas não coincidem.";
        } else {
            $nova_senha = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE usuarios SET senha_hash = ?, token = NULL WHERE token = ?");
            $stmt->bind_param("ss", $nova_senha, $token);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "<script>alert('Senha redefinida com sucesso!');window.location.href='index.html';</script>";
            } else {
                $erro = "Token inválido ou senha não alterada.";
            }

            $stmt->close();
        }
    }
} else {
    echo "Token inválido.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nova Senha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right,rgb(16, 143, 44),rgb(228, 250, 230));
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.3);
            padding: 30px;
            border-radius: 15px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 8px;
        }

        .erro {
            background-color: #ff4c4c;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #00c853;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #00b347;
        }
    </style>
</head>
<body>
    <?php if ($token_valido): ?>
    <div class="container">
        <h2>Redefinir Senha</h2>
        <?php if (!empty($erro)): ?>
            <div class="erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Nova Senha:</label>
            <input type="password" name="senha" required>

            <label>Confirmar Senha:</label>
            <input type="password" name="confirmar_senha" required>

            <button type="submit">Redefinir</button>
        </form>
    </div>
    <?php endif; ?>
</body>
</html>
