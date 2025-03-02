<?php
include_once('config.php');

// Consulta para visualizar o estoque atual com os dados dos produtos
$sqlEstoqueAtual = "
    SELECT p.ID_Produto, p.Nome_Produto, p.Descricao, p.Quantidade, p.Preco, c.Nome_Categoria
    FROM produto p
    INNER JOIN categoria c ON p.ID_Categoria = c.ID_Categoria
";

$resultEstoque = $conexao->query($sqlEstoqueAtual);

// Consulta para visualizar o histórico de movimentações
$sqlHistorico = "
    SELECT h.ID_Historico, h.ID_Produto, p.Nome_Produto, h.Data, h.Quantidade, h.Tipo_Transacao, h.Atual_quantidade
    FROM historico_estoque h
    INNER JOIN produto p ON h.ID_Produto = p.ID_Produto
    ORDER BY h.Data DESC
";
$resultHistorico = $conexao->query($sqlHistorico);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Estoque</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #2C3E50;
        }

        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-box {
            width: 50%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
    <script>
        function searchTable(inputId, tableId) {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById(inputId);
            filter = input.value.toLowerCase();
            table = document.getElementById(tableId);
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");

                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }
    </script>
</head>
<body>

    <h1>Estoque Atual</h1>

    <div class="search-container">
        <input type="text" id="searchEstoque" class="search-box" onkeyup="searchTable('searchEstoque', 'tabelaEstoque')" placeholder="Pesquisar no estoque...">
    </div>

    <table id="tabelaEstoque">
        <thead>
            <tr>
                <th>ID Produto</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Descrição</th>
                <th>Quantidade</th>
                <th>Preço</th>
               
           
            </tr>
        </thead>
        <tbody>
            <?php if ($resultEstoque->num_rows > 0): ?>
                <?php while ($row = $resultEstoque->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['ID_Produto'] ?></td>
                        <td><?= $row['Nome_Produto'] ?></td>
                        <td><?= $row['Nome_Categoria'] ?></td>
                        <td><?= $row['Descricao'] ?></td>
                        <td><?= $row['Quantidade'] ?></td>
                        <td>MZN    <?= number_format($row['Preco'], 2, ',', '.') ?></td>
                        
                        
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Nenhum produto em estoque.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h1>Histórico de Estoque</h1>

    <div class="search-container">
        <input type="text" id="searchHistorico" class="search-box" onkeyup="searchTable('searchHistorico', 'tabelaHistorico')" placeholder="Pesquisar no histórico...">
    </div>

    <table id="tabelaHistorico">
        <thead>
            <tr>
                <th>ID Histórico</th>
                <th>ID Produto</th>
                <th>Nome do Produto</th>
                <th>Data</th>
                <th>Quantidade</th>
                <th>Tipo de Transação</th>
                <th>Quantidade Atual</th>
                
            </tr>
        </thead>
        <tbody>
            <?php if ($resultHistorico->num_rows > 0): ?>
                <?php while ($row = $resultHistorico->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['ID_Historico'] ?></td>
                        <td><?= $row['ID_Produto'] ?></td>
                        <td><?= $row['Nome_Produto'] ?></td>
                        <td><?= $row['Data'] ?></td>
                        <td><?= $row['Quantidade'] ?></td>
                        <td><?= $row['Tipo_Transacao'] ?></td>
                        <td><?= $row['Atual_quantidade'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Nenhuma transação encontrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php $conexao->close(); ?>
</body>
</html>

