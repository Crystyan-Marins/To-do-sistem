<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit; // Encerra o script para garantir o redirecionamento.
}
?>


<!DOCTYPE html>
<html lang="pt-BR" id="user-document-html"> 
    <meta charset="UTF-8">
    <title id="user-page-title-welcome">Bem-vindo</title>
    <link id="favicon" rel="icon" type="image/x-icon" href="img/favicon_claro.png">
    <link rel="stylesheet" href="css/style.css">
    <?php
// Inclui o cabeçalho padrão da página (que já revisamos).
include 'header.php';
?>

<body id="user-index-page"> <div id="user-main-container-index" class="user-container user-home__wrapper">
        <h2 class="user-home__heading-welcome">
            Bem-vindo, 
            <?php echo htmlspecialchars($_SESSION['usuario']); ?>!
        </h2>

        <div class="user-card user-card--todo-access" id="user-card-todo-access">
            <h3 class="user-card__heading">📝 Sistema To-Do List</h3>
            <p class="user-card__description">Gerencie suas tarefas de forma prática e organizada.</p>
            <a href="todo.php" class="user-button user-button--primary user-card__btn--access" id="user-link-access-todo">Acessar</a>
        </div>

        <a href="logout.php" class="user-link-user-link--logout-user-home__link--logout" id="user-link-logout-home">Logout</a>
    </div>

<?php
// Inclui o rodapé padrão da página (que já revisamos).
include 'footer.php';
?>
</body>
</html>