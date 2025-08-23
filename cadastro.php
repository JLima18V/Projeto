<?php
session_start();
include 'conexao.php';

$erro_email = "";
$erro_nome_email = "";
$sucesso = false;
$email_digitado = "";
$nome_digitado = "";
$nome_usuario_digitado = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe os dados do formulário
    $email = trim($_POST['email']);
    $nome_sobrenome = trim($_POST['nome']);
    $nome_usuario = $_POST['nome_usuario'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);

    // Armazena os valores digitados para manter no formulário em caso de erro
    $email_digitado = $email;
    $nome_digitado = $nome_sobrenome;
    $nome_usuario_digitado = $nome_usuario;

    // Expressão regular para validar o formato geral do e-mail
    $regex = '/^([a-zA-Z]+)\.[0-9]{8,14}@aluno\.etejk\.faetec\.rj\.gov\.br$/';

    // Verifica se o e-mail segue o formato correto
    if (!preg_match($regex, $email, $matches)) {
        $erro_email = "Formato de e-mail inválido. Exemplo: nome.12345678@aluno.etejk.faetec.rj.gov.br";
    } else {
        // Pega o primeiro nome digitado no formulário
        $primeiro_nome = explode(' ', strtolower($nome_sobrenome))[0];

        // Nome extraído da parte antes do ponto no e-mail
        $nome_email = strtolower($matches[1]);

        // Comparação entre o nome do campo e o do e-mail
        if ($primeiro_nome !== $nome_email) {
            $erro_nome_email = "O nome do e-mail não corresponde ao nome digitado. Verifique e tente novamente.";
        }
    }

    // Se não houver erros, insere o usuário no banco
    if (empty($erro_email) && empty($erro_nome_email)) {
        $sql = "INSERT INTO usuarios (email, nome_usuario, senha_hash, nome_sobrenome) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $nome_usuario, $senha, $nome_sobrenome);
try {
    $stmt->execute();

    $_SESSION['nome_usuario'] = $nome_usuario;
    $_SESSION['nome_sobrenome'] = $nome_sobrenome;
    $_SESSION['id'] = $stmt->insert_id;

    $sucesso = true;
    header("Location: login.php");
    exit;
} catch (mysqli_sql_exception $e) {
    if (strpos($e->getMessage(), "Duplicate entry") !== false && strpos($e->getMessage(), "usuarios.email") !== false) {
        $erro_email = "Este e-mail já está cadastrado. Tente outro ou <a href='login.php'>faça login</a>.";
    } else {
        echo "Erro inesperado: " . $e->getMessage();
    }
}


        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cadastrologin.css">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <title>Cadastro</title>
</head>
<body>
    <div class="login-container">
        <h1>Bem-vindo!</h1>
        <div class="login-box">
            <h2>Faça seu cadastro</h2>

            <!-- Exibe mensagem de erro ou sucesso -->
            <?php if (!$sucesso && ($erro_email || $erro_nome_email)) : ?>
                <div style="color: red; font-size: 1.2em;">
                    <p><?= $erro_email ?></p>
                    <p><?= $erro_nome_email ?></p>
                </div>
            <?php endif; ?>

            <form action="cadastro.php" method="POST" id="cadastroForm">
                <div class="input-container">
                    <label for="email">Email institucional</label>
                    <input type="email" name="email" placeholder="nome.matricula@aluno.etejk.faetec.rj.gov.br" value="<?= $email_digitado ?>" required>
                </div>

                <div class="input-container">
                    <label for="nome">Nome e Sobrenome</label>
                    <input type="text" name="nome" placeholder="Nome e Sobrenome" value="<?= $nome_digitado ?>" required>
                </div>

                <div class="input-container">
                    <label for="nome_usuario">Nome de usuário</label>
                    <input type="text" name="nome_usuario" placeholder="(O que aparecerá para os outros usuários)" value="<?= $nome_usuario_digitado ?>" required>
                </div>

                <hr style="margin: 20px 0; border: none; border-top: 1px solid #e0e0e0; width: 100%;">

                <div class="input-container">
                    <label for="senha">Senha</label>
                    <input type="password" name="senha" placeholder="Crie uma senha" required minlength="8">
                </div>

                <div class="input-container">
                    <label for="confirmar_senha">Confirmar Senha</label>
                    <input type="password" name="confirmar_senha" placeholder="Digite a senha novamente" required>
                </div>

                <button type="submit" class="botao">Cadastrar</button>
            </form>

            <p class="cadastro-link">Já tem uma conta? <a href="login.html">Faça login</a></p>
        </div>
    </div>
</body>
</html>
