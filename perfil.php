
<?php
    session_start();
    include 'conexao.php';
    include 'generos.php';

    
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

    // Parâmetros de busca e filtro
    $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
    $genero_filtro = isset($_GET['genero']) ? $_GET['genero'] : '';
    $estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';
    $status_filtro = isset($_GET['status']) ? $_GET['status'] : '';
    $ordenar_por = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'data_publicacao_desc';

    // Construir query base para livros
    $sql_livros = "SELECT id, titulo, autor, data_publicacao, imagens, genero, estado, status FROM livros WHERE id_usuario = ?";
    $params = array($_SESSION['id']);
    $types = "i";

    // Aplicar filtros
    if (!empty($busca)) {
        $sql_livros .= " AND (titulo LIKE ? OR autor LIKE ?)";
        $params[] = "%$busca%";
        $params[] = "%$busca%";
        $types .= "ss";
    }

    if (!empty($genero_filtro)) {
        $sql_livros .= " AND genero = ?";
        $params[] = $genero_filtro;
        $types .= "s";
    }

    if (!empty($estado_filtro)) {
        $sql_livros .= " AND estado = ?";
        $params[] = $estado_filtro;
        $types .= "s";
    }

    if (!empty($status_filtro)) {
        $sql_livros .= " AND status = ?";
        $params[] = $status_filtro;
        $types .= "s";
    }

    // Aplicar ordenação
    switch ($ordenar_por) {
        case 'titulo_asc':
            $sql_livros .= " ORDER BY titulo ASC";
            break;
        case 'titulo_desc':
            $sql_livros .= " ORDER BY titulo DESC";
            break;
        case 'autor_asc':
            $sql_livros .= " ORDER BY autor ASC";
            break;
        case 'autor_desc':
            $sql_livros .= " ORDER BY autor DESC";
            break;
        case 'data_publicacao_asc':
            $sql_livros .= " ORDER BY data_publicacao ASC";
            break;
        case 'data_publicacao_desc':
        default:
            $sql_livros .= " ORDER BY data_publicacao DESC";
            break;
    }

    // Executar query
    $stmt_livros = $conn->prepare($sql_livros);
    
    if (count($params) > 1) {
        $stmt_livros->bind_param($types, ...$params);
    } else {
        $stmt_livros->bind_param($types, $_SESSION['id']);
    }
    
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
        .filter-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
            align-items: end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 45px;
            font-weight: bold;
            color: #495057;
            font-size: 14px;
        }
        
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-filter {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn-apply {
            background: #007bff;
            color: white;
        }
        
        .btn-apply:hover {
            background: #0056b3;
        }
        
        .btn-clear {
            background: #6c757d;
            color: white;
        }
        
        .btn-clear:hover {
            background: #545b62;
        }
        
        .search-results-info {
            margin-bottom: 15px;
            color: #6c757d;
            font-style: italic;
        }
        </style>

        <!-- jQuery (necessário pro Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- CSS e JS do Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    </head>
    <body>
        <!-- Cabeçalho -->
        <header class="header">
            <a href="homepage.php">
                <img src="imagens/logo-trocatrocajk.png" alt="Logo" class="logo">
            </a>
            <div class="search-container">
                <form id="searchForm" method="GET" action="perfil.php" style="display: flex; align-items: center; width: 100%;">
                    <input type="text" class="search-bar" name="busca" placeholder="Pesquise seus livros..." value="<?= htmlspecialchars($busca) ?>">
                    <button type="submit" style="background: none; border: none; cursor: pointer; margin-left: 5px;">
                        <!-- <img src="imagens/icone-lupa.svg" alt="Buscar" style="width: 20px; height: 20px;"> -->
                    </button>
                </form>
                <img src="imagens/icone-filtro.svg" alt="Filtrar" class="filter-icon" onclick="toggleFilter()">
            </div>
            <div class="icons">
                <!-- <img src="imagens/icone-publicarlivro.svg" alt="Publicar livro" onclick="abrirPopup()"> -->
                <img src="imagens/icone-listadedesejo.svg" alt="Lista de desejos" onclick="window.location.href='listadedesejo.php'">
                <img src="imagens/icone-mensagem.svg" alt="Trocas Solicitadas" onclick="window.location.href='trocas_solicitadas.php'">
                
                <!-- Ícone de perfil com dropdown -->
                <div class="profile-trigger">
                    <img src="<?= $usuario['foto_perfil'] ? 'imagens/perfis/' . $usuario['foto_perfil'] : 'imagens/icone-perfil.svg' ?>" 
                         alt="Perfil" 
                         class="perfil-icon" 
                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
                         onclick="toggleSidebar()">
                </div>
            </div>
        </header>

        <!-- Container de Filtros -->
        <div id="filterContainer" class="filter-container" style="display: none;">
            <form id="filterForm" method="GET" action="perfil.php">
                <input type="hidden" name="busca" value="<?= htmlspecialchars($busca) ?>">
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="genero">Gênero</label>
                        <select id="genero" name="genero">
                            <option value="">Todos os gêneros</option>
                            <?php foreach ($generos as $genero): ?>
                                <option value="<?= htmlspecialchars($genero) ?>" <?= $genero_filtro === $genero ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($genero) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado">
                            <option value="">Todos os estados</option>
                            <option value="Novo" <?= $estado_filtro === 'Novo' ? 'selected' : '' ?>>Novo</option>
                            <option value="Seminovo" <?= $estado_filtro === 'Seminovo' ? 'selected' : '' ?>>Seminovo</option>
                            <option value="Usado" <?= $estado_filtro === 'Usado' ? 'selected' : '' ?>>Usado</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">Todos os status</option>
                            <option value="disponivel" <?= $status_filtro === 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                            <option value="indisponivel" <?= $status_filtro === 'indisponivel' ? 'selected' : '' ?>>Indisponível</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="ordenar">Ordenar por</label>
                        <select id="ordenar" name="ordenar">
                            <option value="data_publicacao_desc" <?= $ordenar_por === 'data_publicacao_desc' ? 'selected' : '' ?>>Data (Mais Recente)</option>
                            <option value="data_publicacao_asc" <?= $ordenar_por === 'data_publicacao_asc' ? 'selected' : '' ?>>Data (Mais Antiga)</option>
                            <option value="titulo_asc" <?= $ordenar_por === 'titulo_asc' ? 'selected' : '' ?>>Título (A-Z)</option>
                            <option value="titulo_desc" <?= $ordenar_por === 'titulo_desc' ? 'selected' : '' ?>>Título (Z-A)</option>
                            <option value="autor_asc" <?= $ordenar_por === 'autor_asc' ? 'selected' : '' ?>>Autor (A-Z)</option>
                            <option value="autor_desc" <?= $ordenar_por === 'autor_desc' ? 'selected' : '' ?>>Autor (Z-A)</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn-filter btn-apply">Aplicar Filtros</button>
                    <button type="button" class="btn-filter btn-clear" onclick="clearFilters()">Limpar Filtros</button>
                </div>
            </form>
        </div>

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
                        <select id="genero_edit" name="genero" required style="width:100%;">
                            <option value="">Selecione um gênero</option>
                            <?php foreach ($generos as $genero): ?>
                                <option value="<?= htmlspecialchars($genero) ?>">
                                    <?= htmlspecialchars($genero) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                       <?php
                    // Média de avaliações e quantidade
                    $id_logado = $_SESSION['id'];

                    $sql_avaliacoes = "
                        SELECT 
                            ROUND(AVG(nota), 1) AS media_avaliacao,
                            COUNT(nota) AS total_avaliacoes
                        FROM avaliacoes
                        WHERE id_avaliado = ?
                    ";
                    $stmt = $conn->prepare($sql_avaliacoes);
                    $stmt->bind_param("i", $id_logado);
                    $stmt->execute();
                    $result_avaliacao = $stmt->get_result();
                    $avaliacao = $result_avaliacao->fetch_assoc();
                    $stmt->close();

                    $media = $avaliacao['media_avaliacao'] ?? 0;
                    $total = $avaliacao['total_avaliacoes'] ?? 0;

                    ?>

                    <p style="margin-top: 5px; font-size: 16px; color: #555;">
                        ⭐ <?= number_format($media, 1, ',', '.') ?> / 5 
                        (<?= $total ?> <?= $total == 1 ? 'avaliação' : 'avaliações' ?>)
                    </p>
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

    <!-- Informações sobre resultados -->
    <?php if ($busca || $genero_filtro || $estado_filtro || $status_filtro): ?>
        <div class="search-results-info">
            <?php
            $filtros_ativos = [];
            if ($busca) $filtros_ativos[] = "busca: \"$busca\"";
            if ($genero_filtro) $filtros_ativos[] = "gênero: $genero_filtro";
            if ($estado_filtro) $filtros_ativos[] = "estado: $estado_filtro";
            if ($status_filtro) $filtros_ativos[] = "status: " . ($status_filtro === 'disponivel' ? 'disponível' : 'indisponível');
            
            echo "Filtros ativos: " . implode(', ', $filtros_ativos);
            ?>
        </div>
    <?php endif; ?>

    <?php if ($result_livros->num_rows > 0): ?>
        <?php if ($modoTroca): ?>
            <form action="processa_troca.php" method="POST">
                <input type="hidden" name="id_livro_solicitado" value="<?= $idLivroDesejado ?>">
                <p class="instrucao-troca">Selecione o(s) livro(s) que deseja oferecer:</p>
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
                            <label class="checkbox-troca">
                                <input type="checkbox" name="livros_oferecidos[]" value="<?= $livro['id'] ?>">
                                <strong><?= htmlspecialchars($livro['titulo']) ?></strong>
                            </label>
                        <?php else: ?>
                            <strong><?= htmlspecialchars($livro['titulo']) ?></strong>
                        <?php endif; ?>
                        <br>
                        Autor: <?= htmlspecialchars($livro['autor']) ?><br>
                        Gênero: <?= htmlspecialchars($livro['genero']) ?><br>
                        Estado: <?= htmlspecialchars($livro['estado']) ?><br>
                        Publicado em: <?= date("d/m/Y", strtotime($livro['data_publicacao'])) ?><br>
                        Status: <span class="status-<?= $livro['status'] ?>"><?= htmlspecialchars($livro['status']) ?></span>

                        <?php if (!$modoTroca): ?>
                        <div class="livro-acoes">
                            <!-- Botão Editar -->
                            <button onclick="abrirPopupEdicao(event, 
                                <?= $livro['id'] ?>, 
                                '<?= htmlspecialchars(addslashes($livro['titulo'])) ?>', 
                                '<?= htmlspecialchars(addslashes($livro['autor'])) ?>', 
                                '<?= htmlspecialchars(addslashes($livro['genero'])) ?>', 
                                '<?= htmlspecialchars(addslashes($livro['estado'])) ?>', 
                                '<?= htmlspecialchars(addslashes($livro['imagens'])) ?>')" 
                                class="btn-action btn-editar">
                                Editar
                            </button>

                            <!-- Botão Alternar Disponibilidade -->
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="toggle_status_livro" value="1">
                                <input type="hidden" name="livro_id" value="<?= $livro['id'] ?>">
                                <input type="hidden" name="status" value="<?= $livro['status'] === 'disponivel' ? 'indisponivel' : 'disponivel' ?>">
                                <button type="submit" class="btn-action <?= $livro['status'] === 'disponivel' ? 'btn-indisponivel' : 'btn-disponivel' ?>">
                                    <?= $livro['status'] === 'disponivel' ? 'Marcar Indisponível' : 'Marcar Disponível' ?>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($modoTroca): ?>
            <button type="submit" class="postar" style="margin-top:15px;">Enviar Solicitação de Troca</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <p>Nenhum livro encontrado com os filtros aplicados.</p>
        <?php if ($busca || $genero_filtro || $estado_filtro || $status_filtro): ?>
            <a href="perfil.php" class="btn-filter btn-clear">Limpar filtros</a>
        <?php endif; ?>
    <?php endif; ?>
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

                // Initialize Select2
                $('#genero_edit').select2({
                    placeholder: "Selecione um gênero",
                    allowClear: true
                });
            }

            function fecharPopupEdicao() {
                document.getElementById('popupOverlayEditar').style.display = 'none';
            }

            // Fecha popup ao clicar fora da caixa
            document.getElementById('popupOverlayEditar').addEventListener('click', function(e) {
                if(e.target === this) fecharPopupEdicao();
            });

            // Funções para filtros
            function toggleFilter() {
                const filterContainer = document.getElementById('filterContainer');
                filterContainer.style.display = filterContainer.style.display === 'none' ? 'block' : 'none';
            }

            function clearFilters() {
                window.location.href = 'perfil.php';
            }

            // Busca em tempo real (opcional)
            document.getElementById('searchForm').addEventListener('submit', function(e) {
                // Permite que o formulário seja submetido normalmente
            });

            // Restante dos scripts permanece igual
            function abrirPopup() {
                document.getElementById("popupOverlay").style.display = "flex";
            }
            
            function fecharPopup() {
                document.getElementById("popupOverlay").style.display = "none";
            }
        </script>   

        <script>
// Adiciona interatividade aos itens de seleção de livro
document.addEventListener('DOMContentLoaded', function() {
    const livroItems = document.querySelectorAll('.livro-item');
    
    livroItems.forEach(item => {
        const checkbox = item.querySelector('input[type="checkbox"]');
        if (checkbox) {
            // Adiciona classe para estilização
            item.classList.add('selecao-troca');
            
            // Atualiza visual quando checkbox é alterado
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    item.classList.add('selecionado');
                } else {
                    item.classList.remove('selecionado');
                }
            });

            // Permite clicar no card inteiro para selecionar
            item.addEventListener('click', function(e) {
                if (e.target !== checkbox && !checkbox.checked) {
                    checkbox.checked = true;
                    item.classList.add('selecionado');
                }
            });
        }
    });
});

</script>

<?php if(isset($_SESSION['mensagem'])): ?>
    <div class="mensagem-sucesso">
        <?php 
        echo $_SESSION['mensagem'];
        unset($_SESSION['mensagem']); 
        ?>
    </div>
<?php endif; ?>

<div class="sidebar-overlay" onclick="toggleSidebar()"></div>
<div class="sidebar-menu">
    <button class="close-sidebar" onclick="toggleSidebar()">×</button>
    <div class="sidebar-header">
        <img src="<?= $usuario['foto_perfil'] ? 'imagens/perfis/' . $usuario['foto_perfil'] : 'imagens/icone-perfil.svg' ?>" 
             alt="Perfil">
        <div class="sidebar-header-info">
            <h3><?= htmlspecialchars($_SESSION['nome'] . ' ' . $_SESSION['sobrenome']) ?></h3>
            <p>@<?= htmlspecialchars($_SESSION['nome_usuario']) ?></p>
         

        </div>
    </div>
    <ul class="sidebar-menu-items">
        <li><a href="editar_perfil.php"><img src="imagens/icone-editar.svg" alt="">Editar Perfil</a></li>
        <li><a href="minhas_trocas.php"><img src="imagens/icone-troca.svg" alt="">Minhas Trocas</a></li>
        <li><a href="#" onclick="abrirFotoPerfilPopup()"><img src="imagens/icone-foto.svg" alt="">Alterar Foto</a></li>
        <li><a href="confirmar_saida.html"><img src="imagens/icone-sair.svg" alt="">Sair da Conta</a></li>
        <!-- Inclua a biblioteca de ícones Bootstrap Icons no <head> do seu HTML -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Ícone de sair (logout) -->
<i class="bi bi-box-arrow-right"></i> Sair

    </ul>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar-menu');
    const overlay = document.querySelector('.sidebar-overlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}
</script>
    </body>
    </html>
