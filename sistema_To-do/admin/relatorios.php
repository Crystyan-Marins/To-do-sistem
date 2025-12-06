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

// Comentário: Carrega a biblioteca FPDF para geração de documentos PDF.
require('fpdf/fpdf.php');

// Comentário: Bloco PHP para Gerar o PDF (Acionado via POST)
// Verifica se o botão "Gerar PDF" foi clicado (o campo 'gerar' foi enviado via POST).
if (isset($_POST['gerar'])) {
    // Cria uma nova instância da classe FPDF.
    $pdf = new FPDF();
    // Adiciona uma nova página ao documento.
    $pdf->AddPage();
    // Define a fonte: Arial, Negrito (B), tamanho 16.
    $pdf->SetFont('Arial','B',16);
    // Adiciona o título centralizado. (0=largura, 10=altura, 0=sem borda, 1=nova linha, 'C'=centralizado)
    $pdf->Cell(0,10,'Relatorio do Sistema To-Do',0,1,'C');
    // Adiciona um espaço vertical de 10mm.
    $pdf->Ln(10);

    // Comentário: Seção de Estatísticas do Relatório (Consultas ao BD)
    // Obtém o total de usuários.
    $total_usuarios = $conexao->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
    // Obtém o total de tarefas.
    $total_tarefas = $conexao->query("SELECT COUNT(*) as total FROM tarefas")->fetch_assoc()['total'];
    // Obtém o total de tarefas concluídas.
    $total_concluidas = $conexao->query("SELECT COUNT(*) as total FROM tarefas WHERE status='concluida'")->fetch_assoc()['total'];

    // Define a fonte: Arial, Normal, tamanho 12 para as estatísticas.
    $pdf->SetFont('Arial','',12);
    // Exibe o total de usuários.
    $pdf->Cell(0,10,"Total de Usuarios: $total_usuarios",0,1);
    // Exibe o total de tarefas.
    $pdf->Cell(0,10,"Total de Tarefas: $total_tarefas",0,1);
    // Exibe o total de tarefas concluídas.
    $pdf->Cell(0,10,"Tarefas Concluidas: $total_concluidas",0,1);
    // Adiciona um espaço vertical.
    $pdf->Ln(10);

    // Comentário: Seção de Listagem de Usuários
    // Define a fonte: Arial, Negrito, tamanho 14 para o subtítulo.
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Usuarios Cadastrados',0,1);
    // Define a fonte: Arial, Normal, tamanho 12 para o conteúdo.
    $pdf->SetFont('Arial','',12);
    // Consulta para listar usuários.
    $usuarios = $conexao->query("SELECT id,nome,email FROM usuarios ORDER BY id ASC");
    // Itera sobre os resultados e adiciona cada usuário ao PDF.
    while($u = $usuarios->fetch_assoc()){
        $pdf->Cell(0,8,"ID: {$u['id']} - Nome: {$u['nome']} - Email: {$u['email']}",0,1);
    }

    // Adiciona um espaço vertical.
    $pdf->Ln(10);
    // Comentário: Seção de Listagem de Tarefas
    // Define a fonte: Arial, Negrito, tamanho 14 para o subtítulo.
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Tarefas Cadastradas',0,1);
    // Define a fonte: Arial, Normal, tamanho 12 para o conteúdo.
    $pdf->SetFont('Arial','',12);
    // Consulta para listar tarefas juntando (JOIN) com o nome do usuário responsável.
    $tarefas = $conexao->query("SELECT t.id,t.titulo,t.status,u.nome FROM tarefas t JOIN usuarios u ON t.id_usuario=u.id ORDER BY t.id DESC");
    // Itera sobre os resultados e adiciona cada tarefa ao PDF.
    while($t = $tarefas->fetch_assoc()){
        $pdf->Cell(0,8,"ID: {$t['id']} - Titulo: {$t['titulo']} - Usuario: {$t['nome']} - Status: {$t['status']}",0,1);
    }

    // Comentário: Saída do PDF
    // Envia o PDF para o navegador para ser baixado ('D' de Download), com o nome 'relatorio_todo.pdf'.
    $pdf->Output('D','relatorio_todo.pdf');
    // Encerra o script.
    exit;
}
?>

<?php 
// Inclui o cabeçalho (já refatorado)
include 'header.php'; 
?>
<link rel="stylesheet" href="css/admin.css">
<div class="container" id="admin-relatorio-container">
    <h2 class="admin-relatorio-titulo">📄 Gerar Relatório</h2>
    <form method="POST" id="admin-form-relatorio">
        <button type="submit" name="gerar" class="btn admin-btn-primary" id="admin-btn-gerar-pdf">Gerar PDF</button>
    </form>
</div>

<?php 
// Inclui o rodapé (já refatorado)
include 'footer.php'; 
?>