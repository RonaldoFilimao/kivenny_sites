<?php
include_once('config.php');
require_once('sair.php');
session_start();

if (isset($_SESSION['token'])) {
    $ultimaAtividade = $_SESSION['ultima_atividade'] ?? time();

    if (time() - $ultimaAtividade > 180) { // Expira após 3 minutos (180 segundos)
        // Atualiza o status da sessão no banco para "expirada"
        $token = $_SESSION['token'];
        $sqlAtualizar = $conexao->prepare("UPDATE sessao SET status_sessao = 'expirada' WHERE token_sessao = ?");
        $sqlAtualizar->bind_param("s", $token);
        $sqlAtualizar->execute();
        $sqlAtualizar->close();

        // Destrói a sessão do lado do servidor
        session_destroy();

        echo json_encode(['status' => 'expired']);
        exit;
    } else {
        $_SESSION['ultima_atividade'] = time(); // Atualiza a última atividade
        echo json_encode(['status' => 'active']);
    }
} else {
    echo json_encode(['status' => 'expired']);
}
?>
