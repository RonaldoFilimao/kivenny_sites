// Carregar e pesquisar clientes usando jQuery e AJAX
$(document).ready(function () {
    loadClients('');

    $('#search').on('keyup', function () {
        var searchTerm = $(this).val();
        loadClients(searchTerm);
    });
});

// Pesquisa manual via clique do botão
function manualSearch() {
    var searchTerm = $('#search').val();
    loadClients(searchTerm);
}

// Função para carregar clientes dinamicamente
function loadClients(searchTerm) {
    $.ajax({
        url: '../PHP/visualizar.php',
        type: 'GET',
        data: { search: searchTerm },
        success: function (response) {
            $('#clientTable tbody').html(response);
        }
    });
}