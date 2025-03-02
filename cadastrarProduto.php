

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="../CSS/cadastrarProduto.css">
    <title>Cadastro de Produtos</title>
</head>
<body>
    <h1>Cadastro de Produtos</h1>
    <form id="produtoForm">
        <div class="form-group">
            <label for="categoria">CATEGORIA</label>
            <select id="categoria" name="categoria" required>
                <option value="">Selecione uma Categoria</option>
                <!-- Simulando dados do banco de dados -->
            </select>
        </div>
        <div class="form-group">
            <label for="nomeProduto">NOME DO PRODUTO</label>
            <input type="text" id="nomeProduto" name="nomeProduto" required>
        </div>
        <div class="form-group">
            <label for="descricao">DESCRIÇÃO</label>
            <textarea id="descricao" name="descricao" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="Quantidade">QUANTIDADE</label>
            <input type="number" id="Quantidade" name="Quantidade" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="preco">PREÇO</label>
            <input type="number" id="preco" name="preco" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="dataEntrada">DATA DE ENTRADA</label>
            <input type="date" id="dataEntrada" name="dataEntrada">
        </div>
        <div class="form-group">
            <button type="submit">Cadastrar Produto</button>
        </div>
        <a href="../PHP/adminSistema.php">Cancelar</a>
    </form>
</body>
</html>
