<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dados dos Usuários</title>
    <style>
        body {
            font-family: 'Arial';
            background-color: #f4f7fc;
            color: #333;
            padding: 5px;
        }

        /* Botão Voltar */
        .voltar {
            display: inline-block;
            margin-bottom: 20px;
            
        }

        .voltar a {
            text-decoration: none;
            font-size: 1rem;
            color: white;
            background-color: #3498db;
            padding: 10px 20px;
            border-radius: 20px;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .voltar a:hover {
            background-color: #2980b9;
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
            margin-right: 10px;
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
            margin-top: 15px;
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
            border-radius: 4px;
        }

        /* Botão Adicionar */
        .adicionar {
    margin-top: 1px;
    display: inline-block;
    padding: 10px 20px;
    font-size: 1rem;
    background-color: #3498db;
    border-radius: 20px;
    transition: background-color 0.3s ease;
}


.adicionar a {
    text-decoration: none;  
    color: white;
    
}


        .adicionar:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Usuários</h1>

        <!-- Botão Adicionar -->
        <div class="adicionar">
            <a href="../HTML/formularioCadastro.html">Adicionar Novo Usuário</a>
        </div>

        <!-- Botão Sessões -->
        <div class="voltar">
            <a href="visualizar_sessoes.php" class="voltar">Sessões</a>
        </div>


        <!-- Botão Voltar -->
        <div class="voltar">
            <a href="AdminSistema.php">Voltar</a>
        </div>
        <!-- Formulário de pesquisa com botão -->
        <form method="POST">
            <input type="text"  id="search" name="search" class="search-bar" placeholder="Pesquisar por nome..." value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
            <button type="submit">Pesquisar</button>
        </form>

        <?php
include_once('config.php');

// Função para excluir um usuário
if (isset($_GET['delete'])) {
    $id_usuario = $_GET['delete'];
    $deleteSql = "DELETE FROM usuarios WHERE ID_Usuario = ?";
    $deleteStmt = $conexao->prepare($deleteSql);
    $deleteStmt->bind_param("i", $id_usuario);
    $deleteStmt->execute();
    echo "<script>alert('Usuário excluído com sucesso!'); window.location.href='visualizar.php';</script>";
}

// Definindo o termo de pesquisa, se houver
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

// Consulta para obter os usuários com base na pesquisa, incluindo o nome do nível de usuário
$sql = "SELECT u.ID_Usuario, u.ID_Role, u.Nome, u.Email, u.Telefone, u.Sexo, u.DataNascimento, u.Cidade, u.Pais, r.Nome_Role, r.Nivel
        FROM usuarios u
        JOIN role r ON u.ID_Role = r.ID_Role
        WHERE u.Nome LIKE ?";
$stmt = $conexao->prepare($sql);
$searchTerm = '%' . $searchTerm . '%'; // Adiciona os coringas para a busca
$stmt->bind_param("s", $searchTerm); // Binding do parâmetro
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<table class="tabela-produtos">';
    echo '<tr><th>ID</th><th>Nome</th><th>Email</th><th>Telefone</th><th>Sexo</th><th>Data Nascimento</th><th>Cidade</th><th>País</th><th>Nível</th><th>Ações</th></tr>';

    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['ID_Usuario'] . '</td>';
        echo '<td>' . $row['Nome'] . '</td>';
        echo '<td>' . $row['Email'] . '</td>';
        echo '<td>' . $row['Telefone'] . '</td>';
        echo '<td>' . $row['Sexo'] . '</td>';
        echo '<td>' . $row['DataNascimento'] . '</td>';
        echo '<td>' . $row['Cidade'] . '</td>';
        echo '<td>' . $row['Pais'] . '</td>';
        echo '<td>' . $row['Nome_Role'] . '</td>'; // Exibe o nome do nível
        echo '<td>
                <a href="?delete=' . $row['ID_Usuario'] . '" onclick="return confirm(\'Tem certeza que deseja excluir?\')" class="deletar">Excluir</a>
              </td>';
        echo '</tr>';
    }

    echo '</table>';
} else {
    echo '<p>Nenhum usuário encontrado.</p>';
}
?>

    </div>

</body>
</html>
