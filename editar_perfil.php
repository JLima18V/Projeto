<?php
session_start();
include 'conexao.php';
include 'verifica_login.php';


// Busca os dados do usuário
$id = $_SESSION['id'];
$sql = "SELECT nome_usuario, instagram, whatsapp FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Formatar o WhatsApp para exibição se já existir
$whatsapp_formatado = '';
if (!empty($usuario['whatsapp'])) {
    $numero_limpo = preg_replace('/\D/', '', $usuario['whatsapp']);
    if (strlen($numero_limpo) === 11) {
        $whatsapp_formatado = '(' . substr($numero_limpo, 0, 2) . ') ' . 
                             substr($numero_limpo, 2, 5) . '-' . 
                             substr($numero_limpo, 7, 4);
    } else {
        $whatsapp_formatado = $usuario['whatsapp'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="edit_perfil.css">
    <link rel="stylesheet" href="style.css">
    <title>Editar Perfil</title>
    <style>
    
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <header class="header">
        <a href="homepage.php">
            <img src="imagens/logo-trocatrocajk.png" alt="Logo" class="logo">
        </a>
        <div class="search-container">
            <form action="pesquisa.php" method="GET">
                <input type="text" name="q" class="search-bar" placeholder="Pesquise livros">
                <img src="imagens/icone-filtro.svg" alt="Filtrar" class="filter-icon">
            </form>
        </div>
        <div class="icons">
            <img src="imagens/icone-publicarlivro.svg" alt="Publicar livro" onclick="window.location.href='publicar.php'">
            <img src="imagens/icone-listadedesejo.svg" alt="Lista de desejos" onclick="window.location.href='listadedesejo.php'">
            <img src="imagens/icone-mensagem.svg" alt="Chat">
            <div class="profile-dropdown">
                <img src="<?php echo isset($_SESSION['foto_perfil']) ? 'imagens/perfis/' . $_SESSION['foto_perfil'] : 'imagens/icone-perfil.svg'; ?>" 
                     alt="Perfil" 
                     class="perfil-icon">
                <div class="profile-dropdown-content">
                    <a href="perfil.php">Meu Perfil</a>
                    <a href="minhas_trocas.php">Minhas Trocas</a>
                    <a href="logout.php">Sair da Conta</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo principal -->
    <main class="editar-perfil-container">
        <h2>Editar Perfil</h2>

        <form action="atualizar_perfil.php" method="POST" class="editar-perfil-form" onsubmit="return validarWhatsApp()">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <label for="nome_usuario">Nome de Usuário:</label>
            <input type="text" id="nome_usuario" name="nome_usuario" value="<?php echo $usuario['nome_usuario']; ?>" required>

            <label for="instagram">Instagram:</label>
            <input type="text" name="instagram" id="instagram" value="<?= htmlspecialchars($usuario['instagram']) ?>">

            <label for="whatsapp">WhatsApp:</label>
            <div class="whatsapp-container">
                <span class="whatsapp-prefix"></span>
                <input type="text" 
                       name="whatsapp" 
                       id="whatsapp" 
                       value="<?= htmlspecialchars($whatsapp_formatado ?: '') ?>" 
                       placeholder="(00) 00000-0000"
                       maxlength="15"
                       oninput="formatarWhatsApp(this)"
                       onkeydown="return permitirApenasNumeros(event)">
                <div class="whatsapp-error" id="whatsapp-error">
                    Número deve ter 10 ou 11 dígitos
                </div>
            </div>

            <div class="botoes-container">
                <button type="submit" class="botao-estilizado salvar">Salvar Alterações</button>
                <a href="perfil.php" class="botao-estilizado cancelar">Cancelar</a>
            </div>
        </form>

        <!-- Separador -->
        <div class="separador"></div>

        <!-- Botão de deletar conta -->
        <a href="confirmar_exclusao.html" class="botao-deletar-conta">
            Deletar Conta
        </a>
    </main>

    <script>
        function formatarWhatsApp(input) {
            // Remove tudo que não é número
            let valor = input.value.replace(/\D/g, '');
            
            // Limita a 11 dígitos (máximo para celular brasileiro)
            if (valor.length > 11) {
                valor = valor.substring(0, 11);
            }
            
            // Aplica a formatação
            let valorFormatado = '';
            
            if (valor.length > 0) {
                valorFormatado = '(' + valor.substring(0, 2);
            }
            
            if (valor.length > 2) {
                valorFormatado += ') ' + valor.substring(2, 7);
            }
            
            if (valor.length > 7) {
                valorFormatado += '-' + valor.substring(7, 11);
            }
            
            // Atualiza o valor do input
            input.value = valorFormatado;
            
            // Validação visual
            validarCampoWhatsApp(valor);
        }
        
        function permitirApenasNumeros(event) {
            // Permite apenas: números, backspace, delete, tab, setas
            const teclasPermitidas = [
                'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
                'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'
            ];
            
            if (teclasPermitidas.includes(event.key) || 
                (event.key >= '0' && event.key <= '9')) {
                return true;
            }
            
            event.preventDefault();
            return false;
        }
        
        function validarCampoWhatsApp(valorNumerico) {
            const errorElement = document.getElementById('whatsapp-error');
            const inputElement = document.getElementById('whatsapp');
            
            if (valorNumerico.length === 10 || valorNumerico.length === 11 || valorNumerico.length === 0) {
                // Número válido ou vazio
                errorElement.style.display = 'none';
                inputElement.style.borderColor = '#ccc';
            } else {
                // Número inválido
                errorElement.style.display = 'block';
                inputElement.style.borderColor = '#e74c3c';
            }
        }
        
        function validarWhatsApp() {
            const input = document.getElementById('whatsapp');
            const numero = input.value.replace(/\D/g, '');
            
            // WhatsApp é opcional, mas se preenchido deve ser válido
            if (numero.length > 0 && numero.length !== 10 && numero.length !== 11) {
                alert('Por favor, insira um número de WhatsApp válido (10 ou 11 dígitos)');
                input.focus();
                return false;
            }
            
            return true;
        }
        
        // Inicialização quando a página carrega
        document.addEventListener('DOMContentLoaded', function() {
            const inputWhatsApp = document.getElementById('whatsapp');
            
            // Se o campo estiver vazio, mostra o placeholder completo
            if (!inputWhatsApp.value) {
                inputWhatsApp.value = '() -';
            }
            
            // Foca após o "(" quando o usuário clicar no campo
            inputWhatsApp.addEventListener('focus', function() {
                if (this.value === '() -') {
                    setTimeout(() => {
                        this.setSelectionRange(1, 1);
                    }, 0);
                }
            });
            
            // Valida o campo atual ao carregar a página
            const numeroAtual = inputWhatsApp.value.replace(/\D/g, '');
            validarCampoWhatsApp(numeroAtual);
        });
    </script>
</body>
</html>