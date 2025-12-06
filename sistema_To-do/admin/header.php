<?php
// Inclui o arquivo de conexão com o banco de dados.
include('../conexao.php');

// Comentário: Bloco PHP de Verificação de Login
// Verifica se a variável de sessão 'id_usuario' está definida.
if (!isset($_SESSION['id_usuario'])) {
    // Se não estiver logado, redireciona para a página de login e encerra.
    header("Location: ../login.php");
    exit;
}

// Comentário: Bloco PHP de Verificação de Permissão de Admin
// Prepara a consulta para buscar o nome e o tipo de usuário (admin ou comum).
$stmt = $conexao->prepare("SELECT nome, tipo FROM usuarios WHERE id = ?");
// Associa o ID do usuário da sessão.
$stmt->bind_param("i", $_SESSION['id_usuario']);
// Executa a consulta.
$stmt->execute();
// Obtém o resultado e busca a linha como array associativo.
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Verifica se o tipo de usuário NÃO é 'admin'.
if ($usuario['tipo'] !== 'admin') {
    // Se não for admin, redireciona para a página inicial e encerra.
    header("Location: ../index.php");
    exit;
}

// Armazena o nome do usuário logado para exibição no cabeçalho.
$nome_usuario = $usuario['nome'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<header id="admin-header-principal">
    <div id="admin-header-logo">🧠 Admin - <?php echo htmlspecialchars($nome_usuario); ?></div>
    <span id="admin-menu-icon" onclick="toggleMenu()">☰</span>
    <nav id="admin-nav-principal">
        <ul id="admin-nav-lista">
            <li><a href="index.php" class="admin-nav-link">Dashboard</a></li>
            <li><a href="usuarios.php" class="admin-nav-link">Usuários</a></li>
            <li><a href="tarefas.php" class="admin-nav-link">Tarefas</a></li>
            <li><a href="relatorios.php" class="admin-nav-link">Relatórios</a></li>
            <li><a href="../logout.php" class="admin-nav-link" id="admin-link-logout">Logout</a></li>
        </ul>
    </nav>
</header>

<script>
// Comentário: Função JavaScript para alternar a exibição do menu mobile
function toggleMenu() {
    // Adiciona ou remove a classe 'show' na lista de navegação.
    document.getElementById('admin-nav-lista').classList.toggle('show');
}
</script>

<div id="admin-conteudo-padding"> 
