<?php
include_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeCategoria = trim($_POST['nome_categoria']);
    $descricao = trim($_POST['descricao']);

    if (!empty($nomeCategoria)) {
        $sql = $conexao->prepare("INSERT INTO categoria (Nome_Categoria, Descricao) VALUES (?, ?)");
        $sql->bind_param("ss", $nomeCategoria, $descricao);

        if ($sql->execute()) {
            echo "Categoria adicionada com sucesso!";
        } else {
            echo "Erro ao adicionar categoria: " . $conexao->error;
        }

        $sql->close();
        $conexao->close();
    }
}
?>
