$(document).ready(function () {
    $('#formCadastro').on('submit', function (event) {
        event.preventDefault(); // Impede o envio tradicional do formulário

        // Limpa todas as mensagens de erro anteriores
        $('.message').text('').removeClass('error success');

        // Recupera os valores do formulário
        const nome = $('#nome').val().trim();
        const email = $('#email').val().trim();
        const senha = $('#senha').val();
        const confirmarSenha = $('#confirmarSenha').val();
        const telefone = $('#telefone').val().trim();
        const sexo = $('#sexo').val();
        const cidade = $('#cidade').val().trim();
        const pais = $('#pais').val().trim();
        const dataNascimento = $('#dataNascimento').val();
        const nivel = $('#Nivel').val();

        // Expressão regular para validar somente letras (para o nome)
        const nomeRegex = /^[A-Za-zÀ-ÿ\s]+$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const telefoneRegex = /^[0-9]{9,15}$/;
        const senhaRegex = /^[a-z0-9]{6,12}$/;
        
        let hasError = false;

        // Validação do campo Nome
        if (!nome || !nomeRegex.test(nome)) {
            $('#message-nome').addClass('error').text('O campo Nome deve conter apenas letras e espaços.');
            hasError = true;
        }

        // Validação do campo Email
        if (!email || !emailRegex.test(email)) {
            $('#message-email').addClass('error').text('Por favor, insira um email válido.');
            hasError = true;
        }

        // Validação do campo Telefone
        if (!telefone || !telefoneRegex.test(telefone)) {
            $('#message-telefone').addClass('error').text('O telefone deve conter entre 9 a 15 dígitos.');
            hasError = true;
        }

        // Validação da Senha e Confirmar Senha
        if (!senha || !confirmarSenha || senha !== confirmarSenha) {
            $('#message-senha').addClass('error').text('As senhas não coincidem. Por favor, tente novamente.');
            hasError = true;
        } else if (!senhaRegex.test(senha)) {
            $('#message-senha').addClass('error').text('A senha deve ter entre 6 e 12 caracteres, apenas letras minúsculas e números.');
            hasError = true;
        }

        // Validação da Data de Nascimento
        const dataAtual = new Date().toISOString().split('T')[0];
        if (dataNascimento === '' || dataNascimento > dataAtual) {
            $('#message-dataNascimento').addClass('error').text('Por favor, insira uma data de nascimento válida.');
            hasError = true;
        }

        // Validação de Cidade e País
        if (!cidade || !pais) {
            $('#message-cidade').addClass('error').text('Os campos Cidade e País são obrigatórios.');
            hasError = true;
        }

        // Se houver algum erro, não envia o formulário
        if (hasError) {
            return;
        }

        // Serializa os dados do formulário
        var formData = $(this).serialize();

        // Envia os dados via AJAX
        $.ajax({
            url: '../PHP/formularioCadastro.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#message').addClass('success').text(response.message);
                    $('#formCadastro')[0].reset(); // Limpa o formulário após sucesso
                } else {
                    $('#message').addClass('error').text(response.message);
                }
            },
            error: function (jqXHR) {
                $('#message').addClass('error').text('Erro ao processar a solicitação.');
            }
        });
    });
});
