
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinição de Senha</title>
</head>
<body>
    

<?php
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

include 'conexao.php'; // aqui você precisa ter $conn = new mysqli(...);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Prepara e executa a consulta para buscar o usuário
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();

    if ($usuario) {
        // Gera um token seguro
        $token = bin2hex(random_bytes(32));

        // Atualiza o token no banco
        $stmt = $conn->prepare("UPDATE usuarios SET token = ? WHERE id = ?");
        $stmt->bind_param("si", $token, $usuario['id']);
        $stmt->execute();
        $stmt->close();

        // Link de redefinição
        $link = "http://localhost/projetinatt2.3/resetar_senha.php?token=$token";

        // Envia o e-mail com PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'isackalmeida740@gmail.com'; // seu email
            $mail->Password   = 'zfaa hmih zxcz jaez'; // sua senha ou App Password do Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('SEUEMAIL@gmail.com', 'Troca Troca JK - Recuperar Sua  Senha');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Redefinir Sua Senha';
            $mail->Body    = "Olá, clique no link abaixo para redefinir sua senha:<br><a href='$link'>$link</a>";

            $mail->send();
            echo "<script>alert('Link de recuperação enviado com sucesso.');window.location.href='index.html';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Erro ao enviar e-mail: {$mail->ErrorInfo}');</script>";
        }
    } else {
        echo "<script>alert('E-mail não encontrado.');window.location.href='esqueci_senha.html';</script>";
    }
}
?>

</body>
</html>





