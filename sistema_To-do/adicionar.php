<?php
// Inclui o arquivo de conexão com o banco de dados.
// NOTA: Este bloco PHP é back-end puro, não recebe IDs/Classes de Front-end.
include 'conexao.php';

// Verifica se a requisição HTTP foi feita usando o método POST (ou seja, se o formulário foi enviado).
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Coleta o valor do campo 'titulo' enviado via formulário.
  $titulo = $_POST['titulo'];
  // Coleta o valor do campo 'descricao' enviado via formulário.
  $descricao = $_POST['descricao'];
  // Coleta o valor do campo 'data_tarefa' enviado via formulário.
  $data_tarefa = $_POST['data_tarefa'];
  // Coleta o valor do campo 'hora_atividade' enviado via formulário.
  $hora_atividade = $_POST['hora_atividade'];

  // Define a query SQL para inserir os dados da nova tarefa na tabela 'tarefas'.
  $sql = "INSERT INTO tarefas (titulo, descricao, data_tarefa, hora_atividade)
          VALUES ('$titulo', '$descricao', '$data_tarefa', '$hora_atividade')";
  // Executa a query de inserção no banco de dados.
  $conexao->query($sql);
  // Redireciona o usuário para a página principal ('index.php') após a inserção bem-sucedida.
  header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="pt-BR" id="user-document-html">
  <meta charset="UTF-8">
  <title id="user-page-title">Nova Tarefa</title>
  <link id="favicon" rel="icon" type="image/x-icon" href="img/favicon_claro.png">
  <link rel="stylesheet" id="user-theme-style" href="css/style.css">
  <?php
// Inclui o cabeçalho padrão da página de usuário (que deve conter o início do HTML, <head> e possivelmente o <header> visual).
include 'header.php';
?>

<body id="user-task-new-page"> <div class="user-container user-newtask__container" id="user-main-content-wrapper">
    <h1 class="user-newtask__heading">➕ Nova Tarefa</h1>
    <form method="POST" class="user-newtask__form" id="user-add-task-form">
      <input type="text" name="titulo" placeholder="Título" required
             class="user-form__input user-form__input--titulo" id="user-input-titulo">
      <input name="descricao" placeholder="Descrição"
             class="user-form__input user-form__input--descricao" id="user-input-descricao">

      <label for="user-input-data" class="user-form__label user-form__label--data">Data da Tarefa:</label>
      <input type="date" name="data_tarefa" required
             class="user-form__input user-form__input--data" id="user-input-data">

      <label for="user-input-hora" class="user-form__label user-form__label--hora">Hora da Atividade:</label>
      <input type="time" name="hora_atividade" required
             class="user-form__input user-form__input--hora" id="user-input-hora">

      <button type="submit" class="user-button user-button--submit user-form__btn--salvar" id="user-btn-salvar-task">Salvar</button>
      <a href="index.php" class="user-button user-button--secondary user-form__btn--voltar" id="user-link-voltar">Voltar</a>
    </form>
  </div>
</body>
<?php
// Inclui o rodapé padrão da página de usuário.
include 'footer.php';
?>
</html>