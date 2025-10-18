<?php
session_start();
include '../conexao.php';
include '../verifica_admin.php';
// if (!isset($_SESSION['admin'])) {
//     header("Location: login.php");
//     exit;
// }

// Excluir livro
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);

    // Verifica se o livro participa de alguma troca
    $sqlVerifica = "
        SELECT id FROM trocas WHERE id_livro_solicitado = $id
        UNION
        SELECT id FROM trocas_livros_oferecidos WHERE id_livro_oferecido = $id
    ";

    $verifica = $conn->query($sqlVerifica);

    if ($verifica->num_rows > 0) {
        $_SESSION['flash_message'] = '❌ Não é possível excluir este livro, pois ele está envolvido em uma troca.';
        $_SESSION['flash_type'] = 'error';
    } else {
        if ($conn->query("DELETE FROM livros WHERE id = $id")) {
            $_SESSION['flash_message'] = '✅ Livro excluído com sucesso!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = '⚠️ Erro ao excluir livro!';
            $_SESSION['flash_type'] = 'error';
        }
    }

    header("Location: livros.php");
    exit;
}


// Busca
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$sql = "SELECT l.*, u.nome_usuario, u.email 
        FROM livros l 
        LEFT JOIN usuarios u ON l.id_usuario = u.id 
        WHERE 1=1";

if ($busca != '') {
    $sql .= " AND (l.titulo LIKE '%$busca%' OR l.autor LIKE '%$busca%' OR u.nome_usuario LIKE '%$busca%')";
}

$sql .= " ORDER BY l.id DESC";

$resultado = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Livros</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
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
            max-width: 1400px;
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
        
        .search-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .search-input {
            flex: 1;
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
            min-width: 1000px;
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
        
        .status.disponivel {
            background-color: #e8f5e9;
            color: var(--success-color);
        }
        
        .status.indisponivel {
            background-color: #ffebee;
            color: var(--accent-color);
        }
        
        .estado {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .btn-danger {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
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
            .search-form {
                flex-direction: column;
            }
            
            .search-input {
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
            <h1><i class="fas fa-book"></i> Gerenciar Livros</h1>
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

        <div class="search-container">
            <form method="GET" class="search-form">
                <input type="text" name="busca" class="search-input" placeholder="Buscar por título, autor ou nome do usuário..." value="<?= htmlspecialchars($busca) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <?php if ($busca != ''): ?>
                    <a href="livros.php" class="btn btn-secondary">Limpar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Gênero</th>
                        <th>Estado</th>
                        <th>Status</th>
                        <th>Dono</th>
                        <th>Data de Publicação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultado) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><strong><?= htmlspecialchars($row['titulo']) ?></strong></td>
                            <td><?= htmlspecialchars($row['autor']) ?></td>
                            <td><?= htmlspecialchars($row['genero']) ?></td>
                            <td>
                                <span class="estado"><?= htmlspecialchars($row['estado']) ?></span>
                            </td>
                            <td>
                                <span class="status <?= $row['status'] ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="user-info">
                                    <span class="user-name">@<?= htmlspecialchars($row['nome_usuario']) ?></span>
                                    <span class="user-email">ID: <?= $row['id_usuario'] ?></span>
                                    <?php if (!empty($row['email'])): ?>
                                        <span class="user-email"><?= htmlspecialchars($row['email']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= date('d/m/Y', strtotime($row['data_publicacao'])) ?></td>
                            <td>
                                <a href="?excluir=<?= $row['id'] ?>" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Excluir
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 20px;">
                                Nenhum livro encontrado.
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
        // Adiciona confirmação personalizada aos links de exclusão
        document.addEventListener('DOMContentLoaded', function() {
            const deleteLinks = document.querySelectorAll('a.btn-danger');
            
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const url = this.getAttribute('href');
                    
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
                        <h3 style="margin-bottom: 15px; color: var(--dark-color);">Confirmar exclusão</h3>
                        <p style="margin-bottom: 20px; color: #666;">Tem certeza que deseja excluir este livro? Esta ação não pode ser desfeita.</p>
                        <div style="display: flex; gap: 10px; justify-content: center;">
                            <button id="confirmBtn" style="padding: 10px 20px; background: var(--accent-color); color: white; border: none; border-radius: 4px; cursor: pointer;">Excluir</button>
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