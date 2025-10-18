<?php
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
require_once 'PHPMailer/src/Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// include 'conexao.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Exemplo: esse trecho entra logo depois de você registrar a solicitação de troca
// $id_livro_desejado = ID do livro que o usuário escolheu
// $id_solicitante = $_SESSION['id_usuario']

// Busca os dados do dono do livro e do título
$sql = "SELECT u.email, u.nome, l.titulo 
        FROM usuarios u 
        JOIN livros l ON u.id = l.id_usuario    
        WHERE l.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_livro_desejado);
$stmt->execute();
$result = $stmt->get_result();
$dono = $result->fetch_assoc();
$stmt->close();

if ($dono) {
    $emailDono = $dono['email'];
    $nomeDono = $dono['nome'];
    $tituloLivro = $dono['titulo'];

    // Monta o link para as solicitações
    $link = "http://localhost/projetinatt4.1/trocas_solicitadas.php";

    // Configura e envia o e-mail com PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'isackalmeida740@gmail.com'; // seu email
        $mail->Password   = 'zfaa hmih zxcz jaez'; // sua App Password do Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('isackalmeida740@gmail.com', 'Troca Troca JK');
        $mail->addAddress($emailDono);
        $mail->isHTML(true);
        $mail->Subject = "Alguém deseja o seu livro \"{$tituloLivro}\"";
        $mail->Body = "
            <html>
            <body>
                <p>Olá, <b>{$nomeDono}</b>!</p>
                <p>Um usuário demonstrou interesse no seu livro <b>\"{$tituloLivro}\"</b>.</p>
                <p>Veja os detalhes da solicitação clicando no link abaixo:</p>
                <p><a href='{$link}'>Ver solicitações de troca</a></p>
                <br>
                <p>— Equipe Troca Troca JK 📚</p>
            </body>
            </html>
        ";

        $mail->send();
        echo "<script>console.log('E-mail de interesse enviado para {$emailDono}');</script>";
    } catch (Exception $e) {
        echo "<script>console.error('Erro ao enviar e-mail: {$mail->ErrorInfo}');</script>";
    }
}
?>
