<?php
include_once('config.php');
require('fpdf/fpdf.php'); // Inclua a biblioteca FPDF

// Função para gerar relatório de vendas
function gerarRelatorioVendas($conexao) {
    $sql = "SELECT
                iv.ID_Produto,
                p.Nome_Produto,
                p.Preco AS Preco_Produto,
                iv.ID_Item,
                iv.Quantidade AS Quantidade_Item,
                iv.Preco_Total AS Preco_Item,
                v.ID_Venda,
                v.Data_Venda,
                v.Total_Venda,
                v.IVA,
                v.TotalComIva,
                c.Nome_Cliente,
                c.Nuit,
                c.Telefone AS Cliente_Telefone,
                u.Nome AS Nome_Usuario,
                u.Email AS Usuario_Email,
                h.Tipo_Transacao,
                h.Data AS Data_Transacao,
                h.Atual_quantidade AS Quantidade_Atual
            FROM
                venda v
            JOIN
                clientes c ON v.ID_Cliente = c.ID_Cliente
            JOIN
                usuarios u ON v.ID_Usuario = u.ID_Usuario
            JOIN
                itens_venda iv ON v.ID_Venda = iv.ID_Venda
            JOIN
                produto p ON iv.ID_Produto = p.ID_Produto
            LEFT JOIN (
                SELECT
                    ID_Produto,
                    Tipo_Transacao,
                    Data,
                    Atual_quantidade,
                    ROW_NUMBER() OVER (PARTITION BY ID_Produto ORDER BY Data DESC) AS rn
                FROM
                    historico_estoque
            ) h ON p.ID_Produto = h.ID_Produto AND h.rn = 1
            ORDER BY
                v.Data_Venda DESC  -- Ordenando pela data de venda em ordem decrescente
            LIMIT 7000";

    if ($stmt = $conexao->prepare($sql)) {
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Criação do objeto FPDF
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetTitle('Relatório de Vendas');
            $pdf->SetFont('Arial', 'B', 12);

            // Título do relatório com tratamento UTF-8
            $pdf->Cell(0, 10, utf8_decode('****************** Relatório de Vendas ******************'), 0, 1, 'C');
            $pdf->Ln(10);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 10, utf8_decode('Data de Geração: ' . date("Y-m-d H:i:s")), 0, 1, 'C');
            $pdf->Ln(10);

            // Cabeçalho da tabela (horizontal)
            $pdf->Cell(16, 7, "ID_Venda", 1, 0, 'C');
            $pdf->Cell(30, 7, "Data_Venda", 1, 0, 'C');
            $pdf->Cell(30, 7, "T.Venda(MZN)", 1, 0, 'C');
            $pdf->Cell(20, 7, "IVA(MZN)", 1, 0, 'C');
            $pdf->Cell(30, 7, "T.Com Iva(MZN)", 1, 0, 'C');
            $pdf->Cell(40, 7, "N. Cliente", 1, 0, 'C');
            $pdf->Cell(30, 7, "Tel", 1, 0, 'C');
            $pdf->Cell(30, 7, "Nome Produto", 1, 0, 'C');
            $pdf->Cell(20, 7, "Preco Produto(MZN)", 1, 0, 'C');
            $pdf->Cell(20, 7, "Quant. Item", 1, 1, 'C');

            // Inicializando as variáveis para somar os totais
            $totalVendaGeral = 0;
            $ivaGeral = 0;
            $totalComIvaGeral = 0;

            // Inserir os dados das vendas
            $pdf->SetFont('Arial', '', 9);
            while ($row = $result->fetch_assoc()) {
                $totalVenda = $row['Total_Venda'];
                $iva = $row['IVA'];
                $totalComIva = $row['TotalComIva'];
                $precoProduto = number_format($row['Preco_Produto'], 2, '.', ',');

                // Somando os valores
                $totalVendaGeral += $totalVenda;
                $ivaGeral += $iva;
                $totalComIvaGeral += $totalComIva;

                // Tratar caracteres especiais para evitar problemas no PDF
                $nomeCliente = utf8_decode(htmlspecialchars($row['Nome_Cliente'], ENT_QUOTES, 'UTF-8'));
                $nomeProduto = utf8_decode(htmlspecialchars($row['Nome_Produto'], ENT_QUOTES, 'UTF-8'));
                $telefoneCliente = utf8_decode(htmlspecialchars($row['Cliente_Telefone'], ENT_QUOTES, 'UTF-8'));

                $pdf->Cell(16, 7, $row['ID_Venda'], 1, 0, 'C');
                $pdf->Cell(30, 7, $row['Data_Venda'], 1, 0, 'C');
                $pdf->Cell(30, 7, number_format($totalVenda, 2, '.', ','), 1, 0, 'C');
                $pdf->Cell(20, 7, number_format($iva, 2, '.', ','), 1, 0, 'C');
                $pdf->Cell(30, 7, number_format($totalComIva, 2, '.', ','), 1, 0, 'C');
                $pdf->Cell(40, 7, $nomeCliente, 1, 0, 'C');
                $pdf->Cell(30, 7, $telefoneCliente, 1, 0, 'C');
                $pdf->Cell(30, 7, $nomeProduto, 1, 0, 'C');
                $pdf->Cell(20, 7, $precoProduto, 1, 0, 'C');
                $pdf->Cell(20, 7, $row['Quantidade_Item'], 1, 1, 'C');
            }

            // Exibindo os totais
            $pdf->Cell(16, 7, "", 0, 0, 'C');
            $pdf->Cell(30, 7, "Totais:", 1, 0, 'C');
            $pdf->Cell(30, 7, number_format($totalVendaGeral, 2, '.', ','), 1, 0, 'C');
            $pdf->Cell(20, 7, number_format($ivaGeral, 2, '.', ','), 1, 0, 'C');
            $pdf->Cell(30, 7, number_format($totalComIvaGeral, 2, '.', ','), 1, 0, 'C');
            $pdf->Cell(40, 7, "", 0, 0, 'C');
            $pdf->Cell(30, 7, "", 0, 0, 'C');
            $pdf->Cell(30, 7, "", 0, 0, 'C');
            $pdf->Cell(20, 7, "", 0, 1, 'C');

            // Rodapé do relatório
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(0, 10, '*************** Fim do Relatório de Vendas ***************', 0, 1, 'C');

            // Gerar o PDF
            $caminhoRelatorio = 'Relatorios/relatorio_vendas_' . date("Ymd_His") . '.pdf';
            $pdf->Output('F', $caminhoRelatorio);

            // Oferecer o download do arquivo gerado
            if (file_exists($caminhoRelatorio)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($caminhoRelatorio) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($caminhoRelatorio));
                readfile($caminhoRelatorio);
                exit;
            }
        } else {
            return "Nenhuma venda disponível para gerar relatório.";
        }
    } else {
        return "Erro ao preparar a consulta SQL.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar_relatorio'])) {
    gerarRelatorioVendas($conexao);
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Vendas</title>
    <style>
        /* Estilos do formulário e botões */
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
            background-color: dodgerblue;
            color: white;
            padding: 15px 30px;
            font-size: 1.2em;
            cursor: pointer;
            border: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0px 4px 8px rgba(220, 218, 218, 0.3);
        }

        button:hover {
            background-color: deepskyblue;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Relatório de Vendas</h1>
        <form method="POST">
            <button type="submit" name="gerar_relatorio">Gerar Relatório das Vendas</button>
        </form>
    </div>

    <footer>
        <p>&copy; Sistema de Venda de Produtos - Ronaldo R. Filimão - Todos os direitos reservados</p>
    </footer>
</body>
</html>
