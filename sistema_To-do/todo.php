<?php
// Inicia a sessão se ainda não estiver iniciada.
session_start();
// Inclui o arquivo de conexão com o banco de dados.
include('conexao.php');

// Se o usuário não estiver logado, redireciona.
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

// Obtém o ID do usuário logado da sessão.
$id_usuario = $_SESSION['id_usuario'];

// --- Lógica de Inserção, Atualização e Exclusão ---

// 1. Inserir nova tarefa (se o formulário POST for enviado com um título).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'])) {
    // Coleta os dados do formulário.
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data = $_POST['data_tarefa'];
    $hora = $_POST['hora_atividade'];

    // Prepara e executa a instrução de inserção.
    $stmt = $conexao->prepare("INSERT INTO tarefas (id_usuario, titulo, descricao, data_tarefa, hora_atividade) VALUES (?, ?, ?, ?, ?)");
    // Associa os parâmetros: integer, strings (4x).
    $stmt->bind_param("issss", $id_usuario, $titulo, $descricao, $data, $hora);
    $stmt->execute();
}

// 2. Atualizar status (concluir tarefa) via parâmetro GET.
if (isset($_GET['concluir'])) {
    // Obtém o ID da tarefa a ser concluída.
    $id_tarefa = $_GET['concluir'];
    // Prepara e executa a instrução de atualização de status, garantindo o id_usuario.
    $stmt = $conexao->prepare("UPDATE tarefas SET status='concluida' WHERE id=? AND id_usuario=?");
    // Associa os parâmetros: integer (id_tarefa), integer (id_usuario).
    $stmt->bind_param("ii", $id_tarefa, $id_usuario);
    $stmt->execute();
}

// 3. Excluir tarefa via parâmetro GET.
if (isset($_GET['excluir'])) {
    // Obtém o ID da tarefa a ser excluída.
    $id_tarefa = $_GET['excluir'];
    // Prepara e executa a instrução de exclusão, garantindo o id_usuario.
    $stmt = $conexao->prepare("DELETE FROM tarefas WHERE id=? AND id_usuario=?");
    // Associa os parâmetros: integer (id_tarefa), integer (id_usuario).
    $stmt->bind_param("ii", $id_tarefa, $id_usuario);
    $stmt->execute();
}

// --- Listagem da Última Tarefa ---

// Prepara a consulta para listar apenas a última tarefa do usuário logado.
$resultado = $conexao->prepare("SELECT * FROM tarefas WHERE id_usuario=? ORDER BY id DESC LIMIT 1");
// Associa o parâmetro: integer (id_usuario).
$resultado->bind_param("i", $id_usuario);
$resultado->execute();
// Obtém o resultado da consulta.
$tarefas = $resultado->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR" id="user-document-html"> 
    <meta charset="UTF-8">
    <title id="user-page-title-todo">Minhas Tarefas</title>
    <link id="favicon" rel="icon" type="image/x-icon" href="img/favicon_claro.png">
    <link rel="stylesheet" id="user-theme-style" href="css/style.css">
<?php
// Inclui o cabeçalho padrão da página (que já revisamos).
include 'header.php';
?>
<body id="user-todo-list-page"> <div id="user-main-container-todo" class="user-container user-todo__wrapper">
        <h2 class="user-todo__heading-main">📝 Suas Tarefas</h2>

        <form method="POST" class="user-todo__form" id="user-add-task-form">
            <input type="text" name="titulo" placeholder="Título da tarefa" required
                   class="user-form__input user-form__input--titulo user-todo__input"><br>
            <input type="text" name="descricao" placeholder="Descrição da Tarefa" maxlength="255" required
                   class="user-form__input user-form__input--descricao user-todo__input"><br>
            <input type="date" name="data_tarefa" required
                   class="user-form__input user-form__input--data user-todo__input"><br>
            <input type="time" name="hora_atividade" required class="user-form__input user-form__input--hora user-todo__input"><br>
            <button type="submit" 
                    class="user-button user-button--primary user-todo__btn--add" id="user-btn-add-task">Adicionar</button>
        </form>

        <h3 class="user-todo__heading-list">📋 Lista de tarefas (Última)</h3>
        
        <?php
        // Verifica se há tarefas para exibir.
        if ($tarefas->num_rows > 0):
        ?>
            <ul class="user-todo__list" id="user-task-list">
                <?php
                // Loop para iterar sobre a tarefa encontrada.
                while ($tarefa = $tarefas->fetch_assoc()):
                ?>
                    <li class="user-todo__list-item" id="user-list-item-<?php echo $tarefa['id']; ?>">
                        <strong class="user-todo__item-title"><?php echo htmlspecialchars($tarefa['titulo']); ?></strong><br>
                        <span class="user-todo__item-description"><?php echo htmlspecialchars($tarefa['descricao']); ?></span><br>
                        <span class="user-todo__item-details">
                            Data: <?php echo htmlspecialchars($tarefa['data_tarefa']); ?> às <?php echo htmlspecialchars($tarefa['hora_atividade']); ?>
                        </span><br>
                        <span class="user-todo__item-status user-status--<?php echo $tarefa['status']; ?>">
                            Status: <?php echo ucfirst($tarefa['status']); ?>
                        </span><br>
                        
                        <?php if ($tarefa['status'] === 'pendente'): ?>
                            <a href="?concluir=<?php echo $tarefa['id']; ?>" 
                               class="user-todo__action-link user-action-link--concluir" id="user-link-concluir-<?php echo $tarefa['id']; ?>">
                                ✅ Concluir
                            </a>
                            <span class="user-todo__action-separator"> | </span>
                        <?php endif; ?>
                        
                        <a href="editar.php?id=<?php echo $tarefa['id']; ?>" 
                           class="user-todo__action-link user-action-link--editar"  id="user-link-editar-<?php echo $tarefa['id']; ?>">
                           ✏️ Editar
                        </a>
                        <span class="user-todo__action-separator"> | </span>
                        
                        <a href="?excluir=<?php echo $tarefa['id']; ?>" 
                           class="user-todo__action-link user-action-link--excluir" id="user-link-excluir-<?php echo $tarefa['id']; ?>">
                           🗑 Excluir
                        </a>
                    </li>
                    <hr class="user-todo__item-divider">
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="user-todo__empty-message">Você ainda não tem tarefas.</p>
        <?php endif; ?>

        <a href="index.php" class="user-link user-link--back user-todo__link--back" id="user-link-back-home">⬅ Voltar</a>
    </div>
</body>
<?php
// Inclui o rodapé padrão da página (que já revisamos).
include 'footer.php';
?>
</html>