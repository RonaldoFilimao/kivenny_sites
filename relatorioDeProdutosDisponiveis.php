<?php
session_start();  

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id_usuario'])) { 
    header("Location: login.php"); 
    exit(); 
}

include_once('config.php');  
require_once('../PHP/fpdf/fpdf.php'); // Incluindo a biblioteca FPDF

// Função para gerar relatório de produtos disponíveis em PDF
function gerarRelatorioProdutosDisponiveis($conexao) {
    // Verificando e criando a pasta Relatorios caso não exista
    if (!file_exists('Relatorios')) {
        mkdir('Relatorios', 0777, true); // Cria a pasta com permissões adequadas
    }

    $sql = "SELECT produto.Nome_Produto, produto.Preco, produto.Data_Entrada, categoria.Nome_Categoria, produto.Quantidade
            FROM produto
            INNER JOIN categoria ON produto.ID_Categoria = categoria.ID_Categoria";

    $result = $conexao->query($sql);

    $totalInvestimento = 0; // Variável para armazenar o total do investimento

    if ($result->num_rows > 0) {
        // Criação do objeto FPDF
        $pdf = new FPDF();
        $pdf->SetCreator('Sistema de Produtos');
        $pdf->SetAuthor('Sistema de Produtos');
        $pdf->SetTitle(utf8_decode('Relatório de Produtos Disponíveis')); // Usando utf8_decode() para suportar caracteres especiais
        $pdf->SetSubject('Relatório de Produtos');
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        // Definir a fonte padrão da FPDF (sem a necessidade de .ttf)
        $pdf->SetFont('Arial', '', 12); // Arial já vem com a FPDF

        // Cabeçalho do relatório
        $pdf->Cell(0, 10, utf8_decode('Relatório de Produtos Disponíveis'), 0, 1, 'C');
        $pdf->Cell(0, 10, utf8_decode('Data de Geração: ' . date("Y-m-d H:i:s")), 0, 1, 'C');
        $pdf->Ln(10);

        // Cabeçalho da tabela (negrito)
        $pdf->SetFont('Arial', 'B', 12); // Definindo a fonte como negrito para o cabeçalho
        $pdf->Cell(60, 7, utf8_decode('Produto'), 1);
        $pdf->Cell(30, 7, utf8_decode('Categoria'), 1);
        $pdf->Cell(30, 7, utf8_decode('Preço (MZN)'), 1);
        $pdf->Cell(28, 7, utf8_decode('Data Entrada'), 1);
        $pdf->Cell(15, 7, utf8_decode('Quant.'), 1);
        $pdf->Cell(30, 7, utf8_decode('Valor Total'), 1); // Nova coluna para valor total de cada produto
        $pdf->Ln();

        // Resetando a fonte para o conteúdo normal
        $pdf->SetFont('Arial', '', 12); // Voltando para a fonte normal (sem negrito)

        // Adicionando os dados dos produtos
        while ($row = $result->fetch_assoc()) {
            // Verificando e atribuindo valores padrão caso algum campo esteja vazio
            $nome_produto = isset($row['Nome_Produto']) ? utf8_decode($row['Nome_Produto']) : 'N/A';
            $nome_categoria = isset($row['Nome_Categoria']) ? utf8_decode($row['Nome_Categoria']) : 'N/A';
            $preco = isset($row['Preco']) ? number_format($row['Preco'], 2, '.', ',') : '0,00';
            $data_entrada = isset($row['Data_Entrada']) ? utf8_decode($row['Data_Entrada']) : 'N/A';
            $quantidade = isset($row['Quantidade']) ? $row['Quantidade'] : '0';

            // Calculando o valor total de cada produto
            $valor_total = $quantidade * $row['Preco'];
            $totalInvestimento += $valor_total; // Acumulando o total do investimento

            // Preenchendo as células com os dados
            $pdf->Cell(60, 7, $nome_produto, 1);
            $pdf->Cell(30, 7, $nome_categoria, 1);
            $pdf->Cell(30, 7, $preco, 1);
            $pdf->Cell(28, 7, $data_entrada, 1);
            $pdf->Cell(15, 7, $quantidade, 1);
            $pdf->Cell(30, 7, number_format($valor_total, 2, '.', ','), 1); // Exibindo o valor total de cada produto
            $pdf->Ln(); // Quebra de linha para a próxima linha da tabela
        }

        // Linha do "Total Investimento" (negrito)
        $pdf->SetFont('Arial', 'B', 12); // Definindo a fonte como negrito para o total
        $pdf->Cell(163, 7, 'Total Investimento', 1);
        $pdf->Cell(30, 7, number_format($totalInvestimento, 2, '.', ','), 1, 0, 'C');
        $pdf->Ln();

        // Gerar o PDF
        $nomeArquivo = 'relatorio_produtos_disponiveis.pdf';
        $pdf->Output('F', 'Relatorios/' . $nomeArquivo); // Salvar o PDF na pasta "Relatorios"

        // Oferecer o download ao usuário
        if (file_exists('Relatorios/' . $nomeArquivo)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename('Relatorios/' . $nomeArquivo) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize('Relatorios/' . $nomeArquivo));
            readfile('Relatorios/' . $nomeArquivo);
            exit;
        }
    } else {
        echo "Nenhum produto disponível para gerar relatório.";
    }
}

if (isset($_POST['gerar_relatorio'])) {
    gerarRelatorioProdutosDisponiveis($conexao);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Produtos Disponíveis</title>
    <style>
        /* Resetando estilos padrão */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial';
            background-color: #f4f7fc;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }

        h1 {
            font-size: 2.5em;
            color: deepskyblue;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        button {
            background-color: deepskyblue;
            color: white;
            padding: 15px 30px;
            font-size: 1.2em;
            cursor: pointer;
            border: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background-color: dodgerblue;
            transform: scale(1.05);
        }

        button:focus {
            outline: none;
        }

        button:active {
            transform: scale(0.98);
        }

        footer {
            margin-top: 20px;
            text-align: center;
            font-size: 1em;
            color: #777;
        }

        /* Adicionando um layout para a área do botão */
        .container {
            width: 80%;
            max-width: 600px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .container h1 {
            font-size: 2.2em;
        }

        .container form {
            margin-top: 20px;
        }

        .container form button {
            margin-top: 20px;
        }

        footer p {
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Produtos Disponíveis</h1>
        <form method="POST">
            <button type="submit" name="gerar_relatorio"> Gerar Relatório de Produtos Disponíveis</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2025 Sistema de Venda de Produtos - Ronaldo R. Filimão - Todos os direitos reservados</p>
    </footer>
</body>
</html>
