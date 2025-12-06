<?php
// Inicia a sessão se ainda não estiver iniciada.
session_start();
// Inclui o arquivo de conexão com o banco de dados.
include('conexao.php');

// Verifica se a requisição HTTP foi feita usando o método POST (envio do formulário de login).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta o e-mail e a senha enviados via formulário.
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Prepara a instrução SQL para buscar o usuário pelo e-mail.
    $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE email = ?");
    // Associa o e-mail ao parâmetro da query.
    $stmt->bind_param("s", $email);
    // Executa a consulta.
    $stmt->execute();
    // Obtém o resultado da consulta.
    $resultado = $stmt->get_result();

    // Verifica se encontrou alguma linha (usuário com este e-mail existe).
    if ($resultado->num_rows > 0) {
        // Armazena os dados do usuário encontrado.
        $usuario = $resultado->fetch_assoc();

        // Verifica a senha usando a função de verificação de hash.
        if (password_verify($senha, $usuario['senha'])) {
            // Se a senha estiver correta, define variáveis de sessão para manter o usuário logado.
            $_SESSION['usuario'] = $usuario['nome']; // Armazena o nome.
            $_SESSION['id_usuario'] = $usuario['id']; // Armazena o ID.
            // Redireciona para a página principal.
            header("Location: index.php");
            exit; // Encerra o script.
        } else {
            // Exibe um alerta JavaScript se a senha estiver incorreta.
            echo "<script>alert('Senha incorreta!');</script>";
        }
    } else {
        // Exibe um alerta JavaScript se o usuário (e-mail) não for encontrado.
        echo "<script>alert('Usuário não encontrado!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR" id="user-document-html"> <head>
    <meta charset="UTF-8">
    <title id="user-page-title-login">Login</title>
    <link id="favicon" rel="icon" type="image/x-icon" href="img/favicon_claro.png">
    <link rel="stylesheet" href="css/style.css">
    <?php
    // Inclui o cabeçalho padrão da página de usuário.
    include 'header.php';
    ?>
<body data-theme="light" id="user-login-page">
    <div id="user-main-container-login" class="user-container user-login__wrapper">
        <h2 id="user-heading-login" class="user-login__heading">Login</h2>
        <form method="POST" class="user-login__form" id="user-login-form">
            <input class="user-form__input user-form__input--email user-login__input" 
                   type="email" name="email" 
                   placeholder="E-mail" required id="user-input-email-login"><br>
            <input class="user-form__input user-form__input--senha user-login__input" 
                   type="password" name="senha" 
                   placeholder="Senha" required id="user-input-senha-login"><br>
            <button id="user-btn-login-submit" type="submit" 
                    class="user-button user-button--primary user-login__btn--submit">Entrar</button>
            <p class="user-login__register-text">
                Não tem conta? 
                <a href="cadastro.php" class="user-login__register-link" id="user-link-register-page">Cadastre-se</a>
            </p>
            <p class="user-login__register-text">
                Esqueceu a  
                <a href="cadastro.php" class="user-login__register-link" id="user-link-register-page">Senha</a>?
            </p>
        </form>
    </div>
</body>
<?php
// Inclui o rodapé padrão da página (que já revisamos).
include 'footer.php';
?>
</html>