<?php
function enviarEmailInteresse($conn, $id_livro_desejado, $id_solicitante) {
    require_once 'PHPMailer/src/PHPMailer.php';
    require_once 'PHPMailer/src/SMTP.php';
    require_once 'PHPMailer/src/Exception.php';

    // REMOVA estas linhas:
    // use PHPMailer\PHPMailer\PHPMailer;
    // use PHPMailer\PHPMailer\Exception;
    // use PHPMailer\PHPMailer\SMTP;

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
        $link = "http://localhost/projetinattfinal/trocas_solicitadas.php";

        // Configura e envia o e-mail com PHPMailer (sem 'use')
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'isackalmeida740@gmail.com';
            $mail->Password   = 'zfaa hmih zxcz jaez';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('isackalmeida740@gmail.com', 'Troca Troca JK');
            $mail->addAddress($emailDono);
            $mail->isHTML(true);
            $mail->Subject = "Interesse no seu livro \"{$tituloLivro}\"!";
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
            error_log("E-mail de interesse enviado para {$emailDono}");
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail: " . $e->getMessage());
            return false;
        }
    }
    
    return false;
}
?>