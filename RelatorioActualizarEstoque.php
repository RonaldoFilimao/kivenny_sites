<?php
include_once('config.php');
// Função para atualizar o estoque e gerar o relatório
function atualizarEstoque($idProduto, $quantidadeAdicionada) {
    global $conexao;

    // Obter a quantidade atual do produto
    $sqlProduto = "SELECT Quantidade FROM produto WHERE ID_Produto = ?";
    $stmtProduto = $conexao->prepare($sqlProduto);
    $stmtProduto->bind_param("i", $idProduto);
    $stmtProduto->execute();
    $stmtProduto->bind_result($quantidadeAtual);
    $stmtProduto->fetch();

    // Atualizar a quantidade do produto
    $novaQuantidade = $quantidadeAtual + $quantidadeAdicionada;
    $sqlAtualizarEstoque = "UPDATE produto SET Quantidade = ? WHERE ID_Produto = ?";
    $stmtAtualizarEstoque = $conexao->prepare($sqlAtualizarEstoque);
    $stmtAtualizarEstoque->bind_param("ii", $novaQuantidade, $idProduto);
    $stmtAtualizarEstoque->execute();

    // Gerar relatório de atualização de estoque
    gerarRelatorioEstoque($idProduto, $quantidadeAtual, $quantidadeAdicionada);

    echo "Estoque atualizado com sucesso!";
}
?>
