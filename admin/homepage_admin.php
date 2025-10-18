<?php
session_start();
include '../conexao.php';
include '../generos.php';
include '../verifica_admin.php';

// Verifica se é admin


// 🔍 FILTROS E PESQUISA
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$genero_filtro = isset($_GET['genero']) ? $_GET['genero'] : '';
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';
$status_filtro = isset($_GET['status']) ? $_GET['status'] : '';
$ordenar_por = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'data_publicacao_desc';

// Query base
$sql = "SELECT l.*, u.nome_usuario, u.foto_perfil, u.id as id_dono 
        FROM livros l 
        LEFT JOIN usuarios u ON l.id_usuario = u.id 
        WHERE 1=1"; // 1=1 permite adicionar condições dinamicamente

$params = [];
$types = "";

// Filtros
if (!empty($busca)) {
    $sql .= " AND (l.titulo LIKE ? OR l.autor LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
    $types .= "ss";
}

if (!empty($genero_filtro)) {
    $sql .= " AND l.genero = ?";
    $params[] = $genero_filtro;
    $types .= "s";
}

if (!empty($estado_filtro)) {
    $sql .= " AND l.estado = ?";
    $params[] = $estado_filtro;
    $types .= "s";
}

if (!empty($status_filtro)) {
    $sql .= " AND l.status = ?";
    $params[] = $status_filtro;
    $types .= "s";
}

// Ordenação
switch ($ordenar_por) {
    case 'titulo_asc': $sql .= " ORDER BY l.titulo ASC"; break;
    case 'titulo_desc': $sql .= " ORDER BY l.titulo DESC"; break;
    case 'autor_asc': $sql .= " ORDER BY l.autor ASC"; break;
    case 'autor_desc': $sql .= " ORDER BY l.autor DESC"; break;
    case 'data_publicacao_asc': $sql .= " ORDER BY l.data_publicacao ASC"; break;
    case 'data_publicacao_desc':
    default: $sql .= " ORDER BY l.data_publicacao DESC"; break;
}

// Executa a query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Homepage Admin</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../homepage.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
<header class="header">
    <a href="homepage_admin.php">
        <img src="../imagens/logo-trocatrocajk.png" alt="Logo" class="logo">
    </a>

    <div class="search-container">
        <form id="searchForm" method="GET" action="homepage_admin.php" style="display: flex; align-items: center; width: 100%;">
            <input type="text" name="busca" class="search-bar" placeholder="Pesquise livros" value="<?= htmlspecialchars($busca) ?>">
            <img src="../imagens/icone-filtro.svg" alt="Filtro" class="filter-icon" onclick="toggleFilter()" style="cursor:pointer; width:25px; margin-left:8px;">
            <button type="submit" style="display:none;"></button>
        </form>
    </div>

    <div class="icons">
        <button onclick="window.location.href='painel.php'" 
            style="padding:8px 15px; background:#007bff; color:#fff; border:none; border-radius:5px; cursor:pointer;">
            Painel Admin
        </button>
    </div>
</header>

<!-- 🔽 FILTROS -->
<div id="filterContainer" class="filter-container" style="display:none;">
    <form method="GET" action="homepage_admin.php">
        <input type="hidden" name="busca" value="<?= htmlspecialchars($busca) ?>">
        <div class="filter-row">
            <div class="filter-group">
                <label for="genero">Gênero</label>
                <select id="genero" name="genero" class="select2">
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
                <select id="estado" name="estado" class="select2">
                    <option value="">Todos os estados</option>
                    <option value="Novo" <?= $estado_filtro === 'Novo' ? 'selected' : '' ?>>Novo</option>
                    <option value="Seminovo" <?= $estado_filtro === 'Seminovo' ? 'selected' : '' ?>>Seminovo</option>
                    <option value="Usado" <?= $estado_filtro === 'Usado' ? 'selected' : '' ?>>Usado</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="select2">
                    <option value="">Todos os status</option>
                    <option value="disponivel" <?= $status_filtro === 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                    <option value="indisponivel" <?= $status_filtro === 'indisponivel' ? 'selected' : '' ?>>Indisponível</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="ordenar">Ordenar por</label>
                <select id="ordenar" name="ordenar" class="select2">
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

<main id="livrosContainer">
    <?php
    $total_livros = $result->num_rows;
    echo "<div class='livros-count'>$total_livros livro" . ($total_livros != 1 ? "s" : "") . " encontrado" . ($total_livros != 1 ? "s" : "") . "</div>";

    if ($result && $result->num_rows > 0):
        while ($livro = $result->fetch_assoc()):
            $usuario = !empty($livro['nome_usuario']) ? "@" . htmlspecialchars($livro['nome_usuario']) : "@usuarioTeste";
            $foto_perfil = !empty($livro['foto_perfil']) ? '../imagens/perfis/' . htmlspecialchars($livro['foto_perfil']) : '../imagens/icone-perfil.svg';
            $imagens = explode(",", $livro['imagens']);
            $primeiraImagem = $imagens[0];
            $imagensJson = json_encode(array_map(fn($img) => '../uploads/' . $img, $imagens));
    ?>
    <div class="card-livro" data-imagens='<?= $imagensJson ?>'>
        <div class="card-header">
            <div class="header-usuario">
                <img src="<?= $foto_perfil ?>" class="perfil-icon" alt="Perfil">
                <!-- 🔽 AQUI ESTÁ A CORREÇÃO - LINK PARA O PERFIL DO DONO DO LIVRO -->
                <a class="user" href="../perfil_usuario.php?id=<?= $livro['id_dono'] ?>" style="text-decoration: none; color: inherit;">
                    <?= $usuario ?>
                </a>
            </div>
        </div>

        <div class="imagem-container">
            <img src="../uploads/<?= htmlspecialchars($primeiraImagem) ?>" class="imagem-livro" alt="Capa do Livro">
        </div>

        <div class="info-livro">
            <p class="titulo"><strong>Título:</strong> <?= htmlspecialchars($livro['titulo']) ?></p>
            <p class="genero"><strong>Gênero:</strong> <?= htmlspecialchars($livro['genero']) ?></p>
            <p class="autor"><strong>Autor:</strong> <?= htmlspecialchars($livro['autor']) ?></p>
            <p class="estado"><strong>Estado:</strong> <?= htmlspecialchars($livro['estado']) ?></p>
            <p class="status"><strong>Status:</strong> <?= htmlspecialchars($livro['status']) ?></p>
        </div>
    </div>
    <?php endwhile;
    else:
        echo '<p>Nenhum livro encontrado com os filtros aplicados.
                    <button type="button" class="btn-filter btn-clear" onclick="clearFilters()">Limpar Filtros</button>
</p>';
    endif;

    if (isset($stmt)) $stmt->close();
    $conn->close();
    ?>
</main>

<script>
    function toggleFilter() {
        const container = document.getElementById('filterContainer');
        container.style.display = (container.style.display === 'none' || container.style.display === '') ? 'block' : 'none';
    }
    function clearFilters() {
        window.location.href = 'homepage_admin.php';
    }

    $(document).ready(function() {
        $('.select2').select2();
        
        // Evento de clique para abrir modal (se necessário)
        document.addEventListener('click', function(e) {
            // Impede que o modal abra quando clicar no link do usuário
            if (e.target.closest('a') && e.target.closest('a').classList.contains('user')) {
                return;
            }
            
            const card = e.target.closest('.card-livro');
            if (card) {
                // Sua função para mostrar detalhes do livro aqui
                // mostrarDetalhesLivro(card);
            }
        });
    });
</script>
</body>
</html>