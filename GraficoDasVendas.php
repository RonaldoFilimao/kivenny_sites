<?php
include_once('config.php');

// Função para gerar o gráfico de vendas
function gerarGraficoVendas($conexao, $periodo) {     
    if ($periodo == 'semana') {         
        $sql = "SELECT YEAR(v.Data_Venda) AS Ano, WEEK(v.Data_Venda) AS Semana, SUM(v.Total_Venda) AS Total_Venda FROM venda v GROUP BY Ano, Semana ORDER BY Ano ASC, Semana ASC";     
    } elseif ($periodo == 'mes') {         
        $sql = "SELECT DATE_FORMAT(v.Data_Venda, '%Y-%m') AS Periodo, SUM(v.Total_Venda) AS Total_Venda FROM venda v GROUP BY Periodo ORDER BY Periodo ASC";     
    } elseif ($periodo == 'ano') {         
        $sql = "SELECT YEAR(v.Data_Venda) AS Ano, SUM(v.Total_Venda) AS Total_Venda FROM venda v GROUP BY Ano ORDER BY Ano ASC";     
    } else {         
        $sql = "SELECT DATE(v.Data_Venda) AS Periodo, SUM(v.Total_Venda) AS Total_Venda FROM venda v GROUP BY Periodo ORDER BY Periodo ASC";     
    }

    $result = $conexao->query($sql);      
    if ($result->num_rows > 0) {         
        $periodos = [];         
        $vendas = [];         
        $cores = [];         
        $situacoes = [];  // Para armazenar a situação (Crescimento significativo, moderado, ou Queda)
        $lastValue = null;          
        while ($row = $result->fetch_assoc()) {             
            // Verificar se a chave 'Semana' existe no resultado da consulta
            if (isset($row['Semana'])) {
                $periodos[] = $row['Ano'] . ' - Semana ' . $row['Semana'];             
            } else {
                // Se não existe a chave 'Semana', usamos a chave 'Periodo' (para mês ou ano)
                $periodos[] = isset($row['Periodo']) ? $row['Periodo'] : $row['Ano'];             
            }
        
            $vendas[] = (float)$row['Total_Venda'];             
        
            if ($lastValue !== null) {                 
                $crescimento = $vendas[count($vendas) - 1] - $lastValue;                 
                if ($crescimento > 1000000) {                     
                    $cores[] = 'rgba(46, 204, 113, 0.9)';  // Crescimento significativo
                    $situacoes[] = 'Crescimento significativo';                 
                } elseif ($crescimento > 100000) {                     
                    $cores[] = 'rgba(241, 196, 15, 0.7)';  // Crescimento moderado
                    $situacoes[] = 'Crescimento moderado';                 
                } else {                     
                    $cores[] = 'rgba(231, 76, 60, 0.7)';  // Queda nas vendas
                    $situacoes[] = 'Queda nas vendas';                 
                }             
            } else {                 
                $cores[] = 'rgba(52, 152, 219, 0.7)';  // Cor de referência
                $situacoes[] = 'Primeiro período (referência inicial)';             
            }             
            $lastValue = $vendas[count($vendas) - 1];         
        }
        
        return [             
            'periodos' => json_encode($periodos),             
            'vendas' => json_encode($vendas),             
            'cores' => json_encode($cores),
            'situacoes' => json_encode($situacoes) // Passando as situações para o JS
        ];     
    } else {         
        return false;     
    } 
}

$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'semana'; 
$graficoData = gerarGraficoVendas($conexao, $periodo);
if ($graficoData === false) {
    echo "<script>alert('Sem dados para o período selecionado!');</script>";
    $graficoData = [
        'periodos' => json_encode([]),
        'vendas' => json_encode([]),
        'cores' => json_encode([]),
        'situacoes' => json_encode([])
    ];
}
?> 

<!DOCTYPE html> 
<html lang="pt-BR"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">     
    <title>Relatório de Vendas - Crescimento ou Queda</title>     
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>     
    <style>         
        body {             
            font-family: Arial;             
            background-color: #f4f7fc;             
            color: #333;             
            display: flex;             
            justify-content: center;             
            align-items: center;             
            flex-direction: column;             
            height: 100vh;             
            margin: 0;         
        }          
        h1 {             
            color: deepskyblue;             
            font-size: 2.5em;             
            margin-bottom: 5px;         
        }          
        .container {             
            width: 100%;             
            max-width: 900px;             
            background-color: white;             
            padding: 10px;             
            border-radius: 10px;             
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);             
            text-align: center;             
            display: flex;             
            justify-content: space-between;             
        }          
        .select-period {             
            margin: 5px 0;             
            font-size: 1.2em;   
            background-color:deepskyblue;       
        }         
        .color-legend {             
            margin-top: 10px;             
            text-align: left;             
            max-width: 250px;         
        }         
        .color-legend span {             
            display: inline-block;             
            width: 20px;             
            height: 20px;             
            margin-right: 10px;             
            border-radius: 30px;         
        }     
    </style> 
</head> 
<body>  

<div class="container">         
    <div style="width: 65%;">         
        <h1>GRÁFICO DE CONTROLO DE CRESCIMETO DE NEGÓCIO</h1>          

        <!-- Seleção do período -->         
        <div class="select-period">             
            <label for="periodo">Escolha um período: </label>             
            <select id="periodo" onchange="mudarPeriodo()">   
                <option value="dia" <?= $periodo === 'dia' ? 'selected' : ''; ?>>Dias</option>               
                <option value="semana" <?= $periodo === 'semana' ? 'selected' : ''; ?>>Semanas</option>                 
                <option value="mes" <?= $periodo === 'mes' ? 'selected' : ''; ?>>Meses</option>                 
                <option value="ano" <?= $periodo === 'ano' ? 'selected' : ''; ?>>Anos</option>  
            </select>         
        </div>          

        <!-- Gráfico -->         
        <div class="chart-container">             
            <canvas id="graficoVendas"></canvas>         
        </div>  
    </div>   

    <div class="color-legend">
        <h3>Legenda de Cores:</h3>
        <p><span style="background-color: rgba(46, 204, 113, 0.9);"></span> Crescimento significativo</p>
        <p><span style="background-color: rgba(241, 196, 15, 0.7);"></span> Crescimento moderado</p>
        <p><span style="background-color: rgba(231, 76, 60, 0.7);"></span> Queda nas vendas (Fase)</p>
        <p><span style="background-color: rgba(52, 152, 219, 0.7);"></span> Primeiro período (referência inicial)</p>
    </div>
</div>      

<script>         
    function mudarPeriodo() {             
        var periodo = document.getElementById('periodo').value;             
        window.location.href = '?periodo=' + periodo;         
    }          

    // Dados passados do PHP para o JavaScript         
    const periodos = <?php echo $graficoData['periodos']; ?>;         
    const vendas = <?php echo $graficoData['vendas']; ?>;         
    const cores = <?php echo $graficoData['cores']; ?>;         
    const situacoes = <?php echo $graficoData['situacoes']; ?>;  

    // Criar o gráfico         
    const ctx = document.getElementById('graficoVendas').getContext('2d');         
    const graficoVendas = new Chart(ctx, {             
        type: 'bar',             
        data: {                 
            labels: periodos,                 
            datasets: [{                     
                label: 'Total de Vendas (MZN)',                     
                data: vendas,                     
                backgroundColor: cores,                     
                borderColor: '#333',                     
                borderWidth: 1                 
            }]             
        },             
        options: {                 
            responsive: true,                 
            plugins: {                     
                tooltip: {                         
                    enabled: true,                         
                    callbacks: {                             
                        title: function(tooltipItem) {                                 
                            return 'Período: ' + tooltipItem[0].label;                             
                        },                             
                        label: function(tooltipItem) {                                 
                            return 'Vendas: MZN ' + tooltipItem.raw.toLocaleString() + ' (' + situacoes[tooltipItem.dataIndex] + ')';                             
                        }                         
                    }                     
                }                 
            },                 
            animation: {                     
                duration: 1000,                 
                easing: 'easeOutBounce'                 
            },                 
            scales: {                     
                y: {                         
                    beginAtZero: true,                         
                    ticks: {                             
                        callback: function(value) {                                 
                            return 'MZN ' + value.toLocaleString();                             
                        }                         
                    }                     
                }                 
            }             
        }         
    });     
</script>  
</body>  
</html>
