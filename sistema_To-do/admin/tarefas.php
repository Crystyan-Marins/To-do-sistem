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

// Comentário: Bloco PHP para Alteração de Status da Tarefa (Concluir)
// Verifica se a ação de 'concluir' foi solicitada via GET.
if (isset($_GET['concluir'])) {
    // Armazena o ID da tarefa.
    $id_tarefa = $_GET['concluir'];
    // Prepara a consulta para mudar o status para 'concluida'.
    $stmt = $conexao->prepare("UPDATE tarefas SET status='concluida' WHERE id=?");
    $stmt->bind_param("i", $id_tarefa);
    $stmt->execute();
    // Redireciona para a própria página de tarefas.
    header("Location: tarefas.php");
    exit;
}

// Comentário: Bloco PHP para Alteração de Status da Tarefa (Pendente)
// Verifica se a ação de 'pendente' foi solicitada via GET.
if (isset($_GET['pendente'])) {
    // Armazena o ID da tarefa.
    $id_tarefa = $_GET['pendente'];
    // Prepara a consulta para mudar o status para 'pendente'.
    $stmt = $conexao->prepare("UPDATE tarefas SET status='pendente' WHERE id=?");
    $stmt->bind_param("i", $id_tarefa);
    $stmt->execute();
    // Redireciona para a própria página de tarefas.
    header("Location: tarefas.php");
    exit;
}

// Comentário: Bloco PHP para Excluir Tarefa
// Verifica se a ação de 'excluir' foi solicitada via GET.
if (isset($_GET['excluir'])) {
    // Armazena o ID da tarefa.
    $id_tarefa = $_GET['excluir'];
    // Prepara a consulta para DELETAR a tarefa.
    $stmt = $conexao->prepare("DELETE FROM tarefas WHERE id=?");
    $stmt->bind_param("i", $id_tarefa);
    $stmt->execute();
    // Redireciona para a própria página de tarefas.
    header("Location: tarefas.php");
    exit;
}

// Comentário: Consulta SQL para Listar Todas as Tarefas (JOIN com Nome do Usuário)
$tarefas = $conexao->query("
    SELECT t.id, t.titulo, t.descricao, t.data_tarefa, t.hora_atividade, t.status, u.nome 
    FROM tarefas t 
    JOIN usuarios u ON t.id_usuario = u.id
    ORDER BY t.id DESC
");
?>

<?php 
// Inclui o cabeçalho (já refatorado)
include 'header.php'; 
?>
<link rel="stylesheet" href="css/admin.css">
<div id="admin-listagem-tarefas-container" class="container-admin-tarefa">
    <h2 class="admin-tarefas-titulo">📝 Tarefas</h2>
    <a href="nova_tarefa.php" class="btn admin-btn-primary" id="admin-link-nova-tarefa">➕ Nova Tarefa</a>

    <table border="1" cellpadding="10" cellspacing="0" id="admin-tabela-tarefas">
        <tr class="admin-tabela-cabecalho">
            <th class="admin-celula-cabecalho">ID</th>
            <th class="admin-celula-cabecalho">Usuário</th>
            <th class="admin-celula-cabecalho">Título</th>
            <th class="admin-celula-cabecalho">Descrição</th>
            <th class="admin-celula-cabecalho">Data</th>
            <th class="admin-celula-cabecalho">Hora</th>
            <th class="admin-celula-cabecalho">Status</th>
            <th class="admin-celula-cabecalho">Ações</th>
        </tr>
        <?php while($t = $tarefas->fetch_assoc()): ?>
        <tr class="admin-tabela-linha-dados">
            <td class="admin-celula-tarefa"><?php echo $t['id']; ?></td>
            <td class="admin-celula-tarefa"><?php echo htmlspecialchars($t['nome']); ?></td>
            <td class="admin-celula-tarefa"><?php echo htmlspecialchars($t['titulo']); ?></td>
            <td class="admin-celula-tarefa"><?php echo htmlspecialchars($t['descricao']); ?></td>
            <td class="admin-celula-tarefa"><?php echo $t['data_tarefa']; ?></td>
            <td class="admin-celula-tarefa"><?php echo $t['hora_atividade']; ?></td>
            <td class="admin-celula-tarefa"><?php echo ucfirst($t['status']); ?></td>
            <td class="admin-celula-tarefa admin-celula-tarefa-acoes">
                <?php if($t['status'] === 'pendente'): ?>
                    <a href="?concluir=<?php echo $t['id']; ?>" class="admin-link-concluir">✅ Concluir</a> |
                <?php else: ?>
                    <a href="?pendente=<?php echo $t['id']; ?>" class="admin-link-pendente">↩️ Pendente</a> |
                <?php endif; ?>
                <a href="editar_tarefa.php?id=<?php echo $t['id']; ?>" class="admin-link-editar"> Editar</a> |
                <a href="?excluir=<?php echo $t['id']; ?>" class="admin-link-excluir" onclick="return confirm('Deseja realmente excluir esta tarefa?')"> Excluir</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php 
// Inclui o rodapé (já refatorado)
include 'footer.php'; 
?>