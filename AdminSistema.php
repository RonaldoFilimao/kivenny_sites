<?php
// Iniciar a sessão para acessar as variáveis de sessão
session_start();
// Conectar ao banco de dados
include_once('config.php');

// Verificar se os cookies necessários estão definidos
if (!isset($_COOKIE['id_usuario']) || !isset($_COOKIE['validade_token'])) {
    // Redirecionar para o login se os cookies não estiverem definidos
    header("Location: ../HTML/login.html");
    exit;
}
// Verificar a validade do token
$dataValidadeToken = strtotime($_COOKIE['validade_token']);
if ($dataValidadeToken < time()) {
    // Limpar os cookies e redirecionar para login
    foreach (['id_usuario', 'email', 'id_role', 'status', 'validade_token', 'ultima_atividade', 'nome_usuario'] as $cookieName) {
        setcookie($cookieName, '', time() - 3600, "/");
    }
    session_destroy();

    // Atualizar o status da sessão para 'expirada' no banco de dados
    $sqlUpdateStatus = $conexao->prepare("UPDATE sessao SET status_sessao = 'expirada' WHERE token_sessao = ?");
    $sqlUpdateStatus->bind_param("s", $_COOKIE['token']);
    $sqlUpdateStatus->execute();
    $sqlUpdateStatus->close();

    // Redirecionar para a página de login
    header("Location: ../HTML/login.html");
    exit;
}

// Recuperar o nome do usuário do cookie ou usar o valor da sessão
$nomeUsuario = 'Admin'; 

if (isset($_COOKIE['nome_usuario'])) {
    $nomeUsuario = $_COOKIE['nome_usuario']; // Recupera o nome do usuário do cookie
    $_SESSION['nome_usuario'] = $nomeUsuario; // Armazena o nome na sessão
}

// Contar o número de administradores (ID_Role = 1)
$sql_admins = "SELECT COUNT(*) AS total_admins FROM usuarios WHERE ID_Role = 1";
$result_admins = $conexao->query($sql_admins);
if ($result_admins) {
    $row_admins = $result_admins->fetch_assoc();
    $totalAdmins = $row_admins['total_admins'];
} else {
    $totalAdmins = 0;  // Caso não tenha resultados, atribui 0
}

// Contar o número de vendedores (ID_Role = 2)
$sql_vendedores = "SELECT COUNT(*) AS total_vendedores FROM usuarios WHERE ID_Role = 2";
$result_vendedores = $conexao->query($sql_vendedores);
if ($result_vendedores) {
    $row_vendedores = $result_vendedores->fetch_assoc();
    $totalVendedores = $row_vendedores['total_vendedores'];
} else {
    $totalVendedores = 0;  // Caso não tenha resultados, atribui 0
}

// Fechar a conexão com o banco de dados
$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Admin</title>
    <link rel="stylesheet" href="../CSS/adminDashboard.css">
    <script src="../JS/imagensAdminSistema.js"></script>
    <script src="../JS/jquery.js"></script>
</head>
<body>
    <div class="side-menu">
        <h1>Painel do Admin</h1>
        <ul>
            <li><a href="../HTML/formularioCadastro.html"><img src="../ICONS/DadosUsuario1.png" alt="" style="width: 30px; height: 30px;"> &nbsp;<span>Cadastrar Usuários</span></a></li>
            <li><a href="../PHP/ControlaEstoque.php"><img src="../ICONS/Estoque 1.png" alt="" style="width: 30px; height: 30px;"> &nbsp;<span>Controlar Estoque</span></a></li>
            <li><a href="../HTML/Adminclientes.html"><img src="../ICONS/Cliente .png" alt="" style="width: 30px; height: 30px;"> &nbsp;<span>Clientes</span></a></li>
            <li><a href="../PHP/gestaoDeRelatorios.html"><img src="../ICONS/Relatório .png" alt="" style="width: 30px; height: 30px;"> &nbsp;<span>Gerar Relatório</span></a></li>
            <li><a href="visualizar.php"><img src="../ICONS/User.png" alt="" style="width: 30px; height: 30px;"> &nbsp;<span>Usuários</span></a></li>
            <li><a href="../HTML/gestaoProdutos.php"><img src="../ICONS/AdicionarProduto .png" alt="" style="width: 30px; height: 30px;"> &nbsp;<span>Produtos</span></a></li>
            <li><a href="sair.php"><img src="../ICONS/sair.png" alt="" style="width: 30px; height: 30px;"> &nbsp;<span>Sair</span></a></li>
        </ul>
    </div>

    <div class="container">
        <div class="content" id="usuarios">
            <div class="cards">
                <!-- Card de Vendedores -->
                <div class="card">
                    <h1><?php echo $totalVendedores; ?></h1>
                    <h3>Vendedores</h3>
                    <img src="../ICONS/Cliente .png" alt="" style="width: 100px; height: 100px;">
                </div>
                
                <!-- Card de Administradores -->
                <div class="card" id="Administradores">
                    <h1><?php echo $totalAdmins; ?></h1>
                    <h3>Administradores</h3>
                    <img src="../ICONS/Admin.png" alt="" style="width: 100px; height: 100px;">
                </div>

                <!-- Card de Loja (Caso necessário) -->
                <div class="card" id="lojas">
                    <h1>1</h1>
                    <h3>Loja</h3>
                    <img src="../ICONS/Loja.jpg" alt="" style="width: 100px; height: 100px;">
                </div>
            </div>

            <!-- Novo Div com Carrossel de Imagens -->
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
    </div>
</body>
</html>
