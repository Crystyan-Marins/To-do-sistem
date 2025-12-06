<?php
// Verifica o status da sessão PHP e inicia se ainda não tiver sido iniciada.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui o arquivo de conexão com o banco de dados.
include('conexao.php');

// Obtém o ID do usuário da sessão, ou null se não estiver logado.
$id_usuario = $_SESSION['id_usuario'] ?? null;
// Define um nome de fallback inicial.
$nome_usuario = 'Usuário';

// Se o usuário estiver logado, busca seus dados para a saudação personalizada.
if ($id_usuario) {
    // Prepara a instrução SQL para buscar dados do usuário.
    $stmt = $conexao->prepare("SELECT nome, nome_social, nascimento, genero, estado_civil FROM usuarios WHERE id = ?");
    // Associa o ID do usuário ao parâmetro da query.
    $stmt->bind_param("i", $id_usuario);
    // Executa a consulta.
    $stmt->execute();
    // Obtém o resultado.
    $resultado = $stmt->get_result();

    // Se o usuário for encontrado:
    if ($usuario = $resultado->fetch_assoc()) {
        // Decide qual nome usar (dando preferência ao nome social).
        $nome_usar = !empty($usuario['nome_social']) ? $usuario['nome_social'] : $usuario['nome'];

        // Coleta e sanitiza os dados para a saudação.
        $nascimento = isset($usuario['nascimento']) ? (int)$usuario['nascimento'] : 0;
        $genero = isset($usuario['genero']) ? strtolower($usuario['genero']) : '';
        $estado_civil = isset($usuario['estado_civil']) ? strtolower($usuario['estado_civil']) : '';

        // --- Lógica de Personalização da Saudação ---

// --- Lógica de Personalização da Saudação (Simplificada) ---
        
        // Define a saudação base
        $saudacao_base = "Ao seu dispor,";
        
        if ($genero === 'masculino') {
            // Se for masculino, usa "Senhor"
            $nome_usuario = $saudacao_base . " Senhor " . $nome_usar;
        } elseif ($genero === 'feminino') {
            // Se for feminino, usa "Senhora"
            $nome_usuario = $saudacao_base . " Senhora " . $nome_usar;
        } else {
            // Saudação padrão para outros gêneros ou desconhecido.
            $nome_usuario = $saudacao_base . " " . $nome_usar;
        }
    }
}

?>

<header id="user-main-header" class="user-header">
    <link rel="stylesheet" href="css/style.css">
    <div class="user-header__logo-container">
    <img id="jarvis-logo" class="jarvis-favcon" src="img/favicon_claro.png" alt="">
        <span class="user-header__logo-text"> JARVIS - <?php echo htmlspecialchars($nome_usuario); ?></span>
    </div>

    <span class="user-header__menu-icon" onclick="userToggleMenu()" id="user-menu-hamburguer">☰</span>

    <nav class="user-header__navigation">
        <ul id="user-menu-mobile" class="user-navigation__list">
            <li class="user-navigation__item"><a href="perfil_user.php" class="user-navigation__link">Meu Perfil</a></li>
            <li class="user-navigation__item"><a href="index.php" class="user-navigation__link">Home</a></li>
            <li class="user-navigation__item"><a href="todo.php" class="user-navigation__link">Adicionar</a></li>
            <li class="user-navigation__item"><a href="consultar.php" class="user-navigation__link">Consultar</a></li>
            <li class="user-navigation__item"><a href="logout.php" class="user-navigation__link user-navigation__link--logout" id="user-link-logout">Logout</a></li>
            <li class="user-navigation__dark"><button id="user-btn-toggle-theme" class="user-button user-button--theme-toggle user-navigation__btn--theme">🌓 Dark Mode</button></li>
        </ul>
    </nav>
</header>

