<?php
include 'conexao.php';
include 'generos.php'; // Adicionei esta linha para os gêneros

session_start();

$id_logado = $_SESSION['id'] ?? null;

$foto_perfil_logado = null;
if ($id_logado) {
    $sql_foto = "SELECT foto_perfil FROM usuarios WHERE id = ?";
    $stmt_foto = $conn->prepare($sql_foto);
    $stmt_foto->bind_param("i", $id_logado);
    $stmt_foto->execute();
    $result_foto = $stmt_foto->get_result();
    $dados_foto = $result_foto->fetch_assoc();
    $foto_perfil_logado = $dados_foto['foto_perfil'] ?? null;
    $stmt_foto->close();
}

if (!isset($_GET['id'])) {
    echo "ID do usuário não fornecido.";
    exit;
}

$id_usuario = intval($_GET['id']);

// Buscar dados do usuário
$sql = "SELECT nome, sobrenome, nome_usuario, foto_perfil, instagram, whatsapp FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    echo "Usuário não encontrado.";
    exit;
}

// Buscar média e quantidade de avaliações
$sql_avaliacoes = "SELECT AVG(nota) AS media, COUNT(*) AS total FROM avaliacoes WHERE id_avaliado = ?";
$stmt_aval = $conn->prepare($sql_avaliacoes);
$stmt_aval->bind_param("i", $id_usuario);
$stmt_aval->execute();
$result_aval = $stmt_aval->get_result();
$avaliacao = $result_aval->fetch_assoc();
$stmt_aval->close();

$media = $avaliacao['media'] ? number_format($avaliacao['media'], 1, ',', '') : 0;
$total = $avaliacao['total'];

// 🔍 SISTEMA DE PESQUISA E FILTROS (IGUAL AO PERFIL.PHP)
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$genero_filtro = isset($_GET['genero']) ? $_GET['genero'] : '';
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';

$ordenar_por = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'data_publicacao_desc';

// Construir query para livros do usuário com filtros
$sql_livros = "SELECT id, titulo, autor, data_publicacao, imagens, genero, estado FROM livros WHERE id_usuario = ?";
$params = array($id_usuario);
$types = "i";

// Aplicar filtros (MESMA LÓGICA DO PERFIL.PHP)
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



// Aplicar ordenação (MESMA LÓGICA DO PERFIL.PHP)
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

// Executar query com filtros
$stmt_livros = $conn->prepare($sql_livros);

if (count($params) > 1) {
    $stmt_livros->bind_param($types, ...$params);
} else {
    $stmt_livros->bind_param($types, $id_usuario);
}

$stmt_livros->execute();
$livros = $stmt_livros->get_result();
$stmt_livros->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?= htmlspecialchars($usuario['nome_usuario']) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="perfil.css">
    
    <!-- 🔍 ESTILOS DOS FILTROS (IGUAL AO PERFIL.PHP) -->
    <style>
        .perfil-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }

        .perfil-social-links a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #333;
            margin: 4px 0;
        }

        .perfil-social-links img {
            width: 24px;
            height: 24px;
        }

        /* ⭐ Avaliação geral */
        .avaliacao-perfil {
            margin-top: 6px;
            font-size: 17px;
            color: #444;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .avaliacao-perfil strong {
            color: #f5a623;
            font-size: 19px;
        }

        .avaliacao-perfil span {
            color: #777;
            font-size: 15px;
        }

        /* 📦 Avaliações individuais */
        .avaliacoes-container {
            margin-top: 20px;
            background: #fff;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .avaliacao-card {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .avaliador-foto {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
        }

        .avaliador-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .avaliacao-nota {
            margin: 4px 0;
            color: #f5a623;
        }

        .avaliacao-comentario {
            font-style: italic;
            color: #555;
        }

        .avaliacao-fotos img {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            object-fit: cover;
            margin: 5px;
        }

        .ver-todas-btn {
            display: block;
            margin: 10px auto 0;
            padding: 8px 14px;
            background: #f0f0f0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .ver-todas-btn:hover {
            background: #ddd;
        }

        /* Popup */
        .popup-avaliacoes {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .popup-conteudo {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            width: 90%;
        }

        .popup-conteudo .fechar {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }

        /* 🔍 ESTILOS DOS FILTROS (IGUAL AO PERFIL.PHP) */
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
            <!-- 🔍 FORMULÁRIO DE BUSCA (IGUAL AO PERFIL.PHP) -->
            <form id="searchForm" method="GET" action="" style="display: flex; align-items: center; width: 100%;">
                <input type="hidden" name="id" value="<?= $id_usuario ?>">
                <input type="text" class="search-bar" name="busca" placeholder="Pesquise livros" value="<?= htmlspecialchars($busca) ?>">
                <button type="submit" style="background: none; border: none; cursor: pointer; margin-left: 5px;">
                    <!-- <img src="imagens/icone-lupa.svg" alt="Buscar" style="width: 20px; height: 20px;"> -->
                </button>
            </form>
            <img src="imagens/icone-filtro.svg" alt="Filtrar" class="filter-icon" onclick="toggleFilter()">
        </div>
        <div class="icons">
            <img src="imagens/icone-publicarlivro.svg" alt="Publicar livro" onclick="abrirPopup()">
            <img src="imagens/icone-listadedesejo.svg" alt="Lista de desejos" onclick="window.location.href='listadedesejo.php'">
            <img src="imagens/icone-mensagem.svg" alt="Trocas Solicitadas" onclick="window.location.href='trocas_solicitadas.php'">
            <div class="foto-perfil-container" onclick="window.location.href='perfil.php'">
                <img src="<?= $foto_perfil_logado ? 'imagens/perfis/' . htmlspecialchars($foto_perfil_logado) : 'imagens/icone-perfil.svg' ?>" 
                     alt="Perfil" 
                     class="perfil-icon" 
                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            </div>
        </div>
    </header>

    <!-- 🔍 CONTAINER DE FILTROS (IGUAL AO PERFIL.PHP) -->
    <div id="filterContainer" class="filter-container" style="display: none;">
        <form id="filterForm" method="GET" action="">
            <input type="hidden" name="id" value="<?= $id_usuario ?>">
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

    <!-- Conteúdo -->
    <div class="perfil-container">
        <div class="perfil-info">
            <img src="<?= $usuario['foto_perfil'] ? 'imagens/perfis/' . htmlspecialchars($usuario['foto_perfil']) : 'imagens/icone-perfil.svg' ?>" alt="Perfil" class="perfil-icon">
            <div class="perfil-details">
                <h2><?= htmlspecialchars($usuario['nome']) . ' ' . htmlspecialchars($usuario['sobrenome']) ?></h2>
                <p>@<?= htmlspecialchars($usuario['nome_usuario']) ?></p>

                <!-- ⭐ Avaliação geral -->
                <div class="avaliacao-perfil">
                    <?php if ($total > 0): ?>
                        <p>
                            ⭐ <strong><?= $media ?></strong> / 5 
                            <span>(<?= $total ?> <?= $total == 1 ? 'avaliação' : 'avaliações' ?>)</span>
                        </p>
                    <?php else: ?>
                        <p>⭐ Ainda sem avaliações</p>
                    <?php endif; ?>
                </div>

                <!-- Redes sociais -->
                <div class="perfil-social-links">
                    <?php if (!empty($usuario['instagram'])): ?>
                        <p>
                            <a href="https://instagram.com/<?= htmlspecialchars($usuario['instagram']) ?>" target="_blank">
                                <img src="imagens/icone-instagram.svg" alt="Instagram">
                                <span>@<?= htmlspecialchars($usuario['instagram']) ?></span>
                            </a>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($usuario['whatsapp'])): ?>
                        <p>
                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $usuario['whatsapp']) ?>" target="_blank">
                                <img src="imagens/icone-whatsapp.svg" alt="WhatsApp">
                                <span><?= htmlspecialchars($usuario['whatsapp']) ?></span>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 🧾 Avaliações Recentes -->
        <?php
        $sql_recent = "
            SELECT a.id, a.nota, a.comentario, a.data_avaliacao, u.nome_usuario, u.foto_perfil
            FROM avaliacoes a
            JOIN usuarios u ON a.id_avaliador = u.id
            WHERE a.id_avaliado = ?
            ORDER BY a.data_avaliacao DESC
            LIMIT 3";
        $stmt = $conn->prepare($sql_recent);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $avaliacoes = $stmt->get_result();
        ?>
        <div class="avaliacoes-container">
            <h3>Avaliações</h3>
            <?php if ($avaliacoes->num_rows > 0): ?>
                <?php while ($a = $avaliacoes->fetch_assoc()): ?>
                    <div class="avaliacao-card">
                        <div class="avaliador-info">
                            <img src="<?= $a['foto_perfil'] ? 'imagens/perfis/' . htmlspecialchars($a['foto_perfil']) : 'imagens/icone-perfil.svg' ?>" class="avaliador-foto">
                            <p><strong>@<?= htmlspecialchars($a['nome_usuario']) ?></strong></p>
                        </div>
                        <div class="avaliacao-nota"><?= str_repeat("⭐", $a['nota']) ?> <span class="data-avaliacao"><?= date("d/m/Y", strtotime($a['data_avaliacao'])) ?></span></div>
                        <?php if (!empty($a['comentario'])): ?>
                            <p class="avaliacao-comentario">"<?= htmlspecialchars($a['comentario']) ?>"</p>
                        <?php endif; ?>

                        <?php
                        $stmt_imgs = $conn->prepare("SELECT caminho_imagem FROM avaliacoes_imagens WHERE id_avaliacao = ?");
                        $stmt_imgs->bind_param("i", $a['id']);
                        $stmt_imgs->execute();
                        $imgs = $stmt_imgs->get_result();
                        ?>
                        <?php if ($imgs->num_rows > 0): ?>
                            <div class="avaliacao-fotos">
                                <?php while ($img = $imgs->fetch_assoc()): ?>
                                    <img src="uploads/avaliacoes/<?= htmlspecialchars($img['caminho_imagem']) ?>" class="foto-avaliacao">
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
                <button class="ver-todas-btn" onclick="abrirPopupAvaliacoes()">Ver todas as avaliações</button>
            <?php else: ?>
                <p>Ainda não há avaliações para este usuário.</p>
            <?php endif; ?>
        </div>

        <!-- 📚 Livros Publicados COM FILTROS -->
        <div class="livros-publicados">
            <h3>Livros Publicados</h3>

            <!-- 🔍 Informações sobre resultados (IGUAL AO PERFIL.PHP) -->
            <?php if ($busca || $genero_filtro || $estado_filtro ): ?>
                <div class="search-results-info">
                    <?php
                    $filtros_ativos = [];
                    if ($busca) $filtros_ativos[] = "busca: \"$busca\"";
                    if ($genero_filtro) $filtros_ativos[] = "gênero: $genero_filtro";
                    if ($estado_filtro) $filtros_ativos[] = "estado: $estado_filtro";
                    
                    
                    echo "Filtros ativos: " . implode(', ', $filtros_ativos);
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($livros->num_rows > 0): ?>
                <div>
                    <?php while ($livro = $livros->fetch_assoc()): ?>
                        <div class="livro-item">
                            <?php
                                $imagens = explode(',', $livro['imagens']);
                                $caminhoImagem = !empty($imagens[0]) ? 'uploads/' . $imagens[0] : 'imagens/sem-imagem.png';
                            ?>

                            <img src="<?= $caminhoImagem ?>" alt="Capa do livro" class="livro-capa" style="width:100px; height:auto; border-radius:5px;">

                            <div class="livro-info">
                                <strong><?= htmlspecialchars($livro['titulo']) ?></strong>
                                <br>
                                Autor: <?= htmlspecialchars($livro['autor']) ?><br>
                                Gênero: <?= htmlspecialchars($livro['genero']) ?><br>
                                Estado: <?= htmlspecialchars($livro['estado']) ?><br>
                                Publicado em: <?= date("d/m/Y", strtotime($livro['data_publicacao'])) ?><br>
                               
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Nenhum livro encontrado com os filtros aplicados.</p>
                <?php if ($busca || $genero_filtro || $estado_filtro ): ?>
                    <a href="?id=<?= $id_usuario ?>" class="btn-filter btn-clear">Limpar filtros</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Popup -->
    <div id="popupAvaliacoes" class="popup-avaliacoes">
        <div class="popup-conteudo">
            <span class="fechar" onclick="fecharPopupAvaliacoes()">&times;</span>
            <h2>Todas as Avaliações</h2>
            <div id="todasAvaliacoesConteudo"></div>
        </div>
    </div>

    <!-- 🔍 SCRIPTS DOS FILTROS (IGUAL AO PERFIL.PHP) -->
    <script>
        // Funções para filtros
        function toggleFilter() {
            const filterContainer = document.getElementById('filterContainer');
            filterContainer.style.display = filterContainer.style.display === 'none' ? 'block' : 'none';
        }

        function clearFilters() {
            window.location.href = '?id=<?= $id_usuario ?>';
        }

        // Busca em tempo real (opcional)
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            // Permite que o formulário seja submetido normalmente
        });

        // Funções do popup de avaliações
        function abrirPopupAvaliacoes() {
            const popup = document.getElementById('popupAvaliacoes');
            popup.style.display = 'flex';
            fetch('buscar_avaliacoes.php?id=<?= $id_usuario ?>')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('todasAvaliacoesConteudo').innerHTML = html;
                });
        }

        function fecharPopupAvaliacoes() {
            document.getElementById('popupAvaliacoes').style.display = 'none';
        }

        // Inicializar Select2 (se necessário)
        $(document).ready(function() {
            $('#genero').select2({
                placeholder: "Selecione um gênero",
                allowClear: true
            });
        });
    </script>
</body>
</html>