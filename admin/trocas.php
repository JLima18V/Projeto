<?php
session_start();
include '../conexao.php';
include '../verifica_admin.php';
// if (!isset($_SESSION['admin'])) {
//     header("Location: login.php");
//     exit;
// }

// Ações para as trocas
if (isset($_GET['cancelar'])) {
    $id_troca = intval($_GET['cancelar']);
    
    if (mysqli_query($conn, "UPDATE trocas SET status = 'recusada' WHERE id = $id_troca")) {
        $_SESSION['flash_message'] = 'Troca cancelada com sucesso!';
        $_SESSION['flash_type'] = 'success';
    } else {
        $_SESSION['flash_message'] = 'Erro ao cancelar troca!';
        $_SESSION['flash_type'] = 'error';
    }
    header("Location: trocas.php");
    exit;
}

if (isset($_GET['finalizar'])) {
    $id_troca = intval($_GET['finalizar']);
    
    // Iniciar transação para garantir consistência
    mysqli_begin_transaction($conn);
    
    try {
        // Buscar informações da troca
        $troca_info = mysqli_fetch_assoc(mysqli_query($conn, 
            "SELECT * FROM trocas WHERE id = $id_troca"));
        
        if ($troca_info) {
            // Atualizar status da troca para concluída
            mysqli_query($conn, "UPDATE trocas SET status = 'concluída', data_status = NOW() WHERE id = $id_troca");
            
            // Marcar livros como indisponíveis
            // Livro solicitado
            mysqli_query($conn, "UPDATE livros SET status = 'indisponivel' WHERE id = {$troca_info['id_livro_solicitado']}");
            
            // Livros oferecidos
            $livros_oferecidos = mysqli_query($conn, "SELECT id_livro_oferecido FROM trocas_livros_oferecidos WHERE id_troca = $id_troca");
            while ($livro = mysqli_fetch_assoc($livros_oferecidos)) {
                mysqli_query($conn, "UPDATE livros SET status = 'indisponivel' WHERE id = {$livro['id_livro_oferecido']}");
            }
            
            mysqli_commit($conn);
            $_SESSION['flash_message'] = 'Troca finalizada com sucesso!';
            $_SESSION['flash_type'] = 'success';
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['flash_message'] = 'Erro ao finalizar troca!';
        $_SESSION['flash_type'] = 'error';
    }
    
    header("Location: trocas.php");
    exit;
}

// Busca
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$status_filtro = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT t.*, 
               us.nome_usuario as solicitante_nome,
               us.email as solicitante_email,
               ur.nome_usuario as receptor_nome, 
               ur.email as receptor_email,
               ls.titulo as livro_solicitado_titulo,
               ls.autor as livro_solicitado_autor,
               ls.id as livro_solicitado_id,
               GROUP_CONCAT(DISTINCT lo.titulo SEPARATOR '|||') as livros_oferecidos_titulos,
               GROUP_CONCAT(DISTINCT lo.autor SEPARATOR '|||') as livros_oferecidos_autores,
               GROUP_CONCAT(DISTINCT lo.id SEPARATOR ',') as livros_oferecidos_ids,
               COUNT(DISTINCT tlo.id_livro_oferecido) as total_livros_oferecidos
        FROM trocas t
        LEFT JOIN usuarios us ON t.id_solicitante = us.id
        LEFT JOIN usuarios ur ON t.id_receptor = ur.id
        LEFT JOIN livros ls ON t.id_livro_solicitado = ls.id
        LEFT JOIN trocas_livros_oferecidos tlo ON t.id = tlo.id_troca
        LEFT JOIN livros lo ON tlo.id_livro_oferecido = lo.id
        WHERE 1=1";

if ($busca != '') {
    $sql .= " AND (us.nome_usuario LIKE '%$busca%' 
                OR ur.nome_usuario LIKE '%$busca%'
                OR ls.titulo LIKE '%$busca%'
                OR lo.titulo LIKE '%$busca%')";
}

if ($status_filtro != '') {
    $sql .= " AND t.status = '$status_filtro'";
}

$sql .= " GROUP BY t.id
          ORDER BY t.data_solicitacao DESC";

$resultado = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Trocas</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --info-color: #17a2b8;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            padding: 20px;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .header h1 {
            color: var(--secondary-color);
        }
        
        .filters-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 14px;
        }
        
        .search-input, .filter-select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
        }
        
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status.pendente {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status.aceita {
            background-color: #e8f5e9;
            color: var(--success-color);
        }
        
        .status.recusada {
            background-color: #ffebee;
            color: var(--accent-color);
        }
        
        .status.concluída {
            background-color: #e3f2fd;
            color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
        }
        
        .btn-danger {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-info {
            background-color: var(--info-color);
            color: white;
        }
        
        .btn-info:hover {
            background-color: #138496;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark-color);
        }

        .user-email {
            font-size: 0.8rem;
            color: #666;
        }

        .livro-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .livro-titulo {
            font-weight: 600;
            color: var(--dark-color);
        }

        .livro-autor {
            font-size: 0.8rem;
            color: #666;
            font-style: italic;
        }

        .troca-info {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 15px;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 10px 0;
        }

        .troca-arrow {
            text-align: center;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .livros-oferecidos {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .livro-oferecido {
            padding: 5px;
            background: white;
            border-radius: 4px;
            border-left: 3px solid var(--warning-color);
        }

        .confirmacoes {
            display: flex;
            gap: 10px;
            margin-top: 5px;
            font-size: 0.8rem;
        }

        .confirmacao {
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: bold;
        }

        .confirmacao.sim {
            background-color: #e8f5e9;
            color: var(--success-color);
        }

        .confirmacao.nao {
            background-color: #ffebee;
            color: var(--accent-color);
        }

        /* Flash Messages */
        .flash-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideIn 0.3s ease-out;
        }

        .flash-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .flash-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .flash-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: inherit;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-sync-alt"></i> Gerenciar Trocas</h1>
            <a href="painel.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="flash-message flash-<?php echo $_SESSION['flash_type']; ?>">
                <span><?php echo $_SESSION['flash_message']; ?></span>
                <button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
            <?php 
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            ?>
        <?php endif; ?>

        <div class="filters-container">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="busca">Buscar</label>
                    <input type="text" name="busca" class="search-input" placeholder="Buscar por usuário ou livro..." value="<?= htmlspecialchars($busca) ?>">
                </div>
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select name="status" class="filter-select">
                        <option value="">Todos os status</option>
                        <option value="pendente" <?= $status_filtro === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="aceita" <?= $status_filtro === 'aceita' ? 'selected' : '' ?>>Aceita</option>
                        <option value="recusada" <?= $status_filtro === 'recusada' ? 'selected' : '' ?>>Recusada</option>
                        <option value="concluída" <?= $status_filtro === 'concluída' ? 'selected' : '' ?>>Concluída</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary" style="height: 42px;">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <?php if ($busca != '' || $status_filtro != ''): ?>
                        <a href="trocas.php" class="btn btn-secondary" style="height: 42px; margin-top: 5px;">Limpar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Solicitante</th>
                        <th>Receptor</th>
                        <th>Detalhes da Troca</th>
                        <th>Confirmações</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultado) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td>
                                <div class="user-info">
                                    <span class="user-name">@<?= htmlspecialchars($row['solicitante_nome']) ?></span>
                                    <span class="user-email">ID: <?= $row['id_solicitante'] ?></span>
                                    <?php if (!empty($row['solicitante_email'])): ?>
                                        <span class="user-email"><?= htmlspecialchars($row['solicitante_email']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="user-info">
                                    <span class="user-name">@<?= htmlspecialchars($row['receptor_nome']) ?></span>
                                    <span class="user-email">ID: <?= $row['id_receptor'] ?></span>
                                    <?php if (!empty($row['receptor_email'])): ?>
                                        <span class="user-email"><?= htmlspecialchars($row['receptor_email']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="troca-info">
                                    <div class="livros-oferecidos">
                                        <strong>Livros Oferecidos (<?= $row['total_livros_oferecidos'] ?>):</strong>
                                        <?php 
                                        $titulos_oferecidos = explode('|||', $row['livros_oferecidos_titulos']);
                                        $autores_oferecidos = explode('|||', $row['livros_oferecidos_autores']);
                                        for ($i = 0; $i < count($titulos_oferecidos); $i++): 
                                            if (!empty($titulos_oferecidos[$i])):
                                        ?>
                                            <div class="livro-oferecido">
                                                <div class="livro-titulo"><?= htmlspecialchars($titulos_oferecidos[$i]) ?></div>
                                                <div class="livro-autor">por <?= htmlspecialchars($autores_oferecidos[$i]) ?></div>
                                            </div>
                                        <?php 
                                            endif;
                                        endfor; 
                                        ?>
                                    </div>
                                    <div class="troca-arrow">
                                        <i class="fas fa-exchange-alt"></i>
                                    </div>
                                    <div class="livro-info">
                                        <strong>Livro Solicitado:</strong>
                                        <div class="livro-titulo"><?= htmlspecialchars($row['livro_solicitado_titulo']) ?></div>
                                        <div class="livro-autor">por <?= htmlspecialchars($row['livro_solicitado_autor']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="confirmacoes">
                                    <span class="confirmacao <?= $row['confirm_solicitante'] ? 'sim' : 'nao' ?>">
                                        Solicitante: <?= $row['confirm_solicitante'] ? '✓' : '✗' ?>
                                    </span>
                                    <span class="confirmacao <?= $row['confirm_receptor'] ? 'sim' : 'nao' ?>">
                                        Receptor: <?= $row['confirm_receptor'] ? '✓' : '✗' ?>
                                    </span>
                                </div>
                                <?php if ($row['data_status']): ?>
                                    <div style="font-size: 0.8rem; color: #666; margin-top: 5px;">
                                        Status: <?= date('d/m/Y H:i', strtotime($row['data_status'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($row['data_solicitacao'])) ?></td>
                            <td>
                                <span class="status <?= $row['status'] ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <?php if ($row['status'] == 'pendente' || $row['status'] == 'aceita'): ?>
                                        <a href="?finalizar=<?= $row['id'] ?>" class="btn btn-success" title="Finalizar Troca">
                                            <i class="fas fa-check"></i> Finalizar
                                        </a>
                                        <a href="?cancelar=<?= $row['id'] ?>" class="btn btn-danger" title="Cancelar Troca">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    <?php elseif ($row['status'] == 'concluída'): ?>
                                        <span class="btn btn-info" style="cursor: default;">
                                            <i class="fas fa-check-circle"></i> Concluída
                                        </span>
                                    <?php else: ?>
                                        <span class="btn btn-secondary" style="cursor: default;">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">
                                Nenhuma troca encontrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="painel.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Voltar ao Painel
        </a>
    </div>

    <script>
        // Adiciona confirmação personalizada aos links de ação
        document.addEventListener('DOMContentLoaded', function() {
            const actionLinks = document.querySelectorAll('a.btn-success, a.btn-danger');
            
            actionLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const url = this.getAttribute('href');
                    const action = url.includes('finalizar') ? 'finalizar' : 'cancelar';
                    const confirmText = action === 'finalizar' ? 
                        'Tem certeza que deseja finalizar esta troca? Os livros serão marcados como indisponíveis.' : 
                        'Tem certeza que deseja cancelar esta troca?';
                    
                    // Criar modal de confirmação personalizado
                    const modal = document.createElement('div');
                    modal.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.5);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        z-index: 1000;
                    `;
                    
                    const modalContent = document.createElement('div');
                    modalContent.style.cssText = `
                        background: white;
                        padding: 30px;
                        border-radius: 8px;
                        text-align: center;
                        max-width: 400px;
                        width: 90%;
                    `;
                    
                    modalContent.innerHTML = `
                        <h3 style="margin-bottom: 15px; color: var(--dark-color);">Confirmar ação</h3>
                        <p style="margin-bottom: 20px; color: #666;">${confirmText}</p>
                        <div style="display: flex; gap: 10px; justify-content: center;">
                            <button id="confirmBtn" style="padding: 10px 20px; background: ${action === 'finalizar' ? 'var(--success-color)' : 'var(--accent-color)'}; color: white; border: none; border-radius: 4px; cursor: pointer;">Confirmar</button>
                            <button id="cancelBtn" style="padding: 10px 20px; background: #95a5a6; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancelar</button>
                        </div>
                    `;
                    
                    modal.appendChild(modalContent);
                    document.body.appendChild(modal);
                    
                    // Event listeners para os botões do modal
                    document.getElementById('confirmBtn').addEventListener('click', function() {
                        window.location.href = url;
                    });
                    
                    document.getElementById('cancelBtn').addEventListener('click', function() {
                        document.body.removeChild(modal);
                    });
                    
                    // Fechar modal clicando fora
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            document.body.removeChild(modal);
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>