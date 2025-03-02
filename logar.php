<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

session_start();
include_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        echo json_encode(['status' => 'error', 'message' => 'Por favor, preencha todos os campos.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Email inválido.']);
        exit;
    }

    if ($conexao->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Erro na conexão com o banco de dados.']);
        exit;
    }

    // Consulta para recuperar dados do usuário
    $sql = $conexao->prepare("SELECT id_usuario, nome, senha, id_role FROM usuarios WHERE email = ?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $sql->store_result();

    if ($sql->num_rows > 0) {
        $sql->bind_result($idUsuario, $nomeUsuario, $senhaCriptografada, $idRole);
        $sql->fetch();

        if (password_verify($senha, $senhaCriptografada)) {
            $token = bin2hex(random_bytes(32));
            $validadeToken = date('Y-m-d H:i:s', strtotime('+3 minutes'));
            $ultimaAtividade = date('Y-m-d H:i:s');
            $dataInicio = date('Y-m-d H:i:s');
            $statusSessao = (strtotime($validadeToken) > time()) ? 'ativa' : 'expirado';
            $ipUsuario = $_SERVER['REMOTE_ADDR'];
            $navegador = $_SERVER['HTTP_USER_AGENT'];
            $dispositivo = (stripos($navegador, 'Mobile') !== false) ? 'Mobile' : 'Desktop';

            // Inserir dados da sessão no banco
            $sqlSessao = $conexao->prepare("INSERT INTO sessao (id_usuario, data_inicio, token_sessao, ip_usuario, navegador, dispositivo, status_sessao, validade_token, ultima_atividade) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sqlSessao->bind_param("issssssss", $idUsuario, $dataInicio, $token, $ipUsuario, $navegador, $dispositivo, $statusSessao, $validadeToken, $ultimaAtividade);

            if ($sqlSessao->execute()) {
                // Definindo cookies com status dinâmico
                setcookie('id_usuario', $idUsuario, time() + 3600, "/", "", false, true);
                setcookie('email', $email, time() + 3600, "/", "", false, true);
                setcookie('token', $token, time() + 3600, "/", "", false, true);
               setcookie('id_role', $idRole, time() + 3600, "/", "", false, true);
                setcookie('status', $statusSessao, time() + 3600, "/", "", false, true);
                setcookie('validade_token', $validadeToken, time() + 3600, "/", "", false, true);
                setcookie('ultima_atividade', $ultimaAtividade, time() + 3600, "/", "", false, true);

                // Armazenando nome de usuário na sessão
                $_SESSION['nome_usuario'] = $nomeUsuario; // Armazena o nome do usuário na sessão
                $_SESSION['id_usuario'] = $idUsuario; // Armazena o id do usuário na sessão
                $_SESSION['id_role'] = $idRole; // Armazena o papel do usuário na sessão

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login bem-sucedido',
                    'redirect' => ($idRole == 1) ? "../PHP/AdminSistema.php" : "../PHP/VendedorSistema.php"
                ]);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao iniciar a sessão.']);
                exit;
            }

            $sqlSessao->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Senha inválida.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email não encontrado.']);
        exit;
    }

    $sql->close();
    $conexao->close();
}
?>
