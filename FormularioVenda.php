<?php
// Incluir a configuração de conexão ao banco de dados
include_once('config.php');

// Iniciar a sessão para verificar o usuário logado
session_start();

// Verifica se os cookies necessários estão definidos
if (!isset($_COOKIE['id_usuario']) || !isset($_COOKIE['validade_token'])) {
    header("Location: ../HTML/login.html");
    exit;
}

// Verifique se o usuário está autenticado e se a sessão contém um ID válido
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    die("Erro: Usuário não está autenticado.");
}

// Processamento do cadastro de cliente
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nome_cliente'])) {
    $nome_cliente = htmlspecialchars($_POST['nome_cliente']);  // Previne XSS
    $telefone = htmlspecialchars($_POST['telefone']);
    $endereco = htmlspecialchars($_POST['endereco']);
    $nuit = htmlspecialchars($_POST['nuit']);

    // Verificar se o cliente já existe no banco de dados (com base no NUIT)
    $sql_check = "SELECT ID_Cliente FROM clientes WHERE NUIT = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("s", $nuit);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $message = "Erro: Cliente já existe!";
    } else {
        $sql = "INSERT INTO clientes (Nome_Cliente, Telefone, Endereco, NUIT) 
                VALUES (?, ?, ?, ?)";
        if ($stmt = $conexao->prepare($sql)) {
            $stmt->bind_param("ssss", $nome_cliente, $telefone, $endereco, $nuit);
            if ($stmt->execute()) {
                $message = "Cliente cadastrado com sucesso!";
            } else {
                $message = "Erro ao cadastrar cliente: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Erro ao preparar a consulta: " . $conexao->error;
        }
    }
    $stmt_check->close();
}

// Consultar todos os clientes cadastrados
$sql_clientes = "SELECT ID_Cliente, Nome_Cliente, Telefone, Endereco, NUIT FROM clientes";
$result_clientes = $conexao->query($sql_clientes);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro e Registro de Venda</title>
    <link rel="stylesheet" href="../CSS/FormularioVenda.css">
    <script src="../JS/jquery.js"></script>

    <script>
        $(document).ready(function () {
            // Mostrar formulário de cadastro
            $('#btnCadastroCliente').click(function () {
                $('#div_cliente').show();
                $('#div_cliente_exibir').hide();
                $('#div_venda').hide();
                $('#div_tabela_clientes').hide();
            });

            // Mostrar tabela de clientes
            $('#btnExibirClientesTabela').click(function () {
                $('#div_cliente').hide();
                $('#div_cliente_exibir').hide();
                $('#div_venda').hide();
                $('#div_tabela_clientes').show();
            });

            // Mostrar formulário de venda
            $('#btnRegistrarVenda').click(function () {
                $('#div_cliente').hide();
                $('#div_cliente_exibir').hide();
                $('#div_tabela_clientes').hide();
                $('#div_venda').show();
            });
        });

        // Função de Pesquisa
        function searchClient() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById('search-box');
            filter = input.value.toLowerCase();
            table = document.getElementById('client-list');
            tr = table.getElementsByTagName('tr');
            var noResults = true;

            // Loop através das linhas da tabela e esconder aquelas que não correspondem ao filtro
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName('td')[1]; // A coluna 1 é o nome do cliente
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        noResults = false;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }

            // Exibir mensagem de "Nenhum cliente encontrado" caso não haja resultados
            var messageRow = document.getElementById('no-results');
            if (noResults) {
                messageRow.style.display = "";
            } else {
                messageRow.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <h1>Cadastro e Registro de Venda</h1>

    <!-- Botões de Navegação -->
    <div id="botao_menu">
        <button id="btnCadastroCliente">Cadastrar Cliente</button>
        <button id="btnExibirClientesTabela">Exibir Clientes em Tabela</button>
        <button id="btnRegistrarVenda">Registrar Venda</button>
    </div>

    <!-- Div Cadastro Cliente -->
    <div id="div_cliente" style="display: none;">
        <h2>Cadastrar Cliente</h2>
        <form action="" method="POST">
            <label for="nome_cliente">Nome do Cliente:</label>
            <input type="text" id="nome_cliente" name="nome_cliente" required><br><br>

            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" required><br><br>

            <label for="endereco">Endereço:</label>
            <textarea id="endereco" name="endereco" required></textarea><br><br>

            <label for="nuit">NUIT:</label>
            <input type="text" id="nuit" name="nuit" required><br><br>

            <button type="submit">Cadastrar Cliente</button>
        </form>
        <?php if ($message) echo "<p>$message</p>"; ?>
    </div>

    <!-- Div Tabela de Clientes -->
    <div id="div_tabela_clientes" style="display: none;">
        <h2>Lista de Clientes</h2>

        <!-- Caixa de Pesquisa -->
        <label for="search-box">Pesquisar Cliente:</label>
        <input type="text" id="search-box" placeholder="Digite o nome do cliente" onkeyup="searchClient()"><br><br>

        <table border="1" cellspacing="0" cellpadding="5">
            <thead>
                <tr>
                    <th>ID Cliente</th>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Endereço</th>
                    <th>NUIT</th>
                </tr>
            </thead>
            <tbody id="client-list">
                <?php
                if ($result_clientes->num_rows > 0) {
                    while ($row = $result_clientes->fetch_assoc()) {
                        echo "<tr class='client-row'>
                                <td>{$row['ID_Cliente']}</td>
                                <td>{$row['Nome_Cliente']}</td>
                                <td>{$row['Telefone']}</td>
                                <td>{$row['Endereco']}</td>
                                <td>{$row['NUIT']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Nenhum cliente encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <p id="no-results" style="display:none;">Nenhum cliente encontrado.</p>
    </div>

    <!-- Div Registrar Venda -->
    <div id="div_venda" style="display: none;">
        <h2>Registrar Venda</h2>
        <form action="processar_venda.php" method="POST">
            <label for="id_cliente">ID do Cliente:</label>
            <input type="text" id="id_cliente" name="id_cliente" required><br><br>

            <label for="id_produto">Produto:</label>
            <select id="id_produto" name="id_produto" required>
                <option value="">Selecione um Produto</option>
                <?php
                $sql = "SELECT ID_Produto, Nome_Produto FROM produto";
                $result = $conexao->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['ID_Produto']}'>{$row['Nome_Produto']}</option>";
                    }
                } else {
                    echo "<option value=''>Nenhum produto disponível</option>";
                }
                ?>
            </select><br><br>

            <label for="quantidade">Quantidade:</label>
            <input type="number" id="quantidade" name="quantidade" min="1" required><br><br>

            <input type="hidden" id="id_usuario" name="id_usuario" value="<?php echo $_SESSION['id_usuario']; ?>">
            <button type="submit">Registrar Venda</button>
        </form>
    </div>
</body>
</html>
