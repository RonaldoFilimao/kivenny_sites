// Função para exibir os dados armazenados nos cookies
function exibirCookies() {
    const cookies = document.cookie; // Obtém todos os cookies do navegador
    const cookieArray = cookies.split(';'); // Divide os cookies em um array

    let token = '';
    let id_usuario = '';
    let id_role = '';

    // Loop para verificar os cookies específicos
    cookieArray.forEach(cookie => {
        const [name, value] = cookie.trim().split('=');

        if (name === 'token') {
            token = value;
        }
        if (name === 'id_usuario') {
            id_usuario = value;
        }
        if (name === 'id_role') {
            id_role = value;
        }
    });

    // Exibe os valores no HTML
    document.getElementById('token').textContent = token || 'Não encontrado';
    document.getElementById('id_usuario').textContent = id_usuario || 'Não encontrado';
    document.getElementById('id_role').textContent = id_role || 'Não encontrado';

    // Se não houver dados nos cookies
    if (!token || !id_usuario || !id_role) {
        alert('Nenhum dado encontrado nos cookies!');
    }
}
