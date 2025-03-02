<?php
// Incluir a configuração de conexão ao banco de dados
include_once('config.php');
require('fpdf/fpdf.php'); // Inclua a biblioteca FPDF

// Definir cabeçalho para UTF-8
header('Content-Type: text/html; charset=utf-8');
$conexao->set_charset('utf8'); // Definir a codificação do banco de dados

// Função para processar a venda
function processarVenda($id_cliente, $id_produto, $quantidade, $id_usuario) {
    global $conexao; // Usar a conexão global

    // Obter o preço do produto e a quantidade em estoque a partir do banco de dados
    $sql_produto = "SELECT Preco, Quantidade, Nome_Produto FROM produto WHERE ID_Produto = ?";
    $stmt = $conexao->prepare($sql_produto);
    $stmt->bind_param("i", $id_produto);
    $stmt->execute();
    $result = $stmt->get_result();
    $produto = $result->fetch_assoc();
    $preco_unitario = $produto['Preco'];
    $quantidade_estoque = $produto['Quantidade'];
    $nome_produto = $produto['Nome_Produto'];

    // Obter o nome do cliente
    $sql_cliente = "SELECT Nome_Cliente FROM clientes WHERE ID_Cliente = ?";
    $stmt_cliente = $conexao->prepare($sql_cliente);
    $stmt_cliente->bind_param("i", $id_cliente);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();
    $cliente = $result_cliente->fetch_assoc();
    $nome_cliente = $cliente['Nome_Cliente']; // Nome do cliente

    // Obter o nome do usuário (vendedor)
    $sql_usuario = "SELECT Nome FROM usuarios WHERE ID_Usuario = ?";
    $stmt_usuario = $conexao->prepare($sql_usuario);
    $stmt_usuario->bind_param("i", $id_usuario);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    $usuario = $result_usuario->fetch_assoc();
    $nome_usuario = $usuario['Nome']; // Nome do usuário (vendedor)

    // Verificar se há quantidade suficiente em estoque
    if ($quantidade_estoque < $quantidade) {
        echo "Estoque insuficiente!";
        exit; // Parar o processo se o estoque for insuficiente
    }

    // Calcular o total da venda (sem IVA)
    $total_venda = $preco_unitario * $quantidade;

    // Calcular o IVA de 17% (subtração do IVA do preço total)
    $iva = $total_venda * 0.15;

    // Subtrair o IVA do total da venda
    $total_venda_com_iva = $total_venda + $iva;

    // Registrar a venda
    $sql_venda = "INSERT INTO venda (Data_Venda, Total_Venda, IVA, TotalComIva, ID_Cliente, ID_Usuario, Quant_Venda) 
                  VALUES (NOW(), ?, ?, ?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql_venda);
    $stmt->bind_param("dddiis", $total_venda, $iva, $total_venda_com_iva, $id_cliente, $id_usuario, $quantidade);
    $stmt->execute();
    $id_venda = $stmt->insert_id; // ID da venda inserida

    // Registrar os itens da venda
    $sql_itens = "INSERT INTO itens_venda (ID_Venda, ID_Produto, Quantidade, Preco_Total) 
                  VALUES (?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql_itens);
    $stmt->bind_param("iiii", $id_venda, $id_produto, $quantidade, $total_venda_com_iva);
    $stmt->execute();

    // Atualizar a quantidade de produto no estoque
    $quantidade_atualizada = $quantidade_estoque - $quantidade;
    $sql_estoque = "UPDATE produto SET Quantidade = ? WHERE ID_Produto = ?";
    $stmt = $conexao->prepare($sql_estoque);
    $stmt->bind_param("ii", $quantidade_atualizada, $id_produto);
    $stmt->execute();

    // Registrar o histórico de estoque
    $sql_historico = "INSERT INTO historico_estoque (ID_Produto, Data, Quantidade, Tipo_Transacao, Atual_quantidade) 
                      VALUES (?, NOW(), ?, 'Saída', ?)";
    $stmt = $conexao->prepare($sql_historico);
    $stmt->bind_param("iii", $id_produto, $quantidade, $quantidade_atualizada);
    $stmt->execute();

    // Gerar o recibo em PDF
    gerarRecibo($id_venda, $nome_cliente, $nome_produto, $quantidade, $preco_unitario, $iva, $total_venda_com_iva, $nome_usuario);
    
    // Redirecionar para a visualização do recibo
    echo "<h3>Venda registrada com sucesso!</h3>";
    echo "<p><strong>Relatório de Venda:</strong></p>";
    echo "<a href='Recibos/recibo_venda_" . $id_venda . ".pdf' target='_blank'>Clique aqui para visualizar o recibo</a>";
}

// Função para gerar o recibo em PDF
function gerarRecibo($id_venda, $nome_cliente, $nome_produto, $quantidade, $preco_unitario, $iva, $total_venda_com_iva, $nome_usuario) {
    // Criar o documento PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', 'B', 12);

    // Cabeçalho para o cliente
    $pdf->Cell(0, 10, utf8_decode('Recibo de Venda - 1 Via-Cliente'), 0, 1, 'C');
    // Detalhes da venda - Cliente
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(50, 10, 'ID da Venda:');
    $pdf->Cell(50, 10, $id_venda, 0, 1);
    $pdf->Cell(50, 10, 'Nome do Cliente:');
    $pdf->Cell(50, 10, utf8_decode($nome_cliente), 0, 1);
    $pdf->Cell(50, 10, 'Produto:');
    $pdf->Cell(50, 10, utf8_decode($nome_produto), 0, 1);
    $pdf->Cell(50, 10, 'Quantidade:');
    $pdf->Cell(50, 10, $quantidade, 0, 1);
    $pdf->Cell(50, 10, utf8_decode('Preço Unitário:'));
    $pdf->Cell(50, 10, "MZN " . number_format($preco_unitario, 2, ',', '.'), 0, 1);
    $pdf->Cell(50, 10, "Total Sem Iva:" .number_format($preco_unitario * $quantidade, 2,',','.'),0,1);
    $pdf->Cell(50, 10, 'IVA (17%):');
    $pdf->Cell(50, 10, "MZN " . number_format($iva, 2, ',', '.'), 0, 1);
    $pdf->Cell(50, 10, 'Total (com IVA):');
    $pdf->Cell(50, 10, "MZN " . number_format($total_venda_com_iva, 2, ',', '.'), 0, 1);
    $pdf->Cell(50, 10, 'Processado por:');
    $pdf->Cell(50, 10, utf8_decode($nome_usuario), 0, 1);
  // Obter a data e hora atual do sistema (formato: dd/mm/yyyy HH:mm:ss)
$data_hora_venda = date('d/m/Y H:i:s'); // Pode ser ajustado conforme necessário
$pdf->Cell(50, 10, 'Data e Hora da Venda:');
$pdf->Cell(50, 10, utf8_decode($data_hora_venda), 0, 1);
$pdf->Cell(50,10,utf8_decode('Endereço: Matola,Matola_Rio'));
$pdf->Ln(10);
$pdf->Cell(50, 10, utf8_decode('Obrigado pela preferência! Volte sempre!'), 0, 1);
 
    // Detalhes da venda - Vendedor
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(0, 10, utf8_decode('Recibo de Venda - 2 Via - Usuario'), 0, 1, 'C');
    $pdf->SetFont('Helvetica', '', 12); // Corpo não em negrito
    $pdf->Cell(50, 10, 'ID da Venda:');
    $pdf->Cell(50, 10, $id_venda, 0, 1);
    $pdf->Cell(50, 10, 'Nome do Cliente:');
    $pdf->Cell(50, 10, utf8_decode($nome_cliente), 0, 1);
    $pdf->Cell(50, 10, 'Produto:');
    $pdf->Cell(50, 10, utf8_decode($nome_produto), 0, 1);
    $pdf->Cell(50, 10, 'Quantidade:');
    $pdf->Cell(50, 10, $quantidade, 0, 1);
    $pdf->Cell(50, 10, utf8_decode('Preço Unitário:'));
    $pdf->Cell(50, 10, "MZN " . number_format($preco_unitario, 2, ',', '.'), 0, 1);
    $pdf->Cell(50, 10, utf8_decode("Total Sem Iva:") .number_format($preco_unitario * $quantidade, 2,',','.'),0,1);
    $pdf->Cell(50, 10, 'IVA (17%):');
    $pdf->Cell(50, 10, "MZN " . number_format($iva, 2, ',', '.'), 0, 1);
    $pdf->Cell(50, 10, 'Total (com IVA):');
    $pdf->Cell(50, 10, "MZN " . number_format($total_venda_com_iva, 2, ',', '.'), 0, 1);
    // Exibindo o nome do processador e agradecimento (Vendedor)
    $pdf->Cell(50, 10, 'Processado por:');
    $pdf->Cell(50, 10, utf8_decode($nome_usuario), 0, 1);
   // Obter a data do sistema no formato desejado (Ex: dd/mm/yyyy)
// Obter a data e hora atual do sistema (formato: dd/mm/yyyy HH:mm:ss)
$data_hora_venda = date('d/m/Y H:i:s'); // Pode ser ajustado conforme necessário
$pdf->Cell(50, 10, 'Data e Hora da Venda:');
$pdf->Cell(50, 10, utf8_decode($data_hora_venda), 0, 1);
$pdf->Cell(50,10,utf8_decode('Endereço: Matola,Matola_Rio'));
$pdf->Ln(10);
    $pdf->Cell(50, 10, utf8_decode('Obrigado pela preferência! Volte sempre!'), 0, 1);

    // Gerar o arquivo PDF na pasta "Recibos"
    $pdf->Output('F', 'Recibos/recibo_venda_' . $id_venda . '.pdf');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coletar os dados do formulário
    $id_cliente = $_POST['id_cliente'];
    $id_produto = $_POST['id_produto'];
    $quantidade = $_POST['quantidade'];
    $id_usuario = $_POST['id_usuario'];

    // Chamar a função para processar a venda
    processarVenda($id_cliente, $id_produto, $quantidade, $id_usuario);
}
?>