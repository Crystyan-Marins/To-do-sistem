<?php
// Inicia a sessão para acessar as variáveis de sessão.
session_start();
// Inclui o arquivo de conexão com o banco de dados.
include('../conexao.php');

// Comentário: Bloco PHP de Verificação de Login
// Verifica se a variável de sessão 'id_usuario' está definida. Se não estiver, redireciona.
if (!isset($_SESSION['id_usuario'])) header("Location: ../login.php");

// Comentário: Bloco PHP de Verificação de Permissão de Admin
// Prepara a consulta para buscar o tipo de usuário.
$stmt = $conexao->prepare("SELECT tipo FROM usuarios WHERE id=?");
// Associa o ID do usuário da sessão.
$stmt->bind_param("i", $_SESSION['id_usuario']);
// Executa a consulta.
$stmt->execute();
// Obtém o resultado.
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
// Se não for admin, redireciona para a página inicial.
if ($usuario['tipo'] !== 'admin') header("Location: ../index.php");

// Comentário: Bloco PHP para Lógica de Criação de Nova Tarefa (POST)
// Verifica se o método de requisição é POST (o formulário foi submetido).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e armazena os dados do formulário.
    $id_usuario = $_POST['id_usuario'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data_tarefa = $_POST['data_tarefa'];
    $hora_atividade = $_POST['hora_atividade'];

    // Prepara a consulta para INSERIR (INSERT) a nova tarefa no banco.
    $stmt = $conexao->prepare("INSERT INTO tarefas (id_usuario, titulo, descricao, data_tarefa, hora_atividade) VALUES (?,?,?,?,?)");
    // Associa as variáveis aos placeholders da consulta INSERT. Tipos: i (integer), ssss (strings).
    $stmt->bind_param("issss",$id_usuario,$titulo,$descricao,$data_tarefa,$hora_atividade);
    // Executa a consulta.
    $stmt->execute();
    // Redireciona para a lista de tarefas após a inserção.
    header("Location: tarefas.php");
    // Encerra o script para garantir o redirecionamento.
    exit;
}

// Comentário: Bloco PHP de Listagem de Usuários
// Consulta para obter o ID e Nome de todos os usuários, ordenados.
$usuarios = $conexao->query("SELECT id, nome FROM usuarios ORDER BY nome ASC");
?>

<?php 
// Inclui o cabeçalho (já refatorado)
include 'header.php'; 
?>
<link rel="stylesheet" href="css/admin.css">
<div class="container" id="admin-nova-tarefa-container">
    <h2 class="admin-nova-tarefa-titulo">➕ Nova Tarefa</h2>
    <form method="POST" id="admin-form-nova-tarefa">
        <label class="admin-label-campo" for="admin-select-id-usuario">Usuário:</label>
        <select name="id_usuario" required id="admin-select-id-usuario">
            <?php while($u = $usuarios->fetch_assoc()): ?>
                <option value="<?php echo $u['id']; ?>" 
                    <?php if(isset($tarefa) && $tarefa['id_usuario'] == $u['id']) echo 'selected'; ?>
                    class="admin-option-usuario-select">
                    <?php echo htmlspecialchars($u['nome']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br>

        <input type="text" name="titulo" placeholder="Título" 
            value="<?= isset($tarefa) ? htmlspecialchars($tarefa['titulo']) : '' ?>" required 
            id="admin-input-titulo" class="admin-input-tarefa">
        <br>

        <input type="text" name="descricao" placeholder="Descrição" 
            value="<?= isset($tarefa) ? htmlspecialchars($tarefa['descricao']) : '' ?>"
            id="admin-input-descricao" class="admin-input-tarefa">
        <br>

        <input type="date" name="data_tarefa" 
            value="<?= isset($tarefa) ? htmlspecialchars($tarefa['data_tarefa']) : '' ?>" required
            id="admin-input-data" class="admin-input-tarefa">
        <br>

        <input type="time" name="hora_atividade" 
            value="<?= isset($tarefa) ? htmlspecialchars($tarefa['hora_atividade']) : '' ?>" required
            id="admin-input-hora" class="admin-input-tarefa">
        <br>

        <button type="submit" class="btn admin-btn-primary" id="admin-btn-salvar-nova-tarefa">Salvar</button>
        <a href="tarefas.php" class="btn-voltar admin-btn-voltar" id="admin-link-voltar-tarefas">Voltar</a>
    </form>
</div>