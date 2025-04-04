<?php
$servername = "localhost";  // Servidor do banco de dados (normalmente "localhost")
$username = "root";         // Usuário do MySQL
$password = "jk123456";             // Senha do MySQL (pode ser vazia no XAMPP)
$database = "troca_trocaJK"; // Nome do banco de dados

// Criar conexão
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
} 

// Se precisar depurar a conexão, descomente a linha abaixo
// echo "Conexão bem-sucedida!";
?>