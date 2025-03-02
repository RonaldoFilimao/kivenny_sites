<?php
// Conexão com o banco de dados
include_once('config.php');

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recupera os dados enviados
    $nome = trim($_POST['nome']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $senha = $_POST['senha'];
    $telefone = trim($_POST['telefone']);
    $sexo = $_POST['sexo'];
    $cidade = trim($_POST['cidade']);
    $pais = trim($_POST['pais']);
    $dataNascimento = $_POST['dataNascimento'];
    $nivel = intval($_POST['Nivel']);

    // Verifica a conexão com o banco de dados
    if ($conexao->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao conectar ao banco de dados.']);
        exit;
    }

    // Busca o ID_Role
    $sql = $conexao->prepare("SELECT ID_Role FROM Role WHERE Nivel = ?");
    $sql->bind_param("i", $nivel);
    $sql->execute();
    $sql->bind_result($ID_Role);
    $sql->fetch();
    $sql->close();

    if (!$ID_Role) {
        echo json_encode(['status' => 'error', 'message' => 'Nenhum ID_Role correspondente ao nível informado!']);
        exit;
    }

    // Verifica se o email já existe no banco
    $sql = $conexao->prepare("SELECT COUNT(*) FROM usuarios WHERE Email = ?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $sql->bind_result($contagem);
    $sql->fetch();
    $sql->close();

    if ($contagem > 0) {
        echo json_encode(['status' => 'error', 'message' => 'O email já está cadastrado!']);
        exit;
    }

    // Criptografa a senha
    $senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);

    // Insere os dados no banco
    $sql = $conexao->prepare("INSERT INTO usuarios (Nome, Email, Senha, Telefone, Sexo, Cidade, Pais, DataNascimento, ID_Role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $sql->bind_param("ssssssssi", $nome, $email, $senhaCriptografada, $telefone, $sexo, $cidade, $pais, $dataNascimento, $ID_Role);

    if ($sql->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Cadastro realizado com sucesso!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao cadastrar: ' . $sql->error]);
    }

    $sql->close();
    $conexao->close();
}
?>


