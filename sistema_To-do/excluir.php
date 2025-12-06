<?php
include 'conexao.php';
$id = $_GET['id'];
$conexao->query("DELETE FROM tarefas WHERE id=$id");
header("Location: index.php");
?>
<link rel="stylesheet" href="css/style.css">