<?php
// Conexão com o banco de dados e contagem dos administradores e vendedores
include_once('config.php');

// Contar o número de administradores (ID_Role = 0)
$sql_admins = "SELECT COUNT(*) FROM usuarios WHERE ID_Role = 0";
$result_admins = $conexao->query($sql_admins);
$row_admins = $result_admins->fetch_assoc();
$totalAdmins = $row_admins['COUNT(*)'];

// Contar o número de vendedores (ID_Role = 1)
$sql_vendedores = "SELECT COUNT(*) FROM usuarios WHERE ID_Role = 1";
$result_vendedores = $conexao->query($sql_vendedores);
$row_vendedores = $result_vendedores->fetch_assoc();
$totalVendedores = $row_vendedores['COUNT(*)'];

// Fechar a conexão
$conexao->close();
?>