<?php
session_start();
include_once('config.php'); // Certifique-se de que o arquivo de configuração da conexão esteja correto

// Remove os dados da sessão
session_unset();
session_destroy();

// Expira os cookies definidos
if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
    $statusSessao=$_COOKIE['status'];

    // Atualiza o status do token para "expirado"
    $sqlUpdateStatus = $conexao->prepare("UPDATE sessao SET status_sessao = 'expirada' WHERE token_sessao = ?");
    $sqlUpdateStatus->bind_param("s", $_COOKIE['token']);
    $sqlUpdateStatus->execute();
    $sqlUpdateStatus->close();
    // Limpa o cookie
    setcookie('token', '', time() - 3600, "/");
    setcookie('status','',time()-3600,"/");
    
    
}

if (isset($_COOKIE['id_usuario'])) {
    setcookie('id_usuario', '', time() - 3600, "/");
}
if (isset($_COOKIE['id_role'])) {
    setcookie('id_role', '', time() - 3600, "/");
}

// Redireciona para a página de login
header("Location: ../HTML/Login.html");
exit;
?>
