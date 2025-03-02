<?php
include_once('config.php');

// Verifica se há algo no campo de busca
$searchQuery = '';
if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    $sql = "SELECT produto.ID_Produto, produto.Nome_Produto, produto.Preco, produto.Data_Entrada, produto.quantidade, categoria.Nome_Categoria
            FROM produto
            INNER JOIN categoria ON produto.ID_Categoria = categoria.ID_Categoria
            WHERE produto.Nome_Produto LIKE '%$searchQuery%' OR categoria.Nome_Categoria LIKE '%$searchQuery%'";
} else {
    $sql = "SELECT produto.ID_Produto, produto.Nome_Produto, produto.Preco, produto.Data_Entrada, produto.quantidade, categoria.Nome_Categoria
            FROM produto
            INNER JOIN categoria ON produto.ID_Categoria = categoria.ID_Categoria";
}

$result = $conexao->query($sql);

echo "<div class='container'>";
echo "<h1> Produtos Disponíveis</h1>";
echo "<form method='GET' action=''>
        <input type='text' name='search' placeholder='Buscar por nome ou categoria...' value='$searchQuery' class='search-bar'>
        <button type='submit'>Buscar</button>
      </form>";

if ($result->num_rows > 0) {
    echo "<table class='tabela-produtos'>
            <tr><th>Nome do Produto</th><th>Categoria</th><th>Preço</th><th>Data de Entrada</th><th>Quantidade</th><th>Estado</th><th>Ações</th></tr>";

    while ($row = $result->fetch_assoc()) {
        // Verifica se o produto está ativo (quantidade > 0)
        $isActive = $row['quantidade'] > 0;
        $estado = $isActive ? 'Disponível' : 'Indisponível';
        $btnClass = $isActive ? 'btn-vender' : 'btn-vender-disabled';

        echo "<tr>
                <td>{$row['Nome_Produto']}</td>
                <td>{$row['Nome_Categoria']}</td>
                <td>MZN {$row['Preco']}</td>
                <td>{$row['Data_Entrada']}</td>
                <td>{$row['quantidade']}</td>
                <td>$estado</td>
                <td>";
                
                // Exibe o botão "Vender" apenas se o produto estiver ativo
                if ($isActive) {
                    echo "<a href='../PHP/formularioVenda.php?id={$row['ID_Produto']}' class='$btnClass'>Vender</a>";
                } else {
                    echo "<button class='$btnClass' disabled>Indisponível</button>";
                }
                
        echo "</td></tr>";
    }

    echo "</table>";
} else {
    echo "<p>Nenhum produto encontrado.</p>";
}

echo "</div>";
?>

<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f7fc;
        color: #333;
        padding: 40px;
    }

    .container {
        width: 80%;
        margin: 0 auto;
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: center;
        color: #2C3E50;
        margin-bottom: 30px;
        font-size: 2.5rem;
    }

    /* Barra de pesquisa */
    .search-bar {
        width: 250px;
        padding: 8px;
        margin-right: 2px;
        border: 1px solid #ccc;
        border-radius: 20px;
    }

    .search-bar:focus {
        border-color: #3498db;
    }

    button {
        padding: 8px 16px;
        font-size: 1rem;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    button:hover {
        background-color: #2980b9;
    }

    /* Estilos da tabela */
    .tabela-produtos {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .tabela-produtos th, .tabela-produtos td {
        padding: 12px;
        text-align: left;
        font-size: 1rem;
    }

    .tabela-produtos th {
        background-color: #3498db;
        color: white;
    }

    .tabela-produtos tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .tabela-produtos tr:hover {
        background-color: rgb(15, 241, 218);
        color: white;
    }

    .tabela-produtos td {
        border-bottom: 1px solid #ddd;
    }

    .tabela-produtos td, .tabela-produtos th {
        border-radius: 8px;
    }

    /* Estilo do botão Vender */
    .btn-vender {
        padding: 8px 16px;
        background-color: #27ae60;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }

    .btn-vender:hover {
        background-color: #2ecc71;
    }

    /* Estilo do botão desativado */
    .btn-vender-disabled {
        background-color: #e74c3c;
        cursor: not-allowed;
    }

    /* Estilo do botão desativado */
    .btn-vender:disabled {
        background-color: #e74c3c;
        cursor: not-allowed;
    }
</style>
