<?php
session_start();  

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id_usuario'])) { 
    header("Location: login.php"); 
    exit(); 
}

include_once('config.php');  

// Verifica se a conexão foi estabelecida
if (!$conexao) { 
    die("Erro ao conectar ao banco de dados: " . mysqli_connect_error()); 
}

// Verifica se o ID do produto foi fornecido via GET
if (isset($_GET['id'])) {
    $idProduto = $_GET['id'];

    // Valida o ID do produto para evitar SQL Injection
    $idProduto = (int)$idProduto;

    // Verifica se o ID é válido
    if ($idProduto > 0) {
        // Inicia uma transação para garantir a consistência dos dados
        $conexao->begin_transaction();

        try {
            // Apaga os registros relacionados na tabela itens_venda
            $sqlItensVenda = "DELETE FROM itens_venda WHERE ID_Produto = ?";
            if ($stmtItensVenda = $conexao->prepare($sqlItensVenda)) {
                $stmtItensVenda->bind_param("i", $idProduto);
                if (!$stmtItensVenda->execute()) {
                    throw new Exception("Erro ao apagar itens de venda.");
                }
            } else {
                throw new Exception("Erro ao preparar a consulta para itens de venda.");
            }

            // Apaga os registros relacionados na tabela historico_estoque
            $sqlHistorico = "DELETE FROM historico_estoque WHERE ID_Produto = ?";
            if ($stmtHistorico = $conexao->prepare($sqlHistorico)) {
                $stmtHistorico->bind_param("i", $idProduto);
                if (!$stmtHistorico->execute()) {
                    throw new Exception("Erro ao apagar histórico de estoque.");
                }
            } else {
                throw new Exception("Erro ao preparar a consulta para histórico de estoque.");
            }

            // Apaga o produto da tabela produto
            $sqlProduto = "DELETE FROM produto WHERE ID_Produto = ?";
            if ($stmtProduto = $conexao->prepare($sqlProduto)) {
                $stmtProduto->bind_param("i", $idProduto);
                if (!$stmtProduto->execute()) {
                    throw new Exception("Erro ao apagar o produto.");
                }
            } else {
                throw new Exception("Erro ao preparar a consulta para excluir o produto.");
            }

            // Confirma a transação
            $conexao->commit();

            $_SESSION['msg'] = "Produto excluído com sucesso!";
            header("Location: visualizarProdutos.php");
            exit();
        } catch (Exception $e) {
            // Em caso de erro, desfaz a transação
            $conexao->rollback();
            $_SESSION['msg'] = "Erro ao excluir o produto: " . $e->getMessage();
            header("Location: visualizarProdutos.php");
            exit();
        }
    } else {
        $_SESSION['msg'] = "ID do produto inválido.";
        header("Location: visualizarProdutos.php");
        exit();
    }
} else {
    $_SESSION['msg'] = "Produto não encontrado.";
    header("Location: visualizarProdutos.php");
    exit();
}

$conexao->close();
?>
