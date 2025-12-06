

<link rel="stylesheet" href="css/styledark.css">
<footer id="user-main-footer" class="user-footer user-page__footer">
    <div class="user-footer__content-wrapper">
        <p id="user-info-system" class="user-footer__block user-footer__block--info">
                <img id="jarvis-logo-footer" class="jarvis-favcon" src="img/favicon_claro.png" alt="">
                <span class="user-footer__text user-footer__text--heading"> J.A.R.V.I.S - Sistema To-Do List</span><br>
                <span class="user-footer__text user-footer__text--email">E-mail: Chrystyan.ribeiro@outlook.com</span><br>
                <span class="user-footer__text user-footer__text--cell">Cell: +55 (21) 98351-4313</span>
        </p>
        <p id="user-info-copyright" class="user-footer__block user-footer__block--copyright">
            <span class="user-footer__text user-footer__text--rights">
                &copy; <?php echo date("Y"); ?> Todos os direitos reservados.
            </span><br>
            <a href="https://www.instagram.com/" class="user-footer__link user-footer__link--instagram">Instagram</a><br>
            <a href="https://www.linkedin.com/" class="user-footer__link user-footer__link--linkedin">Linkedin</a><br>
        </p>
        <p id="user-info-secondary" class="user-footer__block user-footer__block--secondary">
            <a href="Chrystyan.ribeiro@outlook.com" class="user-footer__link user-footer__link--work">Trabalhe Conosco</a><br>
            <br>
            <a href="admin/index.php" class="user-footer__link user-footer__link--admin">Painel de Controle</a><br>
        </p>
    </div>
</footer>
<script>
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
</script>