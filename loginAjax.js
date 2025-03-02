$(document).ready(function () {
    $('#loginForm').submit(function (event) {
        event.preventDefault(); // Evita recarregar a página

        $.ajax({
            url: '../PHP/logar.php',
            method: 'POST',
            data: {
                email: $('#email').val(),
                senha: $('#senha').val()
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#responseMessage').html(`<p style="color: green;">${response.message}</p>`);
                    // Redireciona com base no papel do usuário
                    window.location.href = response.redirect;
                } else {
                    $('#responseMessage').html(`<p style="color: red;">${response.message}</p>`);
                }
            },
            error: function () {
                $('#responseMessage').html('<p style="color: red;">Erro na comunicação com o servidor.</p>');
            }
        });
    });
});
    // Verificação contínua do estado da sessão
    $(document).ready(function () {
        setInterval(function () {
            $.ajax({
                url: 'verificarSessao.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'expired') {
                        alert('Sua sessão expirou. Você será redirecionado para o login.');
                        window.location.href = '../HTML/login.';
                    }
                },
                error: function () {
                    console.error('Erro ao verificar a sessão.');
                }
            });
        }, 60000); // Verifica a cada 60 segundos
    });
    