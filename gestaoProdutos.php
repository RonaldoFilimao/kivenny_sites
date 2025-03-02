<?php
session_start();
if (isset($_SESSION['msg'])) {
    echo "<script>alert('" . $_SESSION['msg'] . "');</script>";
    unset($_SESSION['msg']);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Produtos</title>
    <style>
        /* Resetando estilos padrões */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            padding: 40px;
            text-align: center;
        }

        h1 {
            color: #2C3E50;
            font-size: 2.5rem;
            margin-bottom: 30px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Estilo geral dos botões */
        button {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin: 10px 5px;
        }

        /* Efeito hover nos botões */
        button:hover {
            background-color: #2980b9;
            transform: translateY(-3px);
        }

        button:active {
            transform: translateY(1px);
        }

        /* Estilos para os formulários */
        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
            text-align: left;
            width: 100%;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        label {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #3498db;
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 150px;
        }

        .div-section {
            display: none;
        }

        /* Estilos para o select de categoria */
        #categoriaSelect {
            background-color: #f9f9f9;
        }

        /* Container de navegação de botões */
        .button-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }

        .button-container button {
            margin: 5px 10px;
        }

        /* Efeitos de transição nas seções de formulário */
        .div-section {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .div-section.active {
            display: block;
            opacity: 1;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }

            .container {
                padding: 15px;
            }

            form {
                padding: 20px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <h1>Gestão de Produtos</h1>
    
    <div class="button-container">
        <button onclick="exibirDiv('categoria')">Adicionar Categoria</button>
        <button onclick="exibirDiv('produto')">Adicionar Produto</button>
        <button onclick="window.location.href='../PHP/visualizarProdutos.php'">Visualizar Produtos</button>
        <button onclick="window.location.href='../PHP/adminSistema.php'">Voltar</button>
    </div>

    <div id="categoria" class="div-section">
        <h2>Adicionar Categoria</h2>
        <form action="../PHP/inserirCategoria.php" method="POST">
            <label>Nome da Categoria:</label><br>
            <input type="text" name="nome_categoria" required><br>
            <label>Descrição:</label><br>
            <textarea name="descricao"></textarea><br><br>
            <button type="submit">Adicionar Categoria</button>
        </form>
    </div>

    <div id="produto" class="div-section">
        <h2>Adicionar Produto</h2>
        <form action="../PHP/inserirProduto.php" method="POST" id="formProduto">
            <label>Categoria:</label><br>
            <select id="categoriaSelect" name="id_categoria" required>
                <option value="">Selecione uma categoria</option>
            </select>
            
            <script>
                // Faz a requisição AJAX para obter as categorias
                fetch('../PHP/SelecionarCategoria.php')
                    .then(response => response.json())
                    .then(data => {
                        const selectElement = document.getElementById('categoriaSelect');
                        
                        data.forEach(categoria => {
                            const option = document.createElement('option');
                            option.value = categoria.ID_Categoria;
                            option.textContent = categoria.Nome_Categoria;
                            selectElement.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Erro ao carregar categorias:', error);
                    });
            </script>
            <br>
            <label>Nome do Produto:</label><br>
            <input type="text" name="nome_produto" id="nomeProduto" required><br>
            <label>Quantidade:</label><br>
            <input type="number" name="quantidade" id="quantidade" required><br>
            <label>Descrição:</label><br>
            <textarea name="descricao" id="descricao"></textarea><br>
            <label>Preço:</label><br>
            <input type="number" name="preco" id="preco" step="0.01" required><br>
            <label>Data de Entrada:</label><br>
            <input type="date" name="data_entrada" id="dataEntrada" required><br><br>
            <button type="submit">Adicionar Produto</button>
        </form>
    </div>

    <script>
        function exibirDiv(divId) {
            document.querySelectorAll('.div-section').forEach(div => {
                div.classList.remove('active');
            });
            document.getElementById(divId).classList.add('active');
        }

        // Limpar os campos do formulário após sucesso
        if (window.location.search.includes("sucesso=true")) {
            document.getElementById('formProduto').reset();
            alert("Produto adicionado com sucesso!");
        }
    </script>
</body>
</html>
