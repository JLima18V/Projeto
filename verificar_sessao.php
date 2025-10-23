<?php
session_start();
include 'conexao.php';

if (isset($_SESSION['id'])) {
    $stmt = $conn->prepare("SELECT status FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'banido') {
            session_destroy();
            echo 'banido';
            exit();
        }
    }
    $stmt->close();
}

echo 'ok';
?>