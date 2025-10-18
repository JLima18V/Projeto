<?php
session_start();
include '../conexao.php';
include '../verifica_admin.php';
// if (!isset($_SESSION['admin'])) {
//     header("Location: login.php");
//     exit;
// }

// Estatísticas para o dashboard
$total_usuarios = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios"))['total'];
$total_livros = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM livros"))['total'];
$usuarios_ativos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios WHERE status = 'ativo'"))['total'];
$usuarios_banidos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM usuarios WHERE status = 'banido'"))['total'];

// Usuários recentes - ADICIONANDO MAIS CAMPOS
$usuarios_recentes = mysqli_query($conn, "SELECT id, nome, sobrenome, nome_usuario, email, status, data_cadastro FROM usuarios ORDER BY id DESC LIMIT 5");

// Livros recentes - ADICIONANDO MAIS CAMPOS
$livros_recentes = mysqli_query($conn, "SELECT l.*, u.nome_usuario FROM livros l LEFT JOIN usuarios u ON l.id_usuario = u.id ORDER BY l.id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador</title>
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
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--secondary-color);
            color: white;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: var(--primary-color);
            text-align: center;
        }
        
        .sidebar-header h2 {
            font-size: 1.5rem;
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .sidebar-menu ul {
            list-style: none;
        }
        
        .sidebar-menu li {
            padding: 12px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: background-color 0.3s;
        }
        
        .sidebar-menu li:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu li.active {
            background-color: var(--primary-color);
        }
        
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
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
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        
        /* Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 1rem;
            color: var(--dark-color);
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .card-icon.users {
            background-color: var(--primary-color);
        }
        
        .card-icon.books {
            background-color: var(--success-color);
        }
        
        .card-icon.active-users {
            background-color: var(--warning-color);
        }
        
        .card-icon.banned {
            background-color: var(--accent-color);
        }
        
        .card-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .card-change {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        /* Tables */
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-header h2 {
            color: var(--secondary-color);
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .search-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
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
        
        .status.active {
            background-color: #e8f5e9;
            color: var(--success-color);
        }
        
        .status.banned {
            background-color: #ffebee;
            color: var(--accent-color);
        }

        .status-livro {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-livro.disponivel {
            background-color: #e8f5e9;
            color: var(--success-color);
        }
        
        .status-livro.indisponivel {
            background-color: #ffebee;
            color: var(--accent-color);
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-danger {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
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
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
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
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .cards {
                grid-template-columns: 1fr;
            }
            
            .table-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .search-form {
                width: 100%;
            }
            
            .search-input {
                flex: 1;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Painel Admin</h2>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li class="active"><a href="painel.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="usuarios.php"><i class="fas fa-users"></i> Gerenciar Usuários</a></li>
                    <li><a href="livros.php"><i class="fas fa-book"></i> Gerenciar Livros</a></li>
                    <li><a href="trocas.php"><i class="fas fa-sync-alt"></i> Gerenciar Trocas</a></li>
                    <li><a href="homepage_admin.php"><i class="fas fa-arrow-left"></i> Voltar</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle" style="font-size: 2rem;"></i>
                    <span><?php echo $_SESSION['email']; ?></span>
                </div>
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
            
            <!-- Cards -->
            <div class="cards">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total de Usuários</h3>
                        <div class="card-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $total_usuarios; ?></div>
                    <div class="card-change">Registrados no sistema</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total de Livros</h3>
                        <div class="card-icon books">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $total_livros; ?></div>
                    <div class="card-change">Cadastrados no sistema</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Usuários Ativos</h3>
                        <div class="card-icon active-users">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $usuarios_ativos; ?></div>
                    <div class="card-change">Atualmente ativos</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Usuários Banidos</h3>
                        <div class="card-icon banned">
                            <i class="fas fa-user-slash"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $usuarios_banidos; ?></div>
                    <div class="card-change">Contas suspensas</div>
                </div>
            </div>
            
            <!-- Usuários Recentes -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Usuários Recentes</h2>
                    <a href="usuarios.php" class="btn btn-primary">Ver Todos</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Sobrenome</th>
                            <th>Nome de Usuário</th>
                            <th>Email</th>
                            <th>Data de Cadastro</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($usuarios_recentes) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($usuarios_recentes)): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['nome']) ?></td>
                                <td><?= htmlspecialchars($row['sobrenome']) ?></td>
                                <td>@<?= htmlspecialchars($row['nome_usuario']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= date('d/m/Y', strtotime($row['data_cadastro'])) ?></td>
                                <td>
                                    <span class="status <?= $row['status'] == 'ativo' ? 'active' : 'banned' ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'ativo'): ?>
                                        <a href="usuarios.php?banir=<?= $row['id'] ?>" class="btn btn-warning">Banir</a>
                                    <?php else: ?>
                                        <a href="usuarios.php?desbanir=<?= $row['id'] ?>" class="btn btn-success">Desbanir</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8">Nenhum usuário encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Livros Recentes -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Livros Recentes</h2>
                    <a href="livros.php" class="btn btn-primary">Ver Todos</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Dono</th>
                            <th>Status</th>
                            <th>Data de Publicação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($livros_recentes) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($livros_recentes)): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['titulo']) ?></td>
                                <td><?= htmlspecialchars($row['autor']) ?></td>
                                <td>@<?= htmlspecialchars($row['nome_usuario']) ?> (ID: <?= $row['id_usuario'] ?>)</td>
                                <td>
                                    <span class="status-livro <?= $row['status'] == 'disponivel' ? 'disponivel' : 'indisponivel' ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($row['data_publicacao'])) ?></td>
                                <td>
                                    <a href="livros.php?excluir=<?= $row['id'] ?>" class="btn btn-danger">Excluir</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7">Nenhum livro encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Adiciona confirmação personalizada aos links de ação
        document.addEventListener('DOMContentLoaded', function() {
            const actionLinks = document.querySelectorAll('a.btn-warning, a.btn-success, a.btn-danger');
            
            actionLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const action = this.textContent.toLowerCase();
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
                        <h3 style="margin-bottom: 15px; color: var(--dark-color);">Confirmar ação</h3>
                        <p style="margin-bottom: 20px; color: #666;">Tem certeza que deseja ${action} este item?</p>
                        <div style="display: flex; gap: 10px; justify-content: center;">
                            <button id="confirmBtn" style="padding: 10px 20px; background: var(--accent-color); color: white; border: none; border-radius: 4px; cursor: pointer;">Confirmar</button>
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