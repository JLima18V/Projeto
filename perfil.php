<?php
    session_start();
    include 'conexao.php';

    // Recuperar dados do usuário
    $sql = "SELECT email,instagram, whatsapp,  nome, sobrenome, nome_usuario, foto_perfil FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();

    // Verifica se os dados do usuário foram encontrados
    if ($usuario) {
        // Define os valores na sessão
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['sobrenome'] = $usuario['sobrenome'];
        $_SESSION['nome_usuario'] = $usuario['nome_usuario'];
        $_SESSION['foto_perfil'] = $usuario['foto_perfil'];
        $_SESSION['instagram'] = $usuario['instagram'];
        $_SESSION['whatsapp'] = $usuario['whatsapp'];

    } else {
        // Caso o usuário não seja encontrado, redirecione ou exiba uma mensagem
        echo "Usuário não encontrado.";
        exit;
    }

   if (isset($_POST['toggle_status_livro'])) {
    $livro_id = $_POST['livro_id'];
$novo_status = (isset($_POST['status']) && $_POST['status'] === 'disponivel') ? 'disponivel' : 'indisponivel';

    // Verificar se o livro pertence ao usuário
    $sql_check = "SELECT id FROM livros WHERE id = ? AND id_usuario = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $livro_id, $_SESSION['id']);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Atualizar status
        $sql_update = "UPDATE livros SET status = ? WHERE id = ? AND id_usuario = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sii", $novo_status, $livro_id, $_SESSION['id']);
        $stmt_update->execute();
        $stmt_update->close();
    }

    $stmt_check->close();
}



    // Recupera os livros publicados pelo usuário
    $sql_livros = "SELECT id, titulo, autor, data_publicacao, imagens, genero, estado, status FROM livros WHERE id_usuario = ? ORDER BY data_publicacao DESC";

    $stmt_livros = $conn->prepare($sql_livros);
    $stmt_livros->bind_param("i", $_SESSION['id']);
    $stmt_livros->execute();
    $result_livros = $stmt_livros->get_result();
    $stmt_livros->close();

    // Verifica se o formulário foi enviado para atualizar a foto
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['remover_foto'])) {
            // Remove a foto de perfil
            if (!empty($usuario['foto_perfil'])) {
                $caminho_foto = 'imagens/perfis/' . $usuario['foto_perfil'];
                if (file_exists($caminho_foto)) {
                    unlink($caminho_foto);
                }
                
                // Atualiza o banco de dados
                $sql = "UPDATE usuarios SET foto_perfil = NULL WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $_SESSION['id']);
                $stmt->execute();
                $stmt->close();
                
                $usuario['foto_perfil'] = null;
                $_SESSION['foto_perfil'] = null;
            }
        } elseif (isset($_FILES['foto_perfil'])) {
            $foto = $_FILES['foto_perfil'];

            // Verifica se o arquivo foi enviado com sucesso
            if ($foto['error'] == 0) {
                // Remove a foto antiga se existir
                if (!empty($usuario['foto_perfil'])) {
                    $caminho_antigo = 'imagens/perfis/' . $usuario['foto_perfil'];
                    if (file_exists($caminho_antigo)) {
                        unlink($caminho_antigo);
                    }
                }

                $extensao = pathinfo($foto['name'], PATHINFO_EXTENSION);
                $novo_nome = 'perfil_' . $_SESSION['id'] . '.' . $extensao;
                $diretorio = 'imagens/perfis/';

                // Verifica se a pasta existe, caso contrário, cria
                if (!is_dir($diretorio)) {
                    mkdir($diretorio, 0777, true);
                }

                // Move o arquivo para o diretório de perfil
                if (move_uploaded_file($foto['tmp_name'], $diretorio . $novo_nome)) {
                    // Atualiza o caminho da foto no banco de dados
                    $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $novo_nome, $_SESSION['id']);
                    $stmt->execute();
                    $stmt->close();
                    $usuario['foto_perfil'] = $novo_nome; // Atualiza a foto no array de dados
                    $_SESSION['foto_perfil'] = $novo_nome;
                } else {
                    echo "<script>alert('Erro ao enviar a foto!');</script>";
                }
            }
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="perfil.css">
        <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
        <title>Perfil</title>
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
                <input type="text" class="search-bar" placeholder="Pesquise livros">
                <img src="imagens/icone-filtro.svg" alt="Filtrar" class="filter-icon" onclick="toggleFilter()">
            </div>
            <div class="icons">
                <img src="imagens/icone-publicarlivro.svg" alt="Publicar livro" onclick="abrirPopup()">
                <img src="imagens/icone-listadedesejo.svg" alt="Lista de desejos" onclick="window.location.href='listadedesejo.php'">
                <img src="imagens/icone-mensagem.svg" alt="Trocas Solicitadas" onclick="window.location.href='trocas_solicitadas.php'">
                
                <!-- Ícone de perfil com dropdown -->
                <div class="profile-dropdown">
                    <div class="foto-perfil-container" onclick="abrirFotoPerfilPopup()">
                        <img src="<?= $usuario['foto_perfil'] ? 'imagens/perfis/' . $usuario['foto_perfil'] : 'imagens/icone-perfil.svg' ?>" 
                             alt="Perfil" 
                             class="perfil-icon" 
                             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div class="foto-perfil-overlay">
                            <span><?= $usuario['foto_perfil'] ? 'Alterar foto' : 'Adicionar foto' ?></span>
                        </div>
                    </div>
                    <div class="profile-dropdown-content">
                        <a href="editar_perfil.php">Editar Conta</a>
                        <a href="confirmar_saida.html">Sair</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Popup para gerenciar foto de perfil -->
        <div id="fotoPerfilPopup">
            <div class="foto-perfil-popup-content">
                <h3>Foto de Perfil</h3>
                <form id="fotoPerfilForm" method="POST" enctype="multipart/form-data">
                    <div class="foto-perfil-options">
                        <label for="fotoPerfilInput" class="foto-perfil-upload-btn">
                            Escolher Foto
                            <input type="file" id="fotoPerfilInput" name="foto_perfil" accept="image/*" style="display: none;" onchange="document.getElementById('fotoPerfilForm').submit()">
                        </label>
                        <?php if ($usuario['foto_perfil']): ?>
                            <button type="submit" name="remover_foto" class="foto-perfil-remove-btn">Remover Foto</button>
                        <?php endif; ?>
                        <button type="button" onclick="fecharFotoPerfilPopup()" class="foto-perfil-cancel-btn">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Popup de confirmação para alterar status -->
<div id="popupConfirmacao" class="popup-confirmacao">
    <div class="popup-content">
        <h3>Alterar Status</h3>
        <p>Deseja realmente marcar este livro como <span id="novoStatusTexto"></span>?</p>
        <div class="popup-buttons">
            <form id="formAlterarStatus" method="POST">
                <input type="hidden" name="alterar_status_livro" value="1">
                <input type="hidden" name="livro_id" id="livroIdInput">
                <input type="hidden" name="status" id="statusInput">
                <button type="submit" class="btn-confirmar">Sim</button>
            </form>
            <button type="button" class="btn-cancelar" onclick="fecharPopupConfirmacao()">Cancelar</button>
        </div>
    </div>
</div>


        <!-- POPUP EDITAR LIVRO -->
        <div id="popupOverlayEditar" class="popup-overlay">
            <div class="popup">
                <div class="popup-header">
                    <span class="fechar" onclick="fecharPopupEdicao()">
                        <img src="imagens/icone-voltar.png" alt="Fechar" class="fechar-imagem" />
                    </span>
                    <h2>Editar Livro</h2>
                </div>

                <form id="formEditarLivro" action="editar_livro.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_livro" id="id_livro_edit" />
                    
                    <div class="upload-area" onclick="document.getElementById('fileInputEditar').click()">
                        <p>Clique para adicionar/alterar imagens</p>
                        <input type="file" id="fileInputEditar" name="imagens[]" multiple accept="image/*" style="display: none;" />
                    </div>
                    <div id="previewContainerEditar" class="preview-container"></div>

                    <div class="input-container">
                        <input type="text" name="titulo" id="titulo_edit" placeholder="Título" required />
                    </div>
                    <div class="input-container">
                        <input type="text" name="autor" id="autor_edit" placeholder="Autor" required />
                    </div>
                    <div class="input-container">
                        <input list="genero_edit" name="genero" id="genero_edit" placeholder="Gênero" required />
                        <datalist id="genero_edit">
                            <option value="Romance"></option>
                            <option value="Terror"></option>
                            <option value="Suspense"></option>
                            <option value="Ficção Científica"></option>
                            <option value="Biografia"></option>
                            <option value="Drama"></option>
                            <option value="Aventura"></option>
                            <option value="Outros"></option>
                        </datalist>
                    </div>
                    <div class="input-container">
                        <select name="estado" id="estado_edit" required>
                            <option value="">Estado</option>
                            <option value="Novo">Novo</option>
                            <option value="Seminovo">Seminovo</option>
                            <option value="Usado">Usado</option>
                        </select>
                    </div>

                    <button type="submit" class="postar">Salvar Alterações</button>
                </form>
            </div>
        </div>

        <!-- Página de Perfil -->
        <div class="perfil-container">
            <!-- Área do Perfil -->
            <div class="perfil-info">
                <!-- Exibe a foto de perfil -->
                <div class="foto-perfil-container" onclick="abrirFotoPerfilPopup()">
                    <img src="<?= $usuario['foto_perfil'] ? 'imagens/perfis/' . $usuario['foto_perfil'] : 'imagens/icone-perfil.svg' ?>" alt="Perfil" class="perfil-icon">
                    <div class="foto-perfil-overlay">
                        <span><?= $usuario['foto_perfil'] ? 'Alterar foto' : 'Adicionar foto' ?></span>
                    </div>
                </div>
                <div class="perfil-details">
                    <h2>
                    <?= (isset($_SESSION['nome']) && isset($_SESSION['sobrenome']) ? htmlspecialchars($_SESSION['nome'] . ' ' . $_SESSION['sobrenome'])   : 'Nome não definido') ?>
                    </h2>

                    <p>@<?= isset($_SESSION['nome_usuario']) ? htmlspecialchars($_SESSION['nome_usuario']) : 'Usuário não definido' ?></p>
                    <div class="perfil-social-links">
                        <?php if (!empty($usuario['instagram'])): ?>
                            <a href="https://instagram.com/<?= htmlspecialchars($usuario['instagram']) ?>" target="_blank" class="social-link">
                                <img src="imagens/icone-instagram.svg" alt="Instagram" style="width: 24px; vertical-align: middle;">
                                <span>@<?= htmlspecialchars($usuario['instagram']) ?></span>
                            </a> </p>
                        <?php endif; ?>

                        <?php if (!empty($usuario['whatsapp'])): ?>
                            <p>  <a href="https://wa.me/<?= preg_replace('/\D/', '', $usuario['whatsapp']) ?>" target="_blank" class="social-link">
                                <img src="imagens/icone-whatsapp.svg" alt="WhatsApp" style="width: 24px; vertical-align: middle;">
                                <span><?= htmlspecialchars($usuario['whatsapp']) ?></span>
                            </a> </p>
                        <?php endif; ?>
                        <a href="minhas_trocas.php">Minhas Trocas</a>
                    </div>
                </div>
            </div>

            <!-- Livros Publicados -->
            <?php
// Detecta se o usuário veio do modo troca
$modoTroca = isset($_GET['modo_troca']) && $_GET['modo_troca'] == 1;
$idLivroDesejado = isset($_GET['id_livro_desejado']) ? intval($_GET['id_livro_desejado']) : null;
?>

<div class="livros-publicados">
    <h3>Livros Publicados</h3>

    <?php if ($result_livros->num_rows > 0): ?>
        <?php if ($modoTroca): ?>
            <form action="processa_troca.php" method="POST">
                <input type="hidden" name="id_livro_solicitado" value="<?= $idLivroDesejado ?>">
        <?php endif; ?>

        <div>
            <?php while ($livro = $result_livros->fetch_assoc()): ?>
                <div class="livro-item">
                    <?php
                        $imagens = explode(',', $livro['imagens']);
                        $caminhoImagem = !empty($imagens[0]) ? 'uploads/' . $imagens[0] : 'imagens/sem-imagem.png';
                    ?>

                    <img src="<?= $caminhoImagem ?>" alt="Capa do livro" class="livro-capa" style="width:100px; height:auto; border-radius:5px;">

                    <div class="livro-info">
                        <?php if ($modoTroca): ?>
                            <label>
                                <input type="checkbox" name="livros_oferecidos[]" value="<?= $livro['id'] ?>">
                                <strong><?= htmlspecialchars($livro['titulo']) ?></strong>
                            </label><br>
                        <?php else: ?>
                            <strong><?= htmlspecialchars($livro['titulo']) ?></strong><br>
                        <?php endif; ?>

                        Autor: <?= htmlspecialchars($livro['autor']) ?><br>
                        Gênero: <?= htmlspecialchars($livro['genero']) ?><br>
                        Estado: <?= htmlspecialchars($livro['estado']) ?><br>
                        Publicado em: <?= date("d/m/Y", strtotime($livro['data_publicacao'])) ?>
                    </div>

                    <?php if (!$modoTroca): ?>
    <div class="livro-actions">
        <button type="button" class="btn-action btn-editar"
            onclick="abrirPopupEdicao(event, <?= $livro['id'] ?>, '<?= htmlspecialchars($livro['titulo']) ?>', '<?= htmlspecialchars($livro['autor']) ?>', '<?= htmlspecialchars($livro['genero']) ?>', '<?= htmlspecialchars($livro['estado']) ?>', '<?= htmlspecialchars($livro['imagens']) ?>')">
            Editar
        </button>

     

        <!-- Checkbox toggle -->
        <form method="POST" style="display:inline;">
            <input type="hidden" name="toggle_status_livro" value="1">
            <input type="hidden" name="livro_id" value="<?= $livro['id'] ?>">
            <input type="hidden" name="status" value="indisponivel">
            <label>
                <input type="checkbox" name="status" value="disponivel"
                    onchange="this.form.submit()" <?= $livro['status'] === 'disponivel' ? 'checked' : '' ?>>
                Disponível
            </label>
        </form>
    </div>
<?php endif; ?>

                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($modoTroca): ?>
            Selecione o(s) livro(s) que
            <button type="submit" class="postar" style="margin-top:15px;">Enviar Solicitação de Troca</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <p>Você ainda não publicou nenhum livro.</p>
    <?php endif; ?>
<?php
if (isset($_GET['status']) && $_GET['status'] == 'sucesso') {
    echo "<p style='color: green;'>Solicitação de troca enviada com sucesso!</p>";
}
?>
</div>


        <!-- Form oculto para deletar livro -->
        <form id="formDeletarLivro" method="POST" style="display: none;">
            <input type="hidden" name="deletar_livro" value="1">
            <input type="hidden" name="livro_id" id="livroIdParaDeletar">
        </form>

        <!-- Scripts -->
        <script>
            let livroIdParaDeletar = null;

            function abrirFotoPerfilPopup() {
                document.getElementById("fotoPerfilPopup").style.display = "flex";
            }
            
            function fecharFotoPerfilPopup() {
                document.getElementById("fotoPerfilPopup").style.display = "none";
            }
            
            function abrirPopupConfirmacao(livroId) {
                livroIdParaDeletar = livroId;
                document.getElementById("popupConfirmacao").style.display = "flex";
            }
            
            function fecharPopupConfirmacao() {
                document.getElementById("popupConfirmacao").style.display = "none";
                livroIdParaDeletar = null;
            }
            
            function confirmarExclusao() {
                if (livroIdParaDeletar) {
                    document.getElementById("livroIdParaDeletar").value = livroIdParaDeletar;
                    document.getElementById("formDeletarLivro").submit();
                }
            }
            
            // Fechar popup ao clicar fora
            document.getElementById('fotoPerfilPopup').addEventListener('click', function(e) {
                if (e.target === this) {
                    fecharFotoPerfilPopup();
                }
            });

            document.getElementById('popupConfirmacao').addEventListener('click', function(e) {
                if (e.target === this) {
                    fecharPopupConfirmacao();
                }
            });

            // Funções para o popup de edição
            function abrirPopupEdicao(event, idLivro, titulo, autor, genero, estado, imagens) {
                event.stopPropagation();
                
                // Preencher campos do formulário
                document.getElementById('id_livro_edit').value = idLivro;
                document.getElementById('titulo_edit').value = titulo;
                document.getElementById('autor_edit').value = autor;
                document.getElementById('genero_edit').value = genero;
                document.getElementById('estado_edit').value = estado;

                // Carregar imagens no preview
                const previewContainer = document.getElementById('previewContainerEditar');
                previewContainer.innerHTML = ''; // limpa previews antigos

                if (imagens) {
                    const imagensArray = imagens.split(',');
                    imagensArray.forEach((img) => {
                        if(img.trim() !== '') {
                            const imgElem = document.createElement('img');
                            imgElem.src = 'uploads/' + img.trim();
                            imgElem.title = 'Clique para remover essa imagem';
                            imgElem.addEventListener('click', () => {
                                imgElem.remove();
                            });
                            previewContainer.appendChild(imgElem);
                        }
                    });
                }

                // Abrir popup
                document.getElementById('popupOverlayEditar').style.display = 'flex';

                // Preparar input file para múltiplas imagens, ao escolher atualiza preview
                const fileInput = document.getElementById('fileInputEditar');
                fileInput.value = ''; // limpa seleção anterior
                fileInput.onchange = () => {
                    // Ao selecionar novas imagens, substituir previews (as antigas removidas)
                    previewContainer.innerHTML = '';
                    Array.from(fileInput.files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const imgElem = document.createElement('img');
                            imgElem.src = e.target.result;
                            imgElem.title = 'Clique para remover essa imagem';
                            imgElem.addEventListener('click', () => {
                                imgElem.remove();
                            });
                            previewContainer.appendChild(imgElem);
                        };
                        reader.readAsDataURL(file);
                    });
                };
            }

            function fecharPopupEdicao() {
                document.getElementById('popupOverlayEditar').style.display = 'none';
            }

            // Fecha popup ao clicar fora da caixa
            document.getElementById('popupOverlayEditar').addEventListener('click', function(e) {
                if(e.target === this) fecharPopupEdicao();
            });

            // Restante dos scripts permanece igual
            function abrirPopup() {
                document.getElementById("popupOverlay").style.display = "flex";
            }
            
            function fecharPopup() {
                document.getElementById("popupOverlay").style.display = "none";
            }
        </script>   
    </body>
    </html>