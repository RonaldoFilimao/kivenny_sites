<?php
// Arquivo de configuração para conectar ao banco de dados

// Dados de acesso ao banco de dados
$servidor = "localhost"; 
$usuario = "root"; 
$senha = "";
$bancoDeDados = "SistemaVendas"; 

// Cria a conexão com o banco de dados
$conexao = new mysqli($servidor, $usuario, $senha, $bancoDeDados);

// Verifica se houve erro na conexão
if ($conexao->connect_error) {
    die("Conexão falhou: " . $conexao->connect_error);
}
?>
