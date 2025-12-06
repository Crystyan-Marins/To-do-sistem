<?php
// Inicia a sessão.
session_start();
// Inclui o arquivo de conexão com o banco de dados.
include 'conexao.php';

// Verifica se o ID do usuário está definido na sessão (se o usuário está logado).
if (!isset($_SESSION['id_usuario'])) {
    // Redireciona para a página de login se não estiver logado.
    header("Location: login.php");
    exit;
}

// Obtém o ID do usuário logado da sessão.
$id_usuario = $_SESSION['id_usuario'];
// Obtém o ID da tarefa a ser editada da URL (parâmetro GET 'id').
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// --- BUSCA DA TAREFA ---
// Prepara a instrução SQL para buscar a tarefa, garantindo que ela pertença ao usuário logado.
$stmt = $conexao->prepare("SELECT * FROM tarefas WHERE id = ? AND id_usuario = ?");
// Associa os parâmetros: integer (id da tarefa), integer (id do usuário).
$stmt->bind_param("ii", $id, $id_usuario);
// Executa a consulta.
$stmt->execute();
// Obtém o resultado e armazena os dados da tarefa.
$tarefa = $stmt->get_result()->fetch_assoc();

// Verifica se a tarefa não foi encontrada ou não pertence ao usuário.
if (!$tarefa) {
    // Exibe uma mensagem de erro estilizada diretamente no HTML (o estilo inline foi mantido).
    echo "<div style='max-width:700px;margin:40px auto;padding:20px;background:#fff;border-radius:8px;text-align:center;'>
              <p>Tarefa não encontrada ou você não tem permissão para editá-la.</p>
              <p><a href='index.php'>Voltar</a></p>
            </div>";
    exit; // Encerra a execução.
}

// --- ATUALIZAÇÃO DA TAREFA (POST) ---
// Verifica se a requisição HTTP foi feita usando o método POST (envio do formulário).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta e armazena os dados do formulário com um fallback para strings vazias.
    $titulo = $_POST['titulo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $data_tarefa = $_POST['data_tarefa'] ?? '';
    $hora_atividade = $_POST['hora_atividade'] ?? '';
    $status = $_POST['status'] ?? 'pendente';

    // Prepara a instrução SQL para atualizar a tarefa.
    $stmt = $conexao->prepare("UPDATE tarefas
                              SET titulo = ?, descricao = ?, data_tarefa = ?, hora_atividade = ?, status = ?
                              WHERE id = ? AND id_usuario = ?");
    // Associa os parâmetros: strings (titulo, descricao, data, hora, status), integer (id da tarefa), integer (id do usuário).
    $stmt->bind_param("sssssii", $titulo, $descricao, $data_tarefa, $hora_atividade, $status, $id, $id_usuario);
    // Executa a atualização.
    $stmt->execute();

    // Redireciona para a página principal após a atualização.
    header("Location: index.php");
    exit;
}

// Função auxiliar para saída segura (Escaping) no HTML.
function h($str) {
    // Escapa caracteres especiais para evitar XSS e garantir saída correta.
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<?php
// Inclui o cabeçalho padrão da página.
include 'header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR" id="user-document-html"> <head>
    <meta charset="UTF-8">
    <title id="user-page-title-edit">Editar Tarefa</title>
    <link id="favicon" rel="icon" type="image/x-icon" href="img/favicon_claro.ico">
    <link rel="stylesheet" id="user-theme-style" href="css/style.css">
</head>
<body id="user-edit-task-page"> 
    <div id="user-main-container-edit" class="user-container user-edit-task__wrapper">
        <h1 class="user-edit-task__heading">✏️ Editar Tarefa</h1>

        <form method="POST" action="editar.php?id=<?= $tarefa['id'] ?>" class="user-edit-task__form" id="user-edit-form">
            <input class="user-form__input user-form__input--titulo user-edit-task__input" 
                   type="text" name="titulo" 
                   value="<?= h($tarefa['titulo']) ?>" required 
                   placeholder="Título da Tarefa" id="user-input-titulo-edit"><br>

            <input class="user-form__input user-form__input--descricao user-edit-task__input" 
                   type="text" name="descricao" 
                   value="<?= h($tarefa['descricao']) ?>" required 
                   placeholder="Descrição da Tarefa" maxlength="255" id="user-input-descricao-edit"><br>

            <input class="user-form__input user-form__input--data user-edit-task__input" 
                   type="date" name="data_tarefa" 
                   value="<?= h($tarefa['data_tarefa']) ?>" required id="user-input-data-edit"><br>

            <input class="user-form__input user-form__input--hora user-edit-task__input" 
                   type="time" name="hora_atividade" 
                   value="<?= h($tarefa['hora_atividade']) ?>" required id="user-input-hora-edit"><br>

            <select class="user-form__select user-edit-task__select--status" name="status" id="user-select-status">
                <option value="pendente" <?= $tarefa['status']=='pendente' ? 'selected' : '' ?> class="user-edit-task__option user-status--pendente">Pendente</option>
                <option value="concluida" <?= $tarefa['status']=='concluida' ? 'selected' : '' ?> class="user-edit-task__option user-status--concluida">Concluída</option>
            </select><br>

            <button type="submit" class="user-button user-button--primary user-edit-task__btn--salvar" id="user-btn-save-edit">Salvar Alterações</button><br>
            <a href="index.php" class="user-button-user-button--secondary user-edit-task__btn--voltar" id="user-link-voltar-edit">Voltar</a>
        </form>
    </div>
</body>
<?php 
// Inclui o rodapé padrão da página.
include 'footer.php'; 
?>
</html>