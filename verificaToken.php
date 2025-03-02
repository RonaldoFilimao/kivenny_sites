<?php
// Inicia a sessão
session_start();
include_once('config.php'); 

// Verifica se o usuário está logado
if (isset($_SESSION['token_sessao'])) {
    $token = $_SESSION['token_sessao'];

    // Verifica se o token é válido
    $sql = $conexao->prepare("SELECT id_usuario, validade_token FROM sessaoes WHERE token_sessao = ? AND validade_token > NOW()");
    $sql->bind_param("s", $token_sessao);
    $sql->execute();
    $sql->store_result();

    if ($sql->num_rows > 0) {
        // Token válido, permite o acesso
        $sql->bind_result($idUsuario, $validadeToken);
        $sql->fetch();
        
        // Opcional: Armazena o ID do usuário na sessão
        $_SESSION['usuario_id'] = $idUsuario;
        
        echo "Acesso permitido!";
    } else {
        // Token inválido ou expirado
        echo "Sessão expirada ou inválida!";
    }

    $sql->close();
} else {
    echo "Usuário não está autenticado!";
}
?>
