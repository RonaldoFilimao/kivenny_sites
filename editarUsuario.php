<?php
// Conexão com o banco de dados
include_once('config.php');

// Verifica a conexão com o banco de dados
if ($conexao->connect_error) {
    die("Conexão falhou: " . $conexao->connect_error);
}

// Se a requisição for para editar o usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $sexo = $_POST['sexo'];
    $cidade = $_POST['cidade'];
    $pais = $_POST['pais'];
    $dataNascimento = $_POST['dataNascimento'];
    $nivel = $_POST['nivel'];  // Aqui está o valor que você está atualizando para o campo ID_Role
    $id_usuario = $_GET['id']; // O ID do usuário é passado pela URL

    // Verificar se o valor de $nivel é válido
    if ($nivel != '0' && $nivel != '1') {
        echo "Valor inválido para nível!";
        exit();
    }

    // Atualiza os dados do usuário no banco
    $sql = $conexao->prepare("UPDATE usuarios SET Nome = ?, Email = ?, Telefone = ?, Sexo = ?, Cidade = ?, Pais = ?, DataNascimento = ?, ID_Role = ? WHERE ID_Usuario = ?");
    $sql->bind_param("ssssssssi", $nome, $email, $telefone, $sexo, $cidade, $pais, $dataNascimento, $nivel, $id_usuario);

    if ($sql->execute()) {
        // Redireciona para visualizar.php após a atualização bem-sucedida
        echo "<script>
                window.location.href = 'visualizar.php';
              </script>";
        exit(); // Evita que o código continue executando após o redirecionamento
    } else {
        echo "Erro ao atualizar usuário: " . $sql->error;
    }

    $sql->close();
}


// Obtém os dados do usuário para editar
$id_usuario = $_GET['id'];
$sql = $conexao->prepare("SELECT * FROM usuarios WHERE ID_Usuario = ?");
$sql->bind_param("i", $id_usuario);
$sql->execute();
$user = $sql->get_result()->fetch_assoc();
$sql->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="../CSS/editarUsuario.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Editar Usuário</h1>
        </header>

        <form action="editarUsuario.php?id=<?php echo $user['ID_Usuario']; ?>" method="POST">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?php echo $user['Nome']; ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $user['Email']; ?>" required>

            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" value="<?php echo $user['Telefone']; ?>" required>

            <label for="sexo">Sexo:</label>
            <select id="sexo" name="sexo" required>
                <option value="M" <?php echo ($user['Sexo'] == 'M') ? 'selected' : ''; ?>>Masculino</option>
                <option value="F" <?php echo ($user['Sexo'] == 'F') ? 'selected' : ''; ?>>Feminino</option>
                <option value="Outro" <?php echo ($user['Sexo'] == 'Outro') ? 'selected' : ''; ?>>Outro</option>
            </select>

            <label for="cidade">Cidade:</label>
            <input type="text" id="cidade" name="cidade" value="<?php echo $user['Cidade']; ?>" required>

            <label for="pais">País:</label>
            <input type="text" id="pais" name="pais" value="<?php echo $user['Pais']; ?>" required>

            <label for="dataNascimento">Data de Nascimento:</label>
            <input type="date" id="dataNascimento" name="dataNascimento" value="<?php echo $user['DataNascimento']; ?>" required>

            <label for="nivel">Nível:</label>
            <select id="nivel" name="nivel" required>
                <option value="0" <?php echo ($user['ID_Role'] == 1) ? 'selected' : ''; ?>>Administrador</option>
                <option value="1" <?php echo ($user['ID_Role'] == 2) ? 'selected' : ''; ?>>Vendedor</option>
            </select>

            <button type="submit">Atualizar</button>
        </form>

        <a href="../Html/adminSistema.html">Cancelar</a>
    </div>
</body>
</html>
