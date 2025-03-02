<?php
session_start();
include_once('../PHP/config.php'); // Inclua a configuração da sua base de dados

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber dados do formulário
    $nome_cliente = $_POST['nome_cliente'];
    $telefone_cliente = $_POST['telefone_cliente'];
    $endereco_cliente = $_POST['endereco_cliente'];
    $nuit_cliente = $_POST['nuit_cliente'];
   // $nome_vendedor = $_SESSION['usuario_nome'];

    // Inserir dados do cliente no banco de dados
    $sqlCliente = "INSERT INTO clientes (Nome, Telefone, Endereco, NUIT) 
                   VALUES ('$nome_cliente', '$telefone_cliente', '$endereco_cliente', '$nuit_cliente')";
    if ($conexao->query($sqlCliente) === TRUE) {
        $cliente_id = $conexao->insert_id; // Pega o ID do cliente inserido
    } else {
        echo "Erro ao registrar cliente: " . $conexao->error;
    }

    // Inserir os produtos
    if (!empty($_POST['produtos'])) {
        foreach ($_POST['produtos'] as $produto) {
            $id_produto = $produto['id_produto'];
            $quantidade = $produto['quantidade'];

            $sqlVenda = "INSERT INTO vendas (ID_Cliente, ID_Produto, Quantidade)
                         VALUES ('$cliente_id', '$id_produto', '$quantidade')";
            if ($conexao->query($sqlVenda) === TRUE) {
                echo "Venda registrada com sucesso!";
            } else {
                echo "Erro ao registrar venda: " . $conexao->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Venda</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Formulário de Venda</h1>
    <form action="venda.php" method="POST">
        <!-- Dados do Cliente -->
        <h2>Dados do Cliente</h2>
        <div>
            <label for="nome_cliente">Nome do Cliente:</label>
            <input type="text" name="nome_cliente" id="nome_cliente" required>
        </div>

        <div>
            <label for="telefone_cliente">Telefone do Cliente:</label>
            <input type="text" name="telefone_cliente" id="telefone_cliente" required>
        </div>

        <div>
            <label for="endereco_cliente">Endereço do Cliente:</label>
            <input type="text" name="endereco_cliente" id="endereco_cliente" required>
        </div>

        <div>
            <label for="nuit_cliente">NUIT do Cliente:</label>
            <input type="text" name="nuit_cliente" id="nuit_cliente" required>
        </div>

        <!-- Vendedor (Puxar automaticamente após login) -->
        <div>
            <label for="nome_vendedor">Vendedor:</label>
            <input type="text" name="nome_vendedor" id="nome_vendedor" value="<?php echo $_SESSION['usuario_nome']; ?>" readonly>
        </div>

        <!-- Produtos -->
        <h3>Produtos</h3>
        <div id="produtos">
            <?php
            
            // Puxa os produtos da base de dados
            $sqlProdutos = "SELECT ID_Produto, Nome_Produto FROM produto";
            $resultProdutos = $conexao->query($sqlProdutos);
            $contador = 0;

            while ($produto = $resultProdutos->fetch_assoc()) {
                ?>
                <div class="produto-item">
                    <label for="produto_<?php echo $contador; ?>">Produto:</label>
                    <select name="produtos[<?php echo $contador; ?>][id_produto]" required>
                        <option value="<?php echo $produto['ID_Produto']; ?>"><?php echo $produto['Nome_Produto']; ?></option>
                    </select>

                    <label for="quantidade_<?php echo $contador; ?>">Quantidade:</label>
                    <input type="number" name="produtos[<?php echo $contador; ?>][quantidade]" min="1" required>
                </div>
                <?php
                $contador++;
            }
            ?>
        </div>

        <button type="button" id="adicionarProduto">Adicionar Produto</button>
        <button type="submit">Registrar Venda</button>
    </form>

    <script>
        let contadorProdutos = <?php echo $contador; ?>; // Inicia o contador com o valor atual

        document.getElementById('adicionarProduto').addEventListener('click', function() {
            let novaDiv = document.createElement('div');
            novaDiv.classList.add('produto-item');
            novaDiv.innerHTML = `
                <label for="produto_${contadorProdutos}">Produto:</label>
                <select name="produtos[${contadorProdutos}][id_produto]" required>
                    <?php
                    $resultProdutos = $conexao->query($sqlProdutos);
                    while ($produto = $resultProdutos->fetch_assoc()) {
                        echo "<option value='".$produto['ID_Produto']."'>".$produto['Nome_Produto']."</option>";
                    }
                    ?>
                </select>

                <label for="quantidade_${contadorProdutos}">Quantidade:</label>
                <input type="number" name="produtos[${contadorProdutos}][quantidade]" min="1" required>
            `;
            document.getElementById('produtos').appendChild(novaDiv);
            contadorProdutos++;
        });
    </script>
</body>
</html>
