<?php
// Inicia a sessão para acessar as variáveis de sessão.
session_start();
// Inclui o arquivo de conexão com o banco de dados.
include('../conexao.php');

// Verifica se o ID do usuário está definido na sessão. Se não estiver, redireciona para a página de login.
if (!isset($_SESSION['id_usuario'])) header("Location: ../login.php");

// Prepara a consulta para buscar o tipo de usuário (admin ou comum) com base no ID da sessão.
$stmt = $conexao->prepare("SELECT tipo FROM usuarios WHERE id=?");
// Associa o ID do usuário da sessão ao placeholder da consulta. O "i" indica que é um integer (inteiro).
$stmt->bind_param("i", $_SESSION['id_usuario']);
// Executa a consulta preparada.
$stmt->execute();
// Obtém o resultado da consulta.
$result = $stmt->get_result();
// Busca a linha de resultado como um array associativo (contém o campo 'tipo').
$usuario = $result->fetch_assoc();
// Verifica se o tipo de usuário não é 'admin'. Se não for, redireciona para a página inicial (index.php), impedindo acesso não autorizado.
if ($usuario['tipo'] !== 'admin') header("Location: ../index.php");

// Comentário: Bloco PHP para Lógica de Edição de Tarefa (GET e POST)
// Verifica se um ID de tarefa foi passado via URL (método GET).
if (isset($_GET['id'])) {
    // Armazena o ID da tarefa a ser editada.
    $id = $_GET['id'];
    // Prepara a consulta para selecionar todos os dados da tarefa com o ID fornecido.
    $stmt = $conexao->prepare("SELECT * FROM tarefas WHERE id=?");
    // Associa o ID da tarefa ao placeholder da consulta.
    $stmt->bind_param("i",$id);
    // Executa a consulta.
    $stmt->execute();
    // Obtém o resultado e busca a linha da tarefa como um array associativo.
    $t = $stmt->get_result()->fetch_assoc();
} else {
    // Se nenhum ID for fornecido na URL, redireciona para a lista de tarefas e encerra o script.
    header("Location: tarefas.php");
    exit;
}

// Comentário: Bloco PHP para Processar o Formulário de Edição (POST)
// Verifica se o método de requisição é POST (ou seja, se o formulário foi submetido).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e armazena os dados do formulário submetido.
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data_tarefa = $_POST['data_tarefa'];
    $hora_atividade = $_POST['hora_atividade'];
    $status = $_POST['status'];

    // Prepara a consulta para ATUALIZAR (UPDATE) os dados da tarefa no banco.
    $stmt = $conexao->prepare("UPDATE tarefas SET titulo=?, descricao=?, data_tarefa=?, hora_atividade=?, status=? WHERE id=?");
    // Associa as variáveis do formulário aos placeholders da consulta UPDATE.
    // Tipos: s (string) para os primeiros 5 campos, i (integer) para o ID.
    $stmt->bind_param("sssssi",$titulo,$descricao,$data_tarefa,$hora_atividade,$status,$id);
    // Executa a consulta de atualização.
    $stmt->execute();
    // Redireciona de volta para a lista de tarefas após a atualização bem-sucedida.
    header("Location: tarefas.php");
    // Encerra o script para garantir que o redirecionamento ocorra.
    exit;
}
?>

<?php 
// Inclui o cabeçalho padrão da página.
include 'header.php'; 
?>
<link rel="stylesheet" href="css/admin.css">

<div class="container" id="admin-edicao-tarefa-container">
    <h2 class="admin-edicao-tarefa-titulo">✏️ Editar Tarefa</h2>
    <form method="POST" id="admin-edicao-tarefa-form">
        <input type="text" name="titulo" placeholder="Tíulo da tarefa:" value="<?php echo htmlspecialchars($t['titulo']); ?>" required id="admin-input-titulo"><br>
        <input type="text" name="descricao" placeholder="Descrição:" value="<?php echo htmlspecialchars($t['descricao']); ?>" required id="admin-input-descricao"><br>
        <input type="date" name="data_tarefa" value="<?php echo $t['data_tarefa']; ?>" required id="admin-input-data"><br>
        <input type="time" name="hora_atividade" value="<?php echo $t['hora_atividade']; ?>" required id="admin-input-hora">
        <select name="status" id="admin-select-status">
            <option value="pendente" <?php if($t['status']=='pendente') echo 'selected'; ?> class="admin-select-status-option">Pendente</option>
            <option value="concluida" <?php if($t['status']=='concluida') echo 'selected'; ?> class="admin-select-status-option">Concluída</option>
        </select>
        <button type="submit" class="btn admin-btn-primary" id="admin-btn-salvar">Salvar</button><br>
        <a href="tarefas.php" class="btn-voltar admin-btn-voltar" id="admin-link-voltar">Voltar</a>
    </form>
</div>
<?php 
// Inclui o rodapé padrão da página.
include 'footer.php'; 
?>