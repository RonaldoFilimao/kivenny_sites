<?php
session_start();
include_once('config.php');


if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Verifica se o ID_Produto foi enviado via GET
if (isset($_GET['id'])) {
    $ID_Produto = $_GET['id'];
    $_SESSION['ID_Produto'] = $ID_Produto; // Armazena na sessão para segurança
} elseif (isset($_SESSION['ID_Produto'])) {
    $ID_Produto = $_SESSION['ID_Produto']; // Usa o ID da sessão se não veio via GET
} else {
    echo "Erro: Produto não especificado!";
    exit();
}

// Recupera os dados do produto
$sql = "SELECT * FROM produto WHERE ID_Produto=?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $ID_Produto);
$stmt->execute();
$result = $stmt->get_result();
$produto = $result->fetch_assoc();
$stmt->close();

if (!$produto) {
    echo "Produto não encontrado!";
    exit();
}

// Se o formulário for enviado, faz o update no banco
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar'])) {
    if ($_SESSION['ID_Produto'] != $_POST['id']) {
        echo "Erro: ID do produto inválido!";
        exit();
    }

    $id = $_POST['id'];
    $nome = $_POST['nomeProduto'];
    $descricao = $_POST['descricao'];
    $quantidade = $_POST['Quantidade'];
    $preco = $_POST['preco'];
    $dataEntrada = $_POST['dataEntrada'];
    $tipoTransacao = 'Actualizado'; // Define o tipo de transação

    // Atualiza os dados do produto
    $sql_update = "UPDATE produto SET Nome_Produto=?, Descricao=?, Quantidade=?, Preco=?, Data_Entrada=? WHERE ID_Produto=?";
    $stmt_update = $conexao->prepare($sql_update);
    $stmt_update->bind_param("ssidsi", $nome, $descricao, $quantidade, $preco, $dataEntrada, $id);

    if ($stmt_update->execute()) {
        // Registra no histórico a edição do produto
        $sql_historico = "INSERT INTO historico_estoque (ID_Produto, Data, Quantidade, Tipo_Transacao, Atual_quantidade) VALUES (?, NOW(), ?, ?, ?)";
        $stmt_historico = $conexao->prepare($sql_historico);
        $stmt_historico->bind_param("iisi", $id, $quantidade, $tipoTransacao, $quantidade);
        
        if ($stmt_historico->execute()) {
            echo "Produto atualizado com sucesso!";
            header("Location: VisualizarProdutos.php"); // Redireciona após a atualização
            exit();
        } else {
            echo "Erro ao registrar histórico: " . $stmt_historico->error;
        }

        $stmt_historico->close();
    } else {
        echo "Erro ao atualizar produto: " . $stmt_update->error;
    }

    $stmt_update->close();
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
</head>
<body>
    <div class="container">
        <h2>Editar Produto</h2>
        <form method="POST" action="">
            <input type="hidden" name="id" value="<?php echo $produto['ID_Produto']; ?>">
            
            <label for="nomeProduto">Nome do Produto:</label>
            <input type="text" id="nomeProduto" name="nomeProduto" value="<?php echo $produto['Nome_Produto']; ?>" required>
            
            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" required><?php echo $produto['Descricao']; ?></textarea>
            
            <label for="Quantidade">Quantidade:</label>
            <input type="number" id="Quantidade" name="Quantidade" value="<?php echo $produto['Quantidade']; ?>" required>
            
            <label for="preco">Preço:</label>
            <input type="number" id="preco" name="preco" step="0.01" value="<?php echo $produto['Preco']; ?>" required>
            
            <label for="dataEntrada">Data de Entrada:</label>
            <input type="date" id="dataEntrada" name="dataEntrada" value="<?php echo $produto['Data_Entrada']; ?>" required>
            
            <button type="submit" name="editar">Salvar Alterações</button>
        </form>
        <a href="VisualizarProdutos.php" class="btn-cancel">Cancelar</a>
    </div>
</body>
</html>


<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .container {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 350px;
        text-align: center;
    }

    h2 {
        color: #333;
    }

    label {
        display: block;
        text-align: left;
        margin: 10px 0 5px;
        font-weight: bold;
    }

    input, textarea {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    button {
        background-color: #28a745;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
    }

    button:hover {
        background-color: #218838;
    }

    .btn-cancel {
        display: block;
        margin-top: 10px;
        color: #dc3545;
        text-decoration: none;
        font-weight: bold;
    }

    .btn-cancel:hover {
        color: #c82333;
    }
</style>
