<?php
header('Content-Type: application/json');
include('config.php');

$sql = "SELECT ID_Categoria, Nome_Categoria FROM categoria";
$result = $conexao->query($sql);

$categorias = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
}

echo json_encode($categorias);
?>
