// -----------------------------------------------------------
// FUNÇÃO PARA ABRIR / FECHAR MENU MOBILE
// -----------------------------------------------------------
function userToggleMenu() {

    // Seleciona o elemento UL do menu mobile
    const menu = document.getElementById('user-menu-mobile');

    // Alterna a classe que mostra/esconde o menu
    menu.classList.toggle('user-navigation__list--show');
}



// -----------------------------------------------------------
// SISTEMA DE DARK MODE + TROCA DE IMAGENS AUTOMÁTICA
// -----------------------------------------------------------

// Captura o botão responsável por alternar o tema
const toggleBtn = document.getElementById('user-btn-toggle-theme');

// Seleciona TODAS as imagens que devem trocar no dark mode
// (inclui HEADER e FOOTER)
const themeImages = document.querySelectorAll('.jarvis-favcon');


// -----------------------------------------------------------
// FUNÇÃO QUE ALTERA AS IMAGENS DO TEMA ATUAL
// -----------------------------------------------------------
function updateImages(theme) {

    // Para cada imagem com a classe .jarvis-favcon
    themeImages.forEach(img => {

        // Se o tema é dark → troca para imagem escura
        // Caso contrário → usa a imagem clara
        img.src = theme === 'dark'
            ? "img/favicon_escuro.png"
            : "img/favicon_claro.png";
    });
}



// -----------------------------------------------------------
// APLICA O TEMA SALVO NO LOCALSTORAGE AO CARREGAR A PÁGINA
// -----------------------------------------------------------

// Lê o tema salvo (se não existir, usa "light" como padrão)
let savedTheme = localStorage.getItem('theme') || 'light';

// Aplica o tema no body
document.body.setAttribute('data-theme', savedTheme);

// Atualiza as imagens de acordo com o tema salvo
updateImages(savedTheme);



// -----------------------------------------------------------
// BOTÃO QUE ALTERNA O TEMA AO SER CLICADO
// -----------------------------------------------------------
toggleBtn.addEventListener('click', () => {

    // Lê o tema atual
    let currentTheme = document.body.getAttribute('data-theme');

    // Define o próximo tema (se está dark → vira light, e vice-versa)
    let nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

    // Aplica o novo tema
    document.body.setAttribute('data-theme', nextTheme);

    // Salva o novo tema no navegador
    localStorage.setItem('theme', nextTheme);

    // Atualiza as imagens conforme o novo tema
    updateImages(nextTheme);
});


// -----------------------------------------------------------
// FIM DO SCRIPT
// -----------------------------------------------------------