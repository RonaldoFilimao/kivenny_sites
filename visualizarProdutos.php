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

// Inicializa a query principal
$sql = "SELECT produto.ID_Produto, produto.Nome_Produto, produto.Quantidade, produto.Descricao, 
        produto.Preco, produto.Data_Entrada, categoria.Nome_Categoria
        FROM produto
        INNER JOIN categoria ON produto.ID_Categoria = categoria.ID_Categoria";

// Adiciona a busca na consulta, se o campo de busca não estiver vazio
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $sql .= " WHERE produto.Nome_Produto LIKE ? OR categoria.Nome_Categoria LIKE ?";
}

$stmt = $conexao->prepare($sql);
if ($stmt === false) { 
    die("Erro ao preparar a consulta: " . $conexao->error); 
}

// Vincula os parâmetros de busca, se houver
if (isset($search)) {
    $searchTerm = "%$search%";
    $stmt->bind_param('ss', $searchTerm, $searchTerm); // 'ss' para string
}

$stmt->execute();
$result = $stmt->get_result();

echo "<h1>Produtos Disponíveis</h1>";

// Barra de pesquisa
echo "<form method='GET' action=''>
        <input type='text' name='search' placeholder='Buscar por nome ou categoria...' value='" . (isset($_GET['search']) ? $_GET['search'] : '') . "' class='search-bar'>
        <button type='submit'>Buscar</button>
      </form>";

$alertas = [];  // Array para acumular mensagens de alerta

// Inicia a tabela
echo "<table class='tabela-produtos'>
        <tr>
            <th>Nome do Produto</th>
            <th>Categoria</th>
            <th>Descrição</th>
            <th>Quantidade</th>
            <th>Preço</th>
            <th>Data de Entrada</th>
            <th>Estado</th>
            <th>Ações</th>
        </tr>";

if ($result && $result->num_rows > 0) { 
    // Exibe os dados dos produtos encontrados
    while ($row = $result->fetch_assoc()) { 
        // Verifica o estado do produto baseado na quantidade
        $estado = $row['Quantidade'] > 0 ? 'Ativo' : 'Inativo';
        
        // Define o alerta conforme a quantidade
        $quantidade = $row['Quantidade'];
        $alerta = '';
        $alertaMessage = '';

        if ($quantidade <= 0) {
            $alerta = 'Estoque esgotado';
            $estado = 'Inativo';
            $alertaMessage = "{$row['Nome_Produto']} - {$alerta}";
        } elseif ($quantidade <= 10) {
            $alerta = 'Estoque crítico';
            $alertaMessage = "{$row['Nome_Produto']} - {$alerta}";
        } elseif ($quantidade <= 20) {
            $alerta = 'Estoque quase esgotando';
        } elseif ($quantidade <= 30) {
            $alerta = 'Estoque reduzido';
        }

        // Se houver alerta, acumula na lista
        if (!empty($alertaMessage)) {
            $alertas[] = $alertaMessage;
        }

        // Exibe os dados do produto
        echo "<tr>
                <td>{$row['Nome_Produto']}</td>
                <td>{$row['Nome_Categoria']}</td>
                <td>{$row['Descricao']}</td>
                <td>{$row['Quantidade']}</td>
                <td>MZN {$row['Preco']}</td>
                <td>{$row['Data_Entrada']}</td>
                <td>{$estado}</td>
                <td>
                    <a href='editarProduto.php?id={$row['ID_Produto']}' class='btn-editar'>Editar</a>
                    <a href='apagarProdutos.php?id={$row['ID_Produto']}' class='btn-apagar' onclick='return confirmarExclusao();'>Apagar</a>
                </td>
            </tr>";
    }

    // Exibe um único alerta com todos os produtos que precisam de atenção
    if (count($alertas) > 0) {
        $alertaFinal = implode("\n", $alertas);
        echo "<script>alert('$alertaFinal');</script>";
    }
} else { 
    // Exibe uma mensagem, mas mantém a estrutura da tabela
    echo "<tr><td colspan='8' class='no-results'>Nenhum produto encontrado.</td></tr>";
}

echo "</table>"; // Finaliza a tabela

$stmt->close();
$conexao->close();
?>

<script>
/*function confirmarExclusao() {
    if (confirm("Tem certeza de que deseja excluir este produto?")) {
        // Remova o redirecionamento
        return true;  // A exclusão ocorrerá, sem redirecionamento
    }
    return false;
}
*/
</script>
<!-- Botão para voltar -->
<div class="btn-container">
    <a href="../PHP/AdminSistema.php" class="btn">Voltar</a>
</div>


<style>
    body {
        font-family: 'Arial';
        background-color: #f4f7fc;
        color: #333;
        padding: 40px;
    }

    h1 {
        text-align: center;
        color: #2C3E50;
        margin-bottom: 30px;
        font-size: 2.5rem;
    }

    /* Barra de pesquisa */
    form {
        text-align: center;
        margin-bottom: 20px;
    }

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
        border-radius: 20px;
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

    /* Mensagem de "nenhum resultado" */
    .no-results {
        text-align: center;
        color: #666;
        font-size: 1.2rem;
        margin-top: 20px;
    }

    /* Botão de voltar */
    .btn-container {
        text-align: center;
        margin-top: 30px;
    }

    .btn {
        display: inline-block;
        background-color: #3498db;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 1rem;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color: #2980b9;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .tabela-produtos th, .tabela-produtos td {
            font-size: 0.9rem;
        }
        .search-bar {
            width: 100%;
            margin-bottom: 10px;
        }
        button {
            width: 100%;
        }
    }

    .btn-editar, .btn-apagar {
        display: inline-block;
        padding: 6px 12px;
        margin-right: 5px;
        text-decoration: none;
        border-radius: 5px;
        font-size: 0.9rem;
    }

    .btn-editar {
        background-color: #f39c12;
        color: white;
    }

    .btn-editar:hover {
        background-color: #e67e22;
    }

    .btn-apagar {
        background-color: #e74c3c;
        color: white;
    }

    .btn-apagar:hover {
        background-color: #c0392b;
    }
</style>
