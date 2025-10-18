<?php
include 'conexao.php';
include 'verifica_login.php';

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
    background: linear-gradient(45deg, #E8F5E8, #C8E6C9);
    color: #222;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.container {
    background: #fff;
    padding: 40px 32px;
    border-radius: 20px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    border: 1.5px solid #3f9142;
}

h2 {
    text-align: center;
    margin-bottom: 28px;
    color: #2E7D32;
    font-weight: bold;
    font-size: 24px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #4e4e4e;
    font-size: 15px;
}

/* Inputs arredondados */
input[type="password"] {
    width: 100%;
    padding: 14px 18px;
    margin-bottom: 20px;
    border: 1.5px solid #e0e0e0;
    border-radius: 999px; /* bem arredondado */
    font-size: 16px;
    color: #222;
    background: transparent;
    transition: border 0.2s;
    box-sizing: border-box;
}

input[type="password"]:focus {
    border: 1.5px solid #3f9142;
    outline: none;
}

input::placeholder {
    color: #999;
}

/* Mensagem de erro */
.erro {
    background-color: #f8d7da;
    color: #721c24;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 18px;
    text-align: center;
    font-size: 15px;
    border: 1px solid #f5c6cb;
}

/* Botão arredondado em degradê */
button {
    width: 100%;
    padding: 14px 0;
    background: linear-gradient(90deg, #4CAF50 60%, #2E7D32 100%);
    color: #fff;
    border: none;
    border-radius: 999px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(5, 211, 49, 0.15);
    transition: background 0.3s, transform 0.2s;
}

button:hover {
    background-position: right center;
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 4px 16px rgba(5, 211, 49, 0.25);
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
