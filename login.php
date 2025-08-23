<?php
session_start();
include 'conexao.php';

$login_error = false;
$cadastro_sucesso = false;
$cadastro_erro = "";

// Verifica se já está logado
if (isset($_SESSION['id'])) {
    header("Location: homepage.php");
    exit();
}

// Exibe mensagens salvas na sessão e limpa
if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
if (isset($_SESSION['cadastro_erro'])) {
    $cadastro_erro = $_SESSION['cadastro_erro'];
    unset($_SESSION['cadastro_erro']);
}
if (isset($_SESSION['cadastro_sucesso'])) {
    $cadastro_sucesso = $_SESSION['cadastro_sucesso'];
    unset($_SESSION['cadastro_sucesso']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // LOGIN
    if (isset($_POST['login'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'];
        
        $stmt = $conn->prepare("SELECT id, nome_usuario, senha_hash FROM usuarios WHERE email = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                if (password_verify($senha, $row['senha_hash'])) {
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['usuario'] = $row['nome_usuario'];
                    $_SESSION['email'] = $email;
                    
                    if (isset($_POST['lembrar'])) {
                        setcookie('lembrar_email', $email, time() + (86400 * 30), "/");
                    } else {
                        setcookie('lembrar_email', '', time() - 3600, "/");
                    }
                    
                    header("Location: homepage.php");
                    exit();
                }
            }
            
            $login_error = true;
            $stmt->close();
        }
    }

    // CADASTRO
    if (isset($_POST['cadastro'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $nome = filter_var($_POST['nome'], FILTER_SANITIZE_STRING);
        $sobrenome = filter_var($_POST['sobrenome'], FILTER_SANITIZE_STRING);
        $nome_usuario = filter_var($_POST['nome_usuario'], FILTER_SANITIZE_STRING);
        $senha = $_POST['senha'];
        
        // Hash da senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Preparar a query com os campos corretos da tabela
        $stmt = $conn->prepare("INSERT INTO usuarios (email, nome, sobrenome, nome_usuario, senha_hash) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("sssss", $email, $nome, $sobrenome, $nome_usuario, $senha_hash);
            
            if ($stmt->execute()) {
                $_SESSION['cadastro_sucesso'] = true;
                header("Location: Login.php");
                exit();
            } else {
                if ($conn->errno == 1062) { // Erro de duplicação
                    $cadastro_erro = "Este email já está cadastrado.";
                } else {
                    $cadastro_erro = "Erro ao cadastrar: " . $stmt->error;
                }
            }
            $stmt->close();
        } else {
            $cadastro_erro = "Erro na preparação do cadastro: " . $conn->error;
        }
    }
}

// Lê o cookie se existir
$saved_email = isset($_COOKIE['lembrar_email']) ? $_COOKIE['lembrar_email'] : "";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrocaTrocaJK</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="wrapper">
        <!-- Formulário de Login -->
        <form id="loginForm" method="POST" action="Login.php" style="<?= isset($_POST['cadastro']) ? 'display:none;' : '' ?>">
            <h1>Login</h1>
            <?php if ($login_error): ?>
            <div class="login-error-message">
                Email ou senha incorretos.
            </div>
            <?php endif; ?>
            <div class="input-box">
                <input type="email" name="email" id="email" placeholder=" " required value="<?= htmlspecialchars($saved_email) ?>">
                <label for="email" class="floating-label">Email</label>
                <img src="imagens/Login-Conta/envelope.svg" alt="Usuário" class="input-icon">
            </div>
            <div class="input-box">
                <input type="password" name="senha" id="login_senha" placeholder=" " required>
                <label for="senha" class="floating-label">Senha</label>
                <button type="button" class="eye-btn" id="toggleLoginSenha" tabindex="-1">
                    <img src="imagens/Login-Conta/eye-slash.svg" alt="Mostrar senha" id="login_eyeIcon">
                </button>
            </div>
            <div class="lembrar-equeceu">
                <label for="lembrar">
                    <input type="checkbox" name="lembrar" id="lembrar" <?= $saved_email ? 'checked' : '' ?>>
                    Lembrar-me
                </label>
                <a href="esqueci_senha.html">Esqueci minha senha</a>
            </div>
            <button type="submit" name="login">Login</button>
            <div class="cadastro">
                <p>Não tem uma conta? <a href="#" id="showCadastro">Cadastre-se!</a></p>
            </div>
        </form>

        <!-- Formulário de Cadastro (inicialmente escondido) -->
        <form id="cadastroForm" method="POST" action="Login.php" style="<?= isset($_POST['cadastro']) ? '' : 'display:none;' ?>">
            <h1>Cadastro</h1>
            <?php if ($cadastro_erro): ?>
                <div class="login-error-message"><?= $cadastro_erro ?></div>
            <?php elseif ($cadastro_sucesso): ?>
                <div class="login-error-message" style="color:green;">Cadastro realizado com sucesso! <a href="#" id="showLogin">Faça login</a></div>
            <?php endif; ?>
            <div id="cadastro-error-message" class="login-error-message" style="display:none;">
                <ul id="cadastro-error-list" style="list-style:none; padding:0; margin:0;"></ul>
            </div>
            <div class="input-box">
                <input type="email" name="email" id="cadastro_email" placeholder=" " required>
                <label for="cadastro_email" class="floating-label">Email institucional</label>
                <img src="imagens/Login-Conta/envelope.svg" alt="Email" class="input-icon">
            </div>
            <div class="nome-sobrenome-row">
                <div class="input-box">
                    <input type="text" id="nome" name="nome" placeholder=" " required pattern="^\S+$" title="Apenas uma palavra, sem espaços">
                    <label for="nome" class="floating-label">Nome</label>
                </div>
                <div class="input-box">
                    <input type="text" id="sobrenome" name="sobrenome" placeholder=" " required pattern="^\S+$" title="Apenas uma palavra, sem espaços">
                    <label for="sobrenome" class="floating-label">Sobrenome</label>
                </div>
            </div>
            <div class="input-box">
                <input type="text" name="nome_usuario" id="nome_usuario" placeholder=" " required>
                <label for="nome_usuario" class="floating-label">Nome de usuário</label>
                <img src="imagens/Login-Conta/person.svg" alt="Nome de usuário" class="input-icon">
            </div>
            <hr class="separador-cadastro">
            <div class="input-box">
                <input type="password" name="senha" id="cadastro_senha" placeholder=" " required minlength="8">
                <label for="cadastro_senha" class="floating-label">Senha</label>
                <button type="button" class="eye-btn" id="toggleCadastroSenha" tabindex="-1">
                    <img src="imagens/Login-Conta/eye-slash.svg" alt="Mostrar senha" id="cadastro_eyeIcon">
                </button>
            </div>
            <div class="input-box">
                <input type="password" name="confirmar_senha" id="confirmar_senha" placeholder=" " required>
                <label for="confirmar_senha" class="floating-label">Confirmar Senha</label>
                <button type="button" class="eye-btn" id="toggleConfirmarSenha" tabindex="-1">
                    <img src="imagens/Login-Conta/eye-slash.svg" alt="Mostrar senha" id="confirmar_eyeIcon">
                </button>
                <span id="erro_senha" style="color: red; font-size: 0.8em; display: none;">As senhas não coincidem!</span>
                <span id="erro_tamanho" style="color: red; font-size: 0.8em; display: none;">A senha deve ter no mínimo 8 caracteres!</span>
            </div>
            <button type="submit" class="botao-cadastro" name="cadastro">Cadastrar</button>
            <div class="cadastro">
                <p>Já tem uma conta? <a href="#" id="showLogin">Faça login</a></p>
            </div>
        </form>
    </div>

    <script src="forms-login.js"></script>
    <script>
function setupButtonHoverEffect(btn) {
    btn.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const percentX = (x / rect.width) * 100;
        const percentY = (y / rect.height) * 100;
        this.style.background = `radial-gradient(circle at ${percentX}% ${percentY}%, #4CAF50 0%, #2E7D32 100%)`;
    });
    btn.addEventListener('mouseleave', function() {
        this.style.background = 'linear-gradient(90deg, #4CAF50 60%, #2E7D32 100%)';
    });
}

// Aplica para todos os botões de submit (exceto .eye-btn)
document.querySelectorAll('.wrapper button[type="submit"]:not(.eye-btn)').forEach(setupButtonHoverEffect);
</script>
</body>
</html>