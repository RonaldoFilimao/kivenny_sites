<?php
include_once('config.php');

// Função para excluir uma sessão
if (isset($_GET['delete'])) {
    $id_sessao = $_GET['delete'];
    $deleteSql = "DELETE FROM sessao WHERE id_sessao = ?";
    $deleteStmt = $conexao->prepare($deleteSql);
    $deleteStmt->bind_param("i", $id_sessao);
    if ($deleteStmt->execute()) {
        echo "<script>alert('Sessão excluída com sucesso!'); window.location.href='visualizar_sessoes.php';</script>";
    } else {
        echo "<script>alert('Erro ao excluir a sessão.'); window.location.href='visualizar_sessoes.php';</script>";
    }
}

// Consulta para obter os dados da tabela 'sessao'
$sql = "SELECT id_sessao, id_usuario, data_inicio, token_sessao, status_sessao, ultima_atividade, validade_token FROM sessao";
$result = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Sessões</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            color: #333;
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #2C3E50;
            margin-bottom: 30px;
        }

        /* Estilos do botão Voltar */
        .voltar-btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 5px;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .voltar-btn:hover {
            background-color: #2980b9;
        }

        /* Estilos da tabela */
        .tabela-sessoes {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .tabela-sessoes th, .tabela-sessoes td {
            padding: 15px;
            text-align: left;
            font-size: 1rem;
        }

        .tabela-sessoes th {
            background-color: #3498db;
            color: white;
        }

        .tabela-sessoes tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .tabela-sessoes tr:hover {
            background-color: rgb(15, 241, 218);
            color: white;
        }

        .tabela-sessoes td {
            border-bottom: 1px solid #ddd;
        }

        .tabela-sessoes td, .tabela-sessoes th {
            border-radius: 8px;
        }

        /* Estilo do botão excluir */
        .deletar {
            color: white;
            background-color: #e74c3c;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
        }

        .deletar:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

    <!-- Botão Voltar -->
    <a href="AdminSistema.php" class="voltar-btn">Voltar</a>

    <h1>Visualizar Sessões</h1>

    <?php
    if ($result->num_rows > 0) {
        echo '<table class="tabela-sessoes">';
        echo '<tr><th>Data Início</th><th>Token Sessão</th><th>Status Sessão</th><th>Última Atividade</th><th>Validade Token</th><th>Ações</th></tr>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            //echo '<td>' . $row['id_sessao'] . '</td>';
            //echo '<td>' . $row['id_usuario'] . '</td>';
            echo '<td>' . $row['data_inicio'] . '</td>';
            echo '<td>' . $row['token_sessao'] . '</td>';
            echo '<td>' . $row['status_sessao'] . '</td>';
            echo '<td>' . $row['ultima_atividade'] . '</td>';
            echo '<td>' . $row['validade_token'] . '</td>';
            echo '<td><a href="?delete=' . $row['id_sessao'] . '" onclick="return confirm(\'Tem certeza que deseja excluir?\')" class="deletar">Excluir</a></td>';
            echo '</tr>';
        }

        echo '</table>';
    } else {
        echo '<p>Nenhuma sessão encontrada.</p>';
    }
    ?>

</body>
</html>
