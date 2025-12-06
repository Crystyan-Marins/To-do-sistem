<?php
// Inicia a sessão para acessar as variáveis de sessão.
session_start();
// Inclui o arquivo de conexão com o banco de dados.
include('../conexao.php');

// Comentário: Bloco PHP de Verificação de Login e Admin
// Verifica se o usuário está logado. Se não estiver, redireciona.
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

// Prepara a consulta para buscar o tipo de usuário.
$stmt = $conexao->prepare("SELECT tipo FROM usuarios WHERE id = ?");
// Associa o ID do usuário da sessão.
$stmt->bind_param("i", $_SESSION['id_usuario']);
// Executa a consulta.
$stmt->execute();
// Obtém o resultado.
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Verifica se o tipo de usuário NÃO é 'admin'. Se não for, redireciona.
if ($usuario['tipo'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Comentário: Bloco PHP para Lógica de Exclusão de Usuário
// Verifica se a ação de 'excluir' foi solicitada via GET.
if (isset($_GET['excluir'])) {
    // Armazena o ID do usuário a ser excluído.
    $id_excluir = $_GET['excluir'];
    // Verifica se o ID a ser excluído é diferente do ID do usuário logado (Admin).
    if ($id_excluir != $_SESSION['id_usuario']) {
        // Prepara a consulta para DELETAR o usuário.
        $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id=?");
        $stmt->bind_param("i", $id_excluir);
        $stmt->execute();
        // Redireciona para a própria página de usuários.
        header("Location: usuarios.php");
        exit;
    } else {
        // Se tentar excluir a si mesmo, exibe um alerta JavaScript.
        echo "<script>alert('Você não pode excluir seu próprio usuário!');</script>";
    }
}

// Comentário: Consulta SQL para Listar Todos os Usuários
$usuarios = $conexao->query("SELECT id, nome, nome_social, email, tipo FROM usuarios ORDER BY id ASC");
?>

<?php 
// Inclui o cabeçalho (já refatorado)
include 'header.php'; 
?>
<link rel="stylesheet" href="css/admin.css">
<div class="container-admin-tarefa" id="admin-listagem-usuarios-container">
    <h2 class="admin-usuarios-titulo">👥 Usuários</h2>
    <a href="cadastro.php" class="btn admin-btn-primary" id="admin-link-novo-usuario">➕ Novo Usuário</a>
    
    <table border="1" cellpadding="10" cellspacing="0" id="admin-tabela-usuarios">
        <tr class="admin-tabela-cabecalho">
            <th class="admin-celula-cabecalho">ID</th>
            <th class="admin-celula-cabecalho">Nome</th>
            <th class="admin-celula-cabecalho">Nome Social</th>
            <th class="admin-celula-cabecalho">Email</th>
            <th class="admin-celula-cabecalho">Tipo</th>
            <th class="admin-celula-cabecalho">Ações</th>
        </tr>
        <?php while($u = $usuarios->fetch_assoc()): ?>
        <tr class="admin-tabela-linha-dados">
            <td class="admin-celula-tarefa"><?php echo $u['id']; ?></td>
            <td class="admin-celula-tarefa"><?php echo htmlspecialchars($u['nome']); ?></td>
            <td class="admin-celula-tarefa"><?php echo htmlspecialchars($u['nome_social']); ?></td>
            <td class="admin-celula-tarefa"><?php echo htmlspecialchars($u['email']); ?></td>
            <td class="admin-celula-tarefa"><?php echo $u['tipo']; ?></td>
            <td class="admin-celula-tarefa admin-celula-tarefa-acoes">
                <a href="editar_usuario.php?id=<?php echo $u['id']; ?>" class="admin-link-editar-usuario"> Editar</a> |
                <a href="?excluir=<?php echo $u['id']; ?>" class="admin-link-excluir-usuario" onclick="return confirm('Tem certeza que deseja excluir este usuário?')"> Excluir</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php 
// Inclui o rodapé (já refatorado)
include 'footer.php'; 
?>