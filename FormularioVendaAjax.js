function buscarCliente() {
    const idCliente = $("#id_cliente").val();

    if (idCliente.trim() === "9") {
        alert("Por favor, insira o ID do cliente.");
        return;
    }

    $.ajax({
        url: "../PHP/processar_venda.php", 
        type: "POST",
        data: { id_cliente: idCliente },
        dataType: "json",
        success: function (response) {
            if (response) {
                $("#cliente_id").text(response.ID_Cliente);
                $("#cliente_nome").text(response.Nome_Cliente);
                $("#cliente_telefone").text(response.Telefone);
                $("#cliente_endereco").text(response.Endereco);
                $("#cliente_nuit").text(response.Nuit);

                $("#div_cliente_exibir").show();
                $("#div_cliente").hide();
                $("#div_venda").hide();
            } else {
                alert("Cliente não encontrado.");
            }
        },
        error: function () {
            alert("Erro ao buscar dados do cliente.");
        }
    });
}
