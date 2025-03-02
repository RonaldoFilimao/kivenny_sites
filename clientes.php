<?php
include_once('config.php');

// Obter o termo de pesquisa (se houver)
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Consulta SQL para buscar clientes cujo nome começa com o termo de pesquisa
$sql = "SELECT * FROM clientes WHERE Nome_Cliente LIKE ?";
$stmt = $conexao->prepare($sql);
$searchTerm = '%' . $searchTerm . '%'; // Adiciona os coringas para a busca
$stmt->bind_param("s", $searchTerm); // Vincula o parâmetro
$stmt->execute();
$result = $stmt->get_result();

// Se houver resultados, gera a tabela com os dados
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['ID_Cliente'] . '</td>';
        echo '<td>' . $row['Nome_Cliente'] . '</td>';
        echo '<td>' . $row['Telefone'] . '</td>';
        echo '<td>' . $row['Endereco'] . '</td>';
        echo '<td>' . $row['NUIT'] . '</td>';
        echo '</tr>';
    }
} else {
    // Se não encontrar resultados, exibe uma mensagem
    echo '<tr><td colspan="5">Nenhum cliente encontrado.</td></tr>';
}
?>
