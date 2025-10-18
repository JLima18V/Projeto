<?php
session_start();
include 'conexao.php';
include 'verifica_login.php';

// Busca os dados completos do usuário
$id = $_SESSION['id'];
$sql = "SELECT nome, sobrenome, nome_usuario, email, instagram, whatsapp, foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Formatar o WhatsApp para exibição
$whatsapp_formatado = '';
if (!empty($usuario['whatsapp'])) {
    $numero_limpo = $usuario['whatsapp'];
    if (strlen($numero_limpo) === 11) {
        $whatsapp_formatado = '(' . substr($numero_limpo, 0, 2) . ') ' . 
                             substr($numero_limpo, 2, 5) . '-' . 
                             substr($numero_limpo, 7, 4);
    } else if (strlen($numero_limpo) === 10) {
        $whatsapp_formatado = '(' . substr($numero_limpo, 0, 2) . ') ' . 
                             substr($numero_limpo, 2, 4) . '-' . 
                             substr($numero_limpo, 6, 4);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="configuracoes.css">
    <title>Configurações</title>
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
                        <a href="editar_perfil.php">Editar Perfil</a>
                        <a href="confirmar_saida.html">Sair da Conta</a>
                        <a href="minhas_trocas.php  ">Minhas Trocas</a>
                    </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo principal -->
    <main class="configuracoes-container">
        <div class="configuracoes-sidebar">
            <h3>Configurações</h3>
            <nav class="configuracoes-menu">
                <a href="#conta" class="menu-item active" data-tab="conta">Conta</a>
                <a href="#privacidade" class="menu-item" data-tab="privacidade">Privacidade</a>
                <a href="#notificacoes" class="menu-item" data-tab="notificacoes">Notificações</a>
                <a href="#seguranca" class="menu-item" data-tab="seguranca">Segurança</a>
                <a href="#aparencia" class="menu-item" data-tab="aparencia">Aparência</a>
            </nav>
        </div>

        <div class="configuracoes-content">
            <!-- Mensagens de feedback -->
            <?php if (isset($_SESSION['sucesso'])): ?>
                <div class="mensagem sucesso"><?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['erro'])): ?>
                <div class="mensagem erro"><?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?></div>
            <?php endif; ?>

            <!-- ABA: CONTA -->
            <div id="conta" class="tab-content active">
                <h2>Configurações da Conta</h2>
                <p class="tab-description">Gerencie suas informações pessoais</p>

                <form action="atualizar_configuracoes.php" method="POST" enctype="multipart/form-data" class="configuracoes-form">
                    <input type="hidden" name="aba" value="conta">

                    <div class="foto-perfil-section">
                        <div class="foto-atual">
                            <img src="<?php echo isset($usuario['foto_perfil']) ? 'imagens/perfis/' . $usuario['foto_perfil'] : 'imagens/icone-perfil.svg'; ?>" 
                                 alt="Foto atual" class="foto-perfil-grande">
                        </div>
                        <div class="upload-actions">
                            <label for="foto_perfil" class="botao-upload">Alterar Foto</label>
                            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display: none;">
                            <button type="button" class="botao-remover" onclick="removerFoto()">Remover Foto</button>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" 
                                   value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                        </div>

                    

                        <div class="form-group">
                            <label for="email">E-mail *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>

                        <div class="form-group full-width">
                            <label for="bio">Biografia</label>
                            <textarea id="bio" name="bio" rows="4" 
                                      placeholder="Conte um pouco sobre você..."><?php echo htmlspecialchars(""); ?></textarea>
                            <span class="contador-caracteres">0/160</span>
                        </div>

                        <div class="form-group">
                            <label for="instagram">Instagram</label>
                            <input type="text" id="instagram" name="instagram" 
                                   value="<?php echo htmlspecialchars($usuario['instagram']); ?>" 
                                   placeholder="@seuusuario">
                        </div>

                        <div class="form-group">
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
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="botao-primario">Salvar Alterações</button>
                        <a href="perfil.php" class="botao-secundario">Cancelar</a>
                    </div>
                </form>
            </div>

            <!-- ABA: PRIVACIDADE -->
            <div id="privacidade" class="tab-content">
                <h2>Privacidade</h2>
                <p class="tab-description">Controle quem pode ver suas informações</p>

                <form action="atualizar_configuracoes.php" method="POST" class="configuracoes-form">
                    <input type="hidden" name="aba" value="privacidade">

                    <div class="privacidade-options">
                        <div class="privacidade-item">
                            <div class="privacidade-info">
                                <h4>Perfil Público</h4>
                                <p>Tornar seu perfil visível para todos os usuários</p>
                            </div>
                           
                        </div>

                        <div class="privacidade-item">
                            <div class="privacidade-info">
                                <h4>Mostrar E-mail</h4>
                                <p>Permitir que outros usuários vejam seu e-mail</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="mostrar_email" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="privacidade-item">
                            <div class="privacidade-info">
                                <h4>Mostrar WhatsApp</h4>
                                <p>Permitir que outros usuários vejam seu WhatsApp</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="mostrar_whatsapp" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="botao-primario">Salvar Configurações</button>
                    </div>
                </form>
            </div>

            <!-- ABA: NOTIFICAÇÕES -->
            <div id="notificacoes" class="tab-content">
                <h2>Notificações</h2>
                <p class="tab-description">Gerencie como e quando você recebe notificações</p>

                <form action="atualizar_configuracoes.php" method="POST" class="configuracoes-form">
                    <input type="hidden" name="aba" value="notificacoes">

                    <div class="notificacoes-section">
                        <h4>Notificações por E-mail</h4>
                        
                        <div class="notificacao-item">
                            <div class="notificacao-info">
                                <h5>Novas Mensagens</h5>
                                <p>Receba notificações quando receber novas mensagens</p>
                            </div>
                            <label class="switch">
                                
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="notificacao-item">
                            <div class="notificacao-info">
                                <h5>Ofertas de Troca</h5>
                                <p>Receba notificações sobre novas ofertas de troca</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="notif_ofertas" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="notificacao-item">
                            <div class="notificacao-info">
                                <h5>Atividades do Perfil</h5>
                                <p>Receba notificações sobre seguidores e interações</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="notif_atividades" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="botao-primario">Salvar Preferências</button>
                    </div>
                </form>
            </div>

            <!-- ABA: SEGURANÇA -->
            <div id="seguranca" class="tab-content">
                <h2>Segurança</h2>
                <p class="tab-description">Proteja sua conta e gerencie sua senha</p>

                <form action="atualizar_configuracoes.php" method="POST" class="configuracoes-form">
                    <input type="hidden" name="aba" value="seguranca">

                    <div class="seguranca-section">
                        <div class="form-group">
                            <label for="senha_atual">Senha Atual *</label>
                            <input type="password" id="senha_atual" name="senha_atual" required>
                        </div>

                        <div class="form-group">
                            <label for="nova_senha">Nova Senha *</label>
                            <input type="password" id="nova_senha" name="nova_senha" required>
                            <div class="forca-senha">
                                <div class="barra-senha" id="barra-senha"></div>
                                <span class="texto-senha" id="texto-senha">Força da senha</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar Nova Senha *</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                            <span class="erro-senha" id="erro-senha" style="display: none;">As senhas não coincidem</span>
                        </div>
                    </div>

                    <div class="sessao-section">
                        <h4>Sessões Ativas</h4>
                        <div class="sessao-item">
                            <div class="sessao-info">
                                <h5>Dispositivo Atual</h5>
                                <p><?php echo $_SERVER['HTTP_USER_AGENT']; ?></p>
                                <span class="sessao-horario">Conectado agora</span>
                            </div>
                            <button type="button" class="botao-encerrar" onclick="encerrarOutrasSessoes()">Encerrar Outras Sessões</button>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="botao-primario">Alterar Senha</button>
                    </div>
                </form>
            </div>

            <!-- ABA: APARÊNCIA -->
            <div id="aparencia" class="tab-content">
                <h2>Aparência</h2>
                <p class="tab-description">Personalize a aparência do site</p>

                <div class="aparencia-options">
                    <div class="tema-option">
                        <h4>Modo de Exibição</h4>
                        <div class="tema-botoes">
                            <button type="button" class="tema-botao active" data-tema="claro">
                                <span class="tema-icone claro"></span>
                                Claro
                            </button>
                            <button type="button" class="tema-botao" data-tema="escuro">
                                <span class="tema-icone escuro"></span>
                                Escuro
                            </button>
                            <button type="button" class="tema-botao" data-tema="auto">
                                <span class="tema-icone auto"></span>
                                Automático
                            </button>
                        </div>
                    </div>

                    <div class="fonte-option">
                        <h4>Tamanho da Fonte</h4>
                        <div class="fonte-botoes">
                            <button type="button" class="fonte-botao" data-fonte="pequeno">P</button>
                            <button type="button" class="fonte-botao active" data-fonte="medio">M</button>
                            <button type="button" class="fonte-botao" data-fonte="grande">G</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEÇÃO DE EXCLUSÃO DE CONTA -->
            <div class="conta-section">
                <div class="conta-danger">
                    <h4>Excluir Conta</h4>
                    <p>Ao excluir sua conta, todos os seus dados serão permanentemente removidos. Esta ação não pode ser desfeita.</p>
                    <a href="confirmar_exclusao.html" class="botao-perigo">Excluir Minha Conta</a>
                </div>
            </div>
        </div>
    </main>

    <script src="configuracoes.js"></script>
</body>
</html>