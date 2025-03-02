<?php
session_start();
include_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCategoria = $_POST['id_categoria'];
    $nomeProduto = trim($_POST['nome_produto']);
    $quantidade = intval($_POST['quantidade']);
    $descricao = trim($_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $dataEntrada = $_POST['data_entrada'];

    // Verifica se a data está no formato correto (YYYY-MM-DD)
    $dataEntradaObj = DateTime::createFromFormat('Y-m-d', $dataEntrada);
    if ($dataEntradaObj && $dataEntradaObj->format('Y-m-d') === $dataEntrada) {
        $dataEntrada = $dataEntradaObj->format('Y-m-d');
    } else {
        echo "Erro: O formato da data de entrada está inválido!";
        exit;
    }

    if (!empty($nomeProduto) && !empty($descricao) && $preco > 0 && !empty($dataEntrada)) {
        // Verifica se o produto já existe
        $checkSql = $conexao->prepare("SELECT COUNT(*) FROM produto WHERE Nome_Produto = ?");
        $checkSql->bind_param("s", $nomeProduto);
        $checkSql->execute();
        $checkSql->bind_result($count);
        $checkSql->fetch();
        $checkSql->close();

        if ($count > 0) {
            $_SESSION['msg'] = "Erro: O produto com este nome já existe!";
            header("Location:../HTML/gestaoProdutos.php");
            exit;
        } else {
            $sql = $conexao->prepare("INSERT INTO produto (ID_Categoria, Nome_Produto, Quantidade, Descricao, Preco, Data_Entrada) VALUES (?, ?, ?, ?, ?, ?)");
            $sql->bind_param("isisss", $idCategoria, $nomeProduto, $quantidade, $descricao, $preco, $dataEntrada);

            if ($sql->execute()) {
                if ($sql->affected_rows > 0) {
                    // Inserir automaticamente no histórico de estoque
                    $idProduto = $sql->insert_id;
                    $tipoTransacao = 'Entrada';
                    $atualQuantidade = $quantidade;

                    $sqlHistorico = $conexao->prepare("
                        INSERT INTO historico_estoque (ID_Produto, Data, Quantidade, Tipo_Transacao, Atual_quantidade) 
                        VALUES (?, NOW(), ?, ?, ?)
                    ");
                    $sqlHistorico->bind_param("iisi", $idProduto, $quantidade, $tipoTransacao, $atualQuantidade);

                    if ($sqlHistorico->execute()) {
                        $_SESSION['msg'] = "Produto cadastrado com sucesso!";
                    } else {
                        $_SESSION['msg'] = "Erro ao atualizar histórico de estoque: " . $sqlHistorico->error;
                    }

                    $sqlHistorico->close();
                } else {
                    $_SESSION['msg'] = "Nenhuma linha foi inserida. Verifique os dados.";
                }
            } else {
                $_SESSION['msg'] = "Erro ao adicionar produto: " . $sql->error;
            }

            $sql->close();
        }

        $conexao->close();
    } else {
        $_SESSION['msg'] = "Preencha todos os campos obrigatórios corretamente!";
    }

    // Redireciona de volta para cadastrarProduto.php com a mensagem
    header("Location: ../HTML/gestaoProdutos.php");
    exit();
}
?>
