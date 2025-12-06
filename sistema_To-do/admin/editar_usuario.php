<?php
// Inicia a sessão para acessar as variáveis de sessão.
session_start();
// Inclui o arquivo de conexão com o banco de dados.
include('../conexao.php');

// Verifica se o ID do usuário está definido na sessão. Se não estiver, redireciona para a página de login.
if (!isset($_SESSION['id_usuario'])) header("Location: ../login.php");

// Comentário: Bloco PHP para Verificação de Permissão de Admin
// Prepara a consulta para buscar o tipo de usuário (admin ou comum).
$stmt = $conexao->prepare("SELECT tipo FROM usuarios WHERE id=?");
// Associa o ID do usuário da sessão ao placeholder da consulta.
$stmt->bind_param("i", $_SESSION['id_usuario']);
// Executa a consulta.
$stmt->execute();
// Obtém o resultado.
$result = $stmt->get_result();
// Busca a linha de resultado como um array associativo.
$usuario = $result->fetch_assoc();
// Verifica se o tipo de usuário NÃO é 'admin'. Se não for, redireciona para a página inicial (index.php).
if ($usuario['tipo'] !== 'admin') header("Location: ../index.php");

// Comentário: Bloco PHP para Lógica de Busca de Usuário a Ser Editado (GET)
// Verifica se um ID de usuário foi passado via URL (método GET).
if (isset($_GET['id'])) {
    // Armazena o ID do usuário a ser editado.
    $id = $_GET['id'];
    // Prepara a consulta para selecionar todos os dados do usuário.
    $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id=?");
    // Associa o ID do usuário ao placeholder da consulta.
    $stmt->bind_param("i",$id);
    // Executa a consulta.
    $stmt->execute();
    // Obtém o resultado e busca a linha do usuário ($u) como um array associativo.
    $u = $stmt->get_result()->fetch_assoc();
} else {
    // Se nenhum ID for fornecido na URL, redireciona para a lista de usuários e encerra o script.
    header("Location: usuarios.php");
    exit;
}

// Comentário: Bloco PHP para Processar o Formulário de Atualização de Usuário (POST)
// Verifica se o método de requisição é POST (o formulário foi submetido).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e armazena os dados do formulário submetido.
    $nome = $_POST['nome'];
    $nome_social = $_POST['nome_social'];
    $email = $_POST['email'];
    $idade = $_POST['idade'];
    $nome_mae = $_POST['nome_mae'];
    $cpf = $_POST['cpf'];
    $estado_civil = $_POST['estado_civil'];
    $genero = $_POST['genero'];
    $tipo = $_POST['tipo'];

    // Prepara a consulta para ATUALIZAR (UPDATE) os dados do usuário no banco.
    $stmt = $conexao->prepare("
        UPDATE usuarios SET 
            nome=?, 
            nome_social=?, 
            email=?, 
            idade=?, 
            nome_mae=?, 
            cpf=?,
            estado_civil=?, 
            genero=?, 
            tipo=? 
        WHERE id=?
    ");
    // Associa as variáveis do formulário aos placeholders da consulta UPDATE.
    // Tipos: 9 strings (s) e 1 integer (i) para o ID.
    $stmt->bind_param(
        "sssssssssi",
        $nome, $nome_social, $email, $idade, $nome_mae, $cpf, $estado_civil, $genero, $tipo, $id
    );
    // Executa a consulta de atualização.
    $stmt->execute();

    // Redireciona de volta para a lista de usuários após a atualização bem-sucedida.
    header("Location: usuarios.php");
    // Encerra o script para garantir que o redirecionamento ocorra.
    exit;
}
?>

<?php 
// Inclui o cabeçalho padrão da página.
include 'header.php'; 
?>
<link rel="stylesheet" id="theme-style" href="css/admin.css">
<div id="admin-container-edicao-usuario" class="container">
    <h2 class="admin-titulo-edicao-usuario">✏️ Editar Usuário</h2>
    <form method="POST" id="admin-form-edicao-usuario">
        <input class="admin-input-usuario" type="text" name="nome" placeholder="Nome Completo:" value="<?php echo htmlspecialchars($u['nome']); ?>" required id="admin-input-nome"><br>
        <input class="admin-input-usuario" type="text" name="nome_social" placeholder="Nome Social:" value="<?php echo htmlspecialchars($u['nome_social']); ?>" required id="admin-input-nome-social"><br>
        <input class="admin-input-usuario" type="email" name="email" placeholder="Digite o E-mail:" value="<?php echo htmlspecialchars($u['email']); ?>" required id="admin-input-email"><br>
        <input class="admin-input-usuario" type="number" name="idade" placeholder="idade:" value="<?php echo $u['idade']; ?>" required id="admin-input-idade"><br>
        <input class="admin-input-usuario" type="text" name="nome_mae" placeholder="Nome da Mãe Completo:" value="<?php echo htmlspecialchars($u['nome_mae']); ?>" required id="admin-input-nome-mae"><br>
        <input class="admin-input-usuario" type="text" name="cpf" placeholder="Digite o CPF:" value="<?php echo $u['cpf']; ?>" required id="admin-input-cpf"><br>
        <label class="admin-label-campo" for="admin-select-estado-civil">Estado Civil:</label>
        <select class="admin-input-usuario" name="estado_civil" required id="admin-select-estado-civil">
            <option value="solteiro" <?php if($u['estado_civil']=='solteiro') echo 'selected'; ?> class="admin-option-estado-civil">Solteiro(a)</option>
            <option value="casado" <?php if($u['estado_civil']=='casado') echo 'selected'; ?> class="admin-option-estado-civil">Casado(a)</option>
            <option value="divorciado" <?php if($u['estado_civil']=='divorciado') echo 'selected'; ?> class="admin-option-estado-civil">Divorciado(a)</option>
            <option value="viuvo" <?php if($u['estado_civil']=='viuvo') echo 'selected'; ?> class="admin-option-estado-civil">Viúvo(a)</option>
        </select><br>
        <label class="admin-label-campo" for="admin-select-genero">Genêro:</label>
        <select class="admin-input-usuario" name="genero" required id="admin-select-genero">
            <option value="masculino" <?php if($u['genero']=='masculino') echo 'selected'; ?> class="admin-option-genero">Masculino</option>
            <option value="feminino" <?php if($u['genero']=='feminino') echo 'selected'; ?> class="admin-option-genero">Feminino</option>
            <option value="outro" <?php if($u['genero']=='outro') echo 'selected'; ?> class="admin-option-genero">Outro</option>
            <option value="prefiro_nao_informar" <?php if($u['genero']=='prefiro_nao_informar') echo 'selected'; ?> class="admin-option-genero">Prefiro não informar</option>
        </select><br>
        <label class="admin-label-campo" for="admin-select-tipo">Tipo de Usuário:</label>
        <select name="tipo" required id="admin-select-tipo">
            <option value="user" <?php if($u['tipo']=='user') echo 'selected'; ?> class="admin-option-tipo">Usuário</option>
            <option value="admin" <?php if($u['tipo']=='admin') echo 'selected'; ?> class="admin-option-tipo">Admin</option>
        </select><br>
        <button type="submit" class="btn admin-btn-primary" id="admin-btn-salvar-usuario">Salvar</button><br>
        <a href="usuarios.php" class="btn-voltar admin-btn-voltar" id="admin-link-voltar-usuario">Voltar</a>
    </form>
</div>
<style>
/* Style tag vazio removido para manter a limpeza */
</style>
<?php 
// Inclui o rodapé padrão da página.
include 'footer.php'; 
?>