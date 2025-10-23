<?php
session_start();
include 'conexao.php';
include 'verifica_login.php';

$id_usuario = $_SESSION['id']; // usuário logado (receptor da troca)

if (isset($_POST['id_troca'], $_POST['resposta'])) {
    $id_troca = intval($_POST['id_troca']);
    $resposta = $_POST['resposta'];

    // Validar que a resposta é aceitável
    if (!in_array($resposta, ['aceita', 'recusada'])) {
        die("Resposta inválida!");
    }

    // 1️⃣ Verificar se a troca realmente pertence ao usuário e pegar dados para o email
    $sql_check = "SELECT 
                    t.*, 
                    u_solicitante.email as email_solicitante,
                    u_solicitante.nome as nome_solicitante,
                    u_receptor.nome as nome_receptor,
                    l.titulo as titulo_livro
                  FROM trocas t
                  JOIN usuarios u_solicitante ON t.id_solicitante = u_solicitante.id
                  JOIN usuarios u_receptor ON t.id_receptor = u_receptor.id  
                  JOIN livros l ON t.id_livro_solicitado = l.id
                  WHERE t.id = ? AND t.id_receptor = ? AND t.status = 'pendente'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_troca, $id_usuario);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        die("Troca não encontrada ou já respondida.");
    }
    
    $dados_troca = $result_check->fetch_assoc();
    $stmt_check->close();

    // 2️⃣ Atualizar o status da troca e registrar a data/hora
    $sql_update = "UPDATE trocas SET status = ?, data_status = NOW() WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $resposta, $id_troca);

    if ($stmt_update->execute()) {
        $stmt_update->close();

        // 3️⃣ ENVIAR EMAIL DE RESPOSTA
        enviarEmailResposta($conn, $dados_troca, $resposta);

        // 4️⃣ Mensagem de sucesso e redirecionamento
        echo "<script>
                window.location.href = 'trocas_solicitadas.php';
              </script>";
        exit();
    } else {
        echo "Erro ao atualizar a troca: " . $stmt_update->error;
    }
} else {
    die("Dados incompletos.");
}

// FUNÇÃO PARA ENVIAR EMAIL DE RESPOSTA
function enviarEmailResposta($conn, $dados_troca, $resposta) {
    require_once 'PHPMailer/src/PHPMailer.php';
    require_once 'PHPMailer/src/SMTP.php';
    require_once 'PHPMailer/src/Exception.php';

    $emailSolicitante = $dados_troca['email_solicitante'];
    $nomeSolicitante = $dados_troca['nome_solicitante'];
    $nomeReceptor = $dados_troca['nome_receptor'];
    $tituloLivro = $dados_troca['titulo_livro'];
    
    // Monta o link para as trocas do solicitante
    $link = "http://localhost/projetinattfinal/minhas_trocas.php";

    // Configura o assunto e mensagem baseado na resposta
    if ($resposta === 'aceita') {
        $assunto = "Sua solicitacao de troca foi ACEITA!";
        $mensagemStatus = "<b style='color: green;'>ACEITA</b>";
        $mensagemCorpo = "Parabéns! {$nomeReceptor} aceitou sua solicitação de troca para o livro <b>\"{$tituloLivro}\"</b>.";
    } else {
        $assunto = "Sua solicitacao de troca foi RECUSADA";
        $mensagemStatus = "<b style='color: red;'>RECUSADA</b>";
        $mensagemCorpo = "Infelizmente, {$nomeReceptor} recusou sua solicitação de troca para o livro <b>\"{$tituloLivro}\"</b>.";
    }

    // Configura e envia o e-mail com PHPMailer
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
        $mail->addAddress($emailSolicitante);
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Resposta da sua Solicitação de Troca</h2>
                
                <p>Olá, <b>{$nomeSolicitante}</b>!</p>
                
                <p>{$mensagemCorpo}</p>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <p><strong>Status:</strong> {$mensagemStatus}</p>
                    <p><strong>Livro solicitado:</strong> {$tituloLivro}</p>
                    <p><strong>Proprietário do livro:</strong> {$nomeReceptor}</p>
                </div>

                <p>Você pode ver os detalhes da sua solicitação clicando no link abaixo:</p>
                <p><a href='{$link}' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Minhas Trocas</a></p>
                
                <br>
                <p><em>— Equipe Troca Troca JK 📚</em></p>
            </body>
            </html>
        ";

        $mail->send();
        error_log("E-mail de resposta enviado para {$emailSolicitante} - Status: {$resposta}");
        return true;
        
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail de resposta: " . $e->getMessage());
        return false;
    }
}
?>