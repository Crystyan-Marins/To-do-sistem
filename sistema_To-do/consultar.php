<?php

// Inclui o arquivo de conexão com o banco de dados.
include 'conexao.php';

// Obtém o ID do usuário logado da sessão. Usa '?? null' para evitar warnings se a sessão não estiver definida.
$id_usuario = $_SESSION['id_usuario'] ?? null;
// Verifica se o ID do usuário não está definido.
if (!$id_usuario) {
    // Redireciona o usuário para a página de login se não estiver logado.
    header('Location: login.php');
    exit;
}

// Lógica para alterar o status da tarefa (se o usuário clicou em 'Concluir' ou 'Reabrir').
if (isset($_GET['alterar_status']) && isset($_GET['id_tarefa'])) {
    // Determina o novo status com base no valor passado na URL. Garante que seja 'pendente' ou 'concluida'.
    $nova_status = $_GET['alterar_status'] === 'pendente' ? 'pendente' : 'concluida';
    // Converte o ID da tarefa para um inteiro para segurança (validação de input).
    $id_tarefa = intval($_GET['id_tarefa']);
    
    // Prepara a instrução SQL para atualizar o status da tarefa, garantindo que a tarefa pertença ao usuário logado.
    $stmt_update = $conexao->prepare("UPDATE tarefas SET status = ? WHERE id = ? AND id_usuario = ?");
    // Associa os parâmetros: string (status), integer (id_tarefa), integer (id_usuario).
    $stmt_update->bind_param("sii", $nova_status, $id_tarefa, $id_usuario);
    // Executa a atualização.
    $stmt_update->execute();
    
    // Redireciona para a mesma página para limpar os parâmetros GET da URL e evitar reenvio.
    header('Location: consultar.php');
    exit;
}

// --- Consultas SQL para Tarefas ---

// 1. Consulta para buscar tarefas PENDENTES do usuário, limitadas a 30, ordenadas pela data mais recente.
$stmt_pendente = $conexao->prepare("
    SELECT * FROM tarefas 
    WHERE id_usuario = ? AND status = 'pendente' 
    ORDER BY data_tarefa DESC, hora_atividade DESC 
    LIMIT 30
");
// Associa o parâmetro (id_usuario).
$stmt_pendente->bind_param("i", $id_usuario);
// Executa a consulta.
$stmt_pendente->execute();
// Obtém o resultado da consulta de tarefas pendentes.
$tarefas_pendentes = $stmt_pendente->get_result();

// 2. Consulta para buscar tarefas CONCLUÍDAS do usuário, limitadas a 30, ordenadas pela data mais recente.
$stmt_concluida = $conexao->prepare("
    SELECT * FROM tarefas 
    WHERE id_usuario = ? AND status = 'concluida' 
    ORDER BY data_tarefa DESC, hora_atividade DESC 
    LIMIT 30
");
// Associa o parâmetro (id_usuario).
$stmt_concluida->bind_param("i", $id_usuario);
// Executa a consulta.
$stmt_concluida->execute();
// Obtém o resultado da consulta de tarefas concluídas.
$tarefas_concluidas = $stmt_concluida->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR" id="user-document-html"> <head>
    <meta charset="UTF-8">
    <title id="user-page-title-consultar">Consultar Tarefas</title>
    <link id="favicon" rel="icon" type="image/x-icon" href="img/favicon_claro.png">
    <link rel="stylesheet" id="user-theme-style" href="css/style.css">
<?php
// Inclui o cabeçalho padrão (geralmente contém a abertura da sessão e HTML inicial).
include 'header.php';
?>
</head>
<body id="user-consult-tasks-page"> <div class="user-consult__container" id="user-main-consult-wrapper">
        <h1 class="user-consult__heading-main" id="user-heading-main">📋 Minhas Tarefas</h1>

        <h2 class="user-consult__heading-section user-consult__heading-section--pendente" id="user-heading-pendentes">Pendentes</h2>
        
        <?php 
        // Verifica se há tarefas pendentes para exibir.
        if ($tarefas_pendentes->num_rows > 0): 
        ?>
            <?php while($tarefa = $tarefas_pendentes->fetch_assoc()): ?>
                <div class="user-task-card user-task-card--pendente" id="user-task-card-<?php echo $tarefa['id']; ?>">
                    <h3 class="user-task-card__title">
                        <?php echo htmlspecialchars($tarefa['titulo']); ?>
                        <span class="user-task-card__status user-status--pendente">Pendente</span>
                    </h3>

                    <p class="user-task-card__detail user-task-card__detail--descricao">
                        <strong class="user-task-card__label">Descrição:</strong> 
                        <?php echo htmlspecialchars($tarefa['descricao']); ?>
                    </p>
                    <p class="user-task-card__detail user-task-card__detail--data">
                        <strong class="user-task-card__label">Data:</strong> 
                        <?php echo date('d/m/Y', strtotime($tarefa['data_tarefa'])); ?>
                    </p>
                    <p class="user-task-card__detail user-task-card__detail--hora">
                        <strong class="user-task-card__label">Hora:</strong> 
                        <?php echo date('H:i', strtotime($tarefa['hora_atividade'])); ?>
                    </p>

                    <div class="user-task-card__actions">
                        <a href="?id_tarefa=<?php echo $tarefa['id']; ?>&alterar_status=concluida" 
                           class="user-task-card__link user-task-card__link--concluir"
                           id="user-link-concluir-<?php echo $tarefa['id']; ?>">
                            <button class="user-button user-button--concluir user-task-card__btn--concluir">Concluir</button>
                        </a>

                        <a href="editar.php?id=<?php echo $tarefa['id']; ?>" 
                           class="user-task-card__link user-task-card__link--editar"
                           id="user-link-editar-<?php echo $tarefa['id']; ?>">
                            <button class="user-button user-button--editar user-task-card__btn--editar">Editar</button>
                        </a>

                        <a href="excluir.php?id=<?php echo $tarefa['id']; ?>" 
                           onclick="return confirm('Tem certeza que deseja excluir esta tarefa?');"
                           class="user-task-card__link user-task-card__link--excluir"
                           id="user-link-excluir-<?php echo $tarefa['id']; ?>">
                            <button class="user-button user-button--excluir user-task-card__btn--excluir">Excluir</button>
                        </a>
                    </div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="user-consult__empty-message user-consult__empty-message--pendente" id="user-msg-no-pendente">Não há tarefas pendentes.</p>
        <?php endif; ?>

        <h2 class="user-consult__heading-section user-consult__heading-section--concluida" id="user-heading-concluidas">Concluídas</h2>
        
        <?php 
        // Verifica se há tarefas concluídas para exibir.
        if ($tarefas_concluidas->num_rows > 0): 
        ?>
            <?php while($tarefa = $tarefas_concluidas->fetch_assoc()): ?>
                <div class="user-task-card user-task-card--concluida" id="user-task-card-<?php echo $tarefa['id']; ?>">
                    <h3 class="user-task-card__title">
                        <?php echo htmlspecialchars($tarefa['titulo']); ?>
                        <span class="user-task-card__status user-status--concluida">Concluída</span>
                    </h3>

                    <p class="user-task-card__detail user-task-card__detail--descricao">
                        <strong class="user-task-card__label">Descrição:</strong> 
                        <?php echo htmlspecialchars($tarefa['descricao']); ?>
                    </p>
                    <p class="user-task-card__detail user-task-card__detail--data">
                        <strong class="user-task-card__label">Data:</strong> 
                        <?php echo date('d/m/Y', strtotime($tarefa['data_tarefa'])); ?>
                    </p>
                    <p class="user-task-card__detail user-task-card__detail--hora">
                        <strong class="user-task-card__label">Hora:</strong> 
                        <?php echo date('H:i', strtotime($tarefa['hora_atividade'])); ?>
                    </p>

                    <div class="user-task-card__actions">
                        <a href="?id_tarefa=<?php echo $tarefa['id']; ?>&alterar_status=pendente" 
                           class="user-task-card__link user-task-card__link--reabrir"
                           id="user-link-reabrir-<?php echo $tarefa['id']; ?>">
                            <button class="user-button user-button--reabrir user-task-card__btn--reabrir">Reabrir</button>
                        </a>

                        <a href="editar.php?id=<?php echo $tarefa['id']; ?>" 
                           class="user-task-card__link user-task-card__link--editar"
                           id="user-link-editar-<?php echo $tarefa['id']; ?>">
                            <button class="user-button user-button--editar user-task-card__btn--editar">Editar</button>
                        </a>

                        <a href="excluir.php?id=<?php echo $tarefa['id']; ?>" 
                           onclick="return confirm('Tem certeza que deseja excluir esta tarefa?');"
                           class="user-task-card__link user-task-card__link--excluir"
                           id="user-link-excluir-<?php echo $tarefa['id']; ?>">
                            <button class="user-button user-button--excluir user-task-card__btn--excluir">Excluir</button>
                        </a>
                    </div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="user-consult__empty-message user-consult__empty-message--concluida" id="user-msg-no-concluida">Não há tarefas concluídas.</p>
        <?php endif; ?>

    </div>

</body>
<?php 
// Inclui o rodapé padrão da página de usuário.
include 'footer.php'; 
?>
</html>