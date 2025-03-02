<?php
session_start();
include_once('config.php');

// Verifica se os cookies necessários estão definidos
if (!isset($_COOKIE['id_usuario']) || !isset($_COOKIE['validade_token'])) {
    header("Location: ../HTML/login.html");
    exit;
}

function verificarSessao($conexao, $token) {
    $sql = $conexao->prepare(
        "SELECT * 
         FROM `sessao` 
         WHERE `id_sessao` = ? 
         AND `status_sessao` = 'ativa' 
         AND `validade_token` > NOW()"
    );
    $sql->bind_param("s", $token);
    $sql->execute();
    $resultado = $sql->get_result();
    return $resultado->num_rows > 0;
}

// Verifica se o token expirou
$dataValidadeToken = strtotime($_COOKIE['validade_token']);
if ($dataValidadeToken < time()) {
    // Atualiza o status da sessão para expirada
    $sqlUpdateStatus = $conexao->prepare("UPDATE sessao SET status_sessao = 'expirada' WHERE token_sessao = ?");
    $sqlUpdateStatus->bind_param("s", $_COOKIE['token']);
    $sqlUpdateStatus->execute();
    $sqlUpdateStatus->close();

    // Limpa os cookies e destrói a sessão
    foreach (['id_usuario', 'email', 'id_role', 'status', 'validade_token', 'ultima_atividade', 'nome_usuario'] as $cookieName) {
        setcookie($cookieName, '', time() - 3600, "/");
    }
    session_destroy();
    header("Location: ../HTML/login.html");
    exit;
}

$nomeUsuario = 'Vendedor'; // Valor padrão

// Recupera o token da sessão
if (isset($_SESSION['token'])) {
    $token = $_SESSION['token'];

    // Consulta para obter o nome do usuário associado ao token
    $sql = $conexao->prepare(
        "SELECT usuarios.nome 
         FROM usuarios 
         INNER JOIN sessao ON usuarios.id_usuario = sessao.id_usuario 
         WHERE sessao.token_sessao = ?"
    );
    $sql->bind_param("s", $token);
    $sql->execute();
    $sql->bind_result($nomeUsuario);

    // Verifica se o nome foi obtido corretamente
    if ($sql->fetch()) {
        $_SESSION['nome_usuario'] = $nomeUsuario; // Atualiza a sessão com o nome do usuário
    } else {
        // Caso o nome não seja encontrado, registra um erro no log
        error_log('Nome de usuário não encontrado para o token: ' . $token);
    }
    $sql->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Vendedor</title>
    <link rel="stylesheet" href="../CSS/vendedorDashboard.css">
    <script src="../JS/imagensAdminSistema.js"></script>
</head>
<body>
    <div class="side-menu">
        <h1>Painel do Vendedor</h1>
        <ul>
            <li><a href="../PHP/VisualizarProdutosVendedor.php"><img src="../ICONS/Produtos .png" alt="" style="width: 30px; height: 30px;"> &nbsp;<span>Produtos</span></a></li>
            <li><a href="../HTML/clientes.html"><img src="../ICONS/Cliente .png" alt="" style="width: 30px; height: 30px;"> &nbsp;<span>Dados dos Clientes</span></a></li>
            <li><a href="../PHP/VisualizarProdutosVendedor.php"><img src="../ICONS/Income .png" alt="" style="width: 30px; height: 30px;"> &nbsp;<span>Vendas</span></a></li>
            <li><a href="sair.php"><img src="../ICONS/sair.png" alt="" style="width: 30px; height: 30px;"> &nbsp;<span>Sair</span></a></li>
        </ul>
    </div>

    <div class="container">
        <div class="welcome">
            <h3>Bem-Vindo ao Sistema: <?php echo htmlspecialchars($_SESSION['nome_usuario'] ?? $nomeUsuario); ?></h3>
        </div>
        <div class="recent-payments">
            <h2>SISTEMA DE CADASTRO E VENDA DE PRODUTOS</h2>
            <div id="imageCarousel" class="carousel">
                <img src="../Imagens/ceres.jpeg" alt="Imagem 1" class="carousel-image active">
                <img src="../imagens/cimentoNacional.jpeg" alt="Imagem 2" class="carousel-image">
                <img src="../imagens/compal.jpeg" alt="Imagem 3" class="carousel-image">
                <img src="../imagens/s24ultra.jpeg" alt="Imagem 4" class="carousel-image">
                <img src="../imagens/Dugongu.jpeg" alt="Imagem 5" class="carousel-image">
                <img src="../imagens/loica.jpeg" alt="Imagem 6" class="carousel-image">
                <img src="../imagens/loica2.jpeg" alt="Imagem 7" class="carousel-image">
                <img src="../imagens/S23Ultra.jpeg" alt="Imagem 8" class="carousel-image">
                <img src="../imagens/vendas.jpeg" alt="Imagem 9" class="carousel-image">
                <img src="../imagens/Diversos1.jpg" alt="Imagem 10" class="carousel-image">
                <img src="../imagens/1computadores.jpeg" alt="Imagem 11" class="carousel-image">
                <img src="../imagens/vendas.jpeg" alt="Imagem 12" class="carousel-image">
            </div>
        </div>
    </div>
</body>
</html>
