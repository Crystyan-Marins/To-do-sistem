<?php
session_start();
include('../conexao.php');

// Verifica se está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

// Verifica se é admin
$stmt = $conexao->prepare("SELECT tipo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id_usuario']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if ($usuario['tipo'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Estatísticas gerais
$total_usuarios = $conexao->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
$total_tarefas = $conexao->query("SELECT COUNT(*) as total FROM tarefas")->fetch_assoc()['total'];
$total_concluidas = $conexao->query("SELECT COUNT(*) as total FROM tarefas WHERE status='concluida'")->fetch_assoc()['total'];
?>

<?php include 'header.php'; ?>
<link rel="stylesheet" href="css/admin.css">
<div id="container-dashboard" class="container">
    <h2>📊 Dashboard do Admin</h2>

    <div class="card">
        <h3>Usuários Registrados</h3>
        <p><?php echo $total_usuarios; ?></p>
    </div>
    <div class="card">
        <h3>Tarefas Criadas</h3>
        <p><?php echo $total_tarefas; ?></p>
    </div>
    <div class="card">
        <h3>Tarefas Concluídas</h3>
        <p><?php echo $total_concluidas; ?></p>
    </div>

    <div id="nav-admin" style="margin-top:20px;">
        <a href="usuarios.php" class="btn">👤Usuários - </a>
        <a href="tarefas.php" class="btn"> 📎Tarefas - </a>
        <a href="relatorios.php" class="btn"> 📊Relatório</a>
    </div>
</div>

<?php include 'footer.php'; ?>
